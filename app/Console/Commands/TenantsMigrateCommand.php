<?php

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class TenantsMigrateCommand extends Command
{
    protected $signature = 'tenants:migrate {tenant?}';
    protected $description = 'Run migrations for tenant databases';

    public function handle(): int
    {
        $tenants = [
            'alpha' => 'tenant_alpha',
            'beta' => 'tenant_beta',
        ];

        $selectedTenant = $this->argument('tenant');

        if ($selectedTenant) {
            if (! isset($tenants[$selectedTenant])) {
                $this->error("Tenant [{$selectedTenant}] not found.");
                return self::FAILURE;
            }

            $tenants = [$selectedTenant => $tenants[$selectedTenant]];
        }

        foreach ($tenants as $tenant => $database) {
            $this->info("Migrating tenant [{$tenant}] database [{$database}]...");

            DB::purge('tenant');

            Config::set('database.connections.tenant.database', $database);

            DB::reconnect('tenant');

            Artisan::call('migrate', [
                '--database' => 'tenant',
                '--path' => 'database/migrations',
                '--force' => true,
            ]);

            $this->line(Artisan::output());
        }

        return self::SUCCESS;
    }
}
