<?php

namespace App\Http\Controllers\Api\V1;
use App\Domain\Tenant\Models\Tenant;
use App\Http\Controllers\Controller;
use App\Domain\Tenant\Jobs\ProcessTenantReport;
use App\Domain\Tenant\Models\TenantReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TenantReportController extends Controller
{
    public function generate(): JsonResponse
    {
        $tenantId = null;

        if (function_exists('tenant')) {
            $tenantId = tenant('id');
        }

        if (!$tenantId) {
            $user = auth()->user();
            $tenantId = $user ? ($user->tenant_id ?? null) : null;
        }

        if (!$tenantId) {
            $host = request()->getHost();
            $tenant = DB::connection('central')
                ->table('tenants')
                ->where('domain', $host)
                ->first();

            $tenantId = $tenant ? $tenant->id : null;
        }

        if (!$tenantId) {
            return response()->json([
                'error' => 'Tenant ID not found.'
            ], 400);
        }

        $report = TenantReport::create([
            'tenant_id' => $tenantId,
            'status' => 'queued',
        ]);

        ProcessTenantReport::dispatch((int) $tenantId, (int) $report->id);

        return response()->json([
            'message' => 'Report queued successfully.',
            'data' => [
                'id' => $report->id,
                'status' => $report->status,
                'status_url' => route('tenant-reports.status', ['report' => $report->id]),
                'download_url' => route('tenant-reports.download-csv', ['report' => $report->id]),
            ],
        ], 202);
    }

    public function status(TenantReport $report): JsonResponse
    {
        return response()->json([
            'data' => [
                'id' => $report->id,
                'status' => $report->status,
                'payload' => $report->payload,
                'error_message' => $report->error_message,
                'started_at' => $report->started_at,
                'completed_at' => $report->completed_at,
                'download_url' => $report->status === 'completed'
                    ? route('tenant-reports.download-csv', ['report' => $report->id])
                    : null,
            ],
        ]);
    }

public function downloadCsv(int $report)
{
    $tenant = app(Tenant::class);

    $tenantReport = TenantReport::on('central')
        ->whereKey($report)
        ->where('tenant_id', $tenant->id)
        ->firstOrFail();

    if ($tenantReport->status !== 'completed') {
        return response()->json([
            'message' => 'Report is not completed.',
        ], 400);
    }

    $payload = $tenantReport->payload;

    if (! is_array($payload)) {
        return response()->json([
            'message' => 'Report payload is invalid.',
        ], 400);
    }

    $filename = "tenant-report-{$tenantReport->id}.csv";

    return response()->streamDownload(
        function () use ($payload): void {
            $stream = fopen('php://output', 'wb');

            fputcsv($stream, [
                'projects_count',
                'tasks_count',
                'generated_at',
            ]);

            fputcsv($stream, [
                $payload['projects_count'] ?? 0,
                $payload['tasks_count'] ?? 0,
                $payload['generated_at'] ?? '',
            ]);

            fclose($stream);
        },
        $filename,
        [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]
    );
}

}
