<?php

namespace Tests\Feature;

use App\Domain\Tenant\Jobs\ProcessTenantReport;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Tenant\Models\TenantReport;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TenantReportTest extends TestCase
{
    protected string $centralPath;
    protected string $tenantPath;
    protected string $tenantDomain = 'alpha.taskino.test';
    protected Tenant $tenant;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $testId = str_replace('.', '_', uniqid('tenant_report_', true));

        $this->centralPath = database_path("testing/central_{$testId}.sqlite");
        $this->tenantPath = database_path("testing/tenant_{$testId}.sqlite");

        $testingDirectory = dirname($this->centralPath);

        if (! is_dir($testingDirectory)) {
            mkdir($testingDirectory, 0777, true);
        }

        touch($this->centralPath);
        touch($this->tenantPath);

        Config::set('app.url', "http://{$this->tenantDomain}");

        // تنظیم اتصال پیش‌فرض برای اجرای تست‌ها
        Config::set('database.default', 'central');

        Config::set('database.connections.central', [
            'driver' => 'sqlite',
            'database' => $this->centralPath,
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);

        Config::set('database.connections.tenant', [
            'driver' => 'sqlite',
            'database' => $this->tenantPath,
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);

        DB::purge('central');
        DB::purge('tenant');

        DB::reconnect('central');
        DB::reconnect('tenant');

        $this->artisan('migrate', [
            '--database' => 'central',
            '--path' => 'database/migrations',
            '--realpath' => false,
        ])->assertExitCode(0);

        $this->artisan('migrate', [
            '--database' => 'tenant',
            '--path' => 'database/migrations/tenant',
            '--realpath' => false,
        ])->assertExitCode(0);

        $this->tenant = Tenant::on('central')->create([
            'name' => 'Alpha Tenant',
            'domain' => $this->tenantDomain,
            'database' => $this->tenantPath,
        ]);

        $this->user = User::on('tenant')->create([
            'name' => 'Test User',
            'email' => 'test@alpha.com',
            'password' => bcrypt('password'),
        ]);
    }

    protected function tearDown(): void
    {
        DB::disconnect('tenant');
        DB::disconnect('central');

        if (isset($this->centralPath) && file_exists($this->centralPath)) {
            @unlink($this->centralPath);
        }

        if (isset($this->tenantPath) && file_exists($this->tenantPath)) {
            @unlink($this->tenantPath);
        }

        parent::tearDown();
    }

    protected function tenantUrl(string $path): string
    {
        return "http://{$this->tenantDomain}" . '/' . ltrim($path, '/');
    }

    public function test_a_user_can_request_a_report_generation_which_is_queued(): void
    {
        Queue::fake();

        Sanctum::actingAs($this->user);

        $this->assertDatabaseHas('tenants', [
            'id' => $this->tenant->id,
            'domain' => $this->tenantDomain,
        ], 'central');

        $centralTenant = Tenant::on('central')
            ->where('domain', $this->tenantDomain)
            ->first();

        $this->assertNotNull($centralTenant);

        $response = $this->postJson($this->tenantUrl('/api/v1/tenant/reports'));

    $response->assertStatus(202)
    ->assertJsonStructure([
        'message',
        'data' => [
            'id',
            'status',
            'status_url',
            'download_url',
        ],
    ]);

$reportId = $response->json('data.id');

        $this->assertDatabaseHas('tenant_reports', [
            'id' => $reportId,
            'status' => 'queued',
        ], 'central');

        Queue::assertPushed(ProcessTenantReport::class);
    }

    public function test_a_user_can_check_report_status_and_download_completed_report(): void
    {
        $this->assertDatabaseHas('tenants', [
            'id' => $this->tenant->id,
            'domain' => $this->tenantDomain,
            'database' => $this->tenantPath,
        ], 'central');

        Sanctum::actingAs($this->user);

        $report = TenantReport::on('central')->create([
            'tenant_id' => $this->tenant->id,
            'type' => 'projects_summary',
            'status' => 'completed',
            'payload' => [
                'projects_count' => 5,
                'tasks_count' => 10,
                'generated_at' => now()->toIso8601String(),
            ],
            'started_at' => now()->subSeconds(2),
            'completed_at' => now(),
        ]);

        $statusResponse = $this->getJson($this->tenantUrl("/api/v1/tenant/reports/{$report->id}/status"));

        $statusResponse->assertOk()
            ->assertJsonPath('data.status', 'completed');

        $downloadResponse = $this->get($this->tenantUrl("/api/v1/tenant/reports/{$report->id}/download-csv"));

        $downloadResponse->assertOk();
    }
}
