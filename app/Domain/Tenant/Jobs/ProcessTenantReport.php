<?php

namespace App\Domain\Tenant\Jobs;

use App\Domain\Project\Models\Project;
use App\Domain\Task\Models\Task;
use App\Domain\Tenant\Models\TenantReport;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessTenantReport implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * تعداد دفعات تلاش برای اجرای Job
     */
    public int $tries = 3;

    /**
     * حداکثر زمان اجرای Job بر حسب ثانیه
     */
    public int $timeout = 120;

    public function __construct(
        public int $tenantId,
        public int $reportId,
    ) {
    }

    /**
     * جلوگیری از اجرای هم‌زمان چند گزارش برای یک tenant
     */
    public function middleware(): array
    {
        return [
            (new WithoutOverlapping(
                'tenant-report-'.$this->tenantId
            ))
                ->releaseAfter(10)
                ->expireAfter(180),
        ];
    }

    /**
     * اجرای اصلی Job
     */
   // در App\Domain\Tenant\Jobs\GenerateTenantReportJob
public function handle()
{
    $this->report->update(['status' => 'processing', 'started_at' => now()]);

    try {
        // منطق محاسبات گزارش
        $data = [
            'projects_count' => Project::count(),
            'tasks_count' => Task::count(),
            'generated_at' => now(),
        ];

        $this->report->update([
            'status' => 'completed',
            'payload' => $data,
            'completed_at' => now(),
        ]);
    } catch (\Exception $e) {
        $this->report->update([
            'status' => 'failed',
            'error_message' => $e->getMessage(), // اضافه کردن این ستون در صورت نیاز
            'completed_at' => now(),
        ]);
    }
}


    /**
     * این متد زمانی اجرا می‌شود که تمام تلاش‌های Job ناموفق باشند.
     */
    public function failed(Throwable $exception): void
    {
        $report = TenantReport::query()->find($this->reportId);

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
