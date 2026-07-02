<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use App\Services\DatabaseManager;

class CreateTenant extends Command
{
    protected $signature = 'tenant:create {name} {domain}';
    protected $description = 'Create a new tenant';

    public function handle()
    {
        $name = $this->argument('name');
        $domain = $this->argument('domain');

        $database = 'tenant_' . strtolower($name);

        if (Tenant::where('domain', $domain)->exists()) {
            $this->error("Domain already exists.");
            return;
        }

        DB::statement("CREATE DATABASE `$database`");

        $tenant = Tenant::create([
            'name' => $name,
            'domain' => $domain,
            'database' => $database,
        ]);

        DatabaseManager::connectToTenant($tenant->database);

        Artisan::call('migrate', [
    '--path' => 'database/migrations/tenant',
    '--database' => 'tenant',
    '--force' => true,
]);

    Artisan::call('db:seed', [
    '--class' => 'Database\\Seeders\\TenantDatabaseSeeder',
    '--database' => 'tenant',
    '--force' => true,
]);

        $this->info("Tenant {$name} created successfully.");
    }
}
