<?php

namespace App\Console\Commands;

use App\Domain\Tenant\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class TenantMigrateCommand extends Command
{
    // تغییر نام دستور به tenant:migrate
    protected $signature = 'tenant:migrate {--tenant= : Tenant database name, for example alpha_taskino}';

    protected $description = 'Run migrations for tenant databases';

    public function handle(): int
    {
        $tenantOption = $this->option('tenant');

        $query = Tenant::query();

        if ($tenantOption) {
            $query->where('database', $tenantOption);
        }

        $tenants = $query->get();

        if ($tenants->isEmpty()) {
            $this->error('No tenants found.');
            return self::FAILURE;
        }

        foreach ($tenants as $tenant) {
            $this->info("Migrating tenant database: {$tenant->database}");

            // تنظیم مشخصات اتصال برای دیتابیس مستاجر بر اساس کانکشن mysql
            $tenantConnection = config('database.connections.tenant');
            $tenantConnection['database'] = $tenant->database;

            Config::set('database.connections.tenant', $tenantConnection);

            DB::purge('tenant');
            DB::reconnect('tenant');

            // اجرای مایگریشن‌های پوشه tenant روی کانکشن tenant
            Artisan::call('migrate', [
                '--database' => 'tenant',
                '--path' => 'database/migrations/tenant',
                '--force' => true,
            ]);

            $this->line(Artisan::output());
        }

        $this->info('Tenant migrations completed successfully.');

        return self::SUCCESS;
    }
}
