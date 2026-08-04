<?php

namespace App\Domain\Tenant\Jobs;

use App\Domain\Project\Models\Project;
use App\Domain\Task\Models\Task;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Tenant\Models\TenantReport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessTenantReport implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $timeout = 120;

    public function __construct(
        public int $tenantId,
        public int $reportId,
    ) {
    }

    public function middleware(): array
    {
        return [
            (new WithoutOverlapping('tenant-report-' . $this->tenantId))
                ->releaseAfter(10)
                ->expireAfter(180),
        ];
    }

    public function handle(): void
    {
        // رپورت‌ها در دیتابیس central ذخیره می‌شوند
        $report = TenantReport::on('central')->find($this->reportId);

        if (!$report) {
            Log::warning('Tenant report not found.', [
                'tenant_id' => $this->tenantId,
                'report_id' => $this->reportId,
            ]);

            return;
        }

        $report->update([
            'status' => 'processing',
            'started_at' => now(),
        ]);

        try {
            // مستاجرها در دیتابیس central قرار دارند
            $tenant = Tenant::on('central')->find($this->tenantId);

            if (!$tenant) {
                throw new \RuntimeException("Tenant not found for ID {$this->tenantId}");
            }

            if (empty($tenant->database)) {
                throw new \RuntimeException("Tenant database name is empty for tenant ID {$this->tenantId}");
            }

            // پیکربندی اتصال tenant متناسب با محیط اجرا
            if (app()->environment('testing')) {
                Config::set('database.connections.tenant', [
                    'driver' => 'sqlite',
                    'database' => $tenant->database,
                    'prefix' => '',
                    'foreign_key_constraints' => true,
                ]);
            } else {
                Config::set('database.connections.tenant.database', $tenant->database);
            }

            DB::purge('tenant');
            DB::reconnect('tenant');
            DB::setDefaultConnection('tenant');

            $payload = [
                'projects_count' => Project::count(),
                'tasks_count' => Task::count(),
                'generated_at' => now()->toISOString(),
            ];

            // بازگرداندن اتصال به central برای ذخیره وضعیت نهایی گزارش
            DB::setDefaultConnection('central');

            $report->update([
                'status' => 'completed',
                'payload' => $payload,
                'completed_at' => now(),
                'error_message' => null,
            ]);
        } catch (Throwable $e) {
            // اطمینان از اعمال تغییرات روی دیتابیس central در صورت بروز خطا
            try {
                $report->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                    'completed_at' => now(),
                ]);
            } catch (Throwable $dbEx) {
                // نادیده گرفتن خطای دیتابیس ثانویه برای لاگ کردن خطای اصلی
            }

            Log::error('Tenant report processing failed', [
                'report_id' => $report->id,
                'tenant_id' => $report->tenant_id,
                'message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function failed(Throwable $exception): void
    {
        $report = TenantReport::on('central')->find($this->reportId);

        if ($report) {
            $report->update([
                'status' => 'failed',
                'error_message' => $exception->getMessage(),
                'completed_at' => now(),
            ]);
        }

        Log::critical('Tenant report job failed permanently.', [
            'tenant_id' => $this->tenantId,
            'report_id' => $this->reportId,
            'error' => $exception->getMessage(),
        ]);
    }
}
