<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ProjectController;
use App\Http\Controllers\Api\V1\TaskController;
use App\Http\Controllers\Api\V1\TenantReportController;
use App\Http\Middleware\IdentifyTenant;

Route::prefix('v1')->group(function () {
    // عمومی
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware([
    IdentifyTenant::class,
    'auth:sanctum',
])->group(function () {
    Route::prefix('tenant')->group(function () {
        Route::post('reports', [TenantReportController::class, 'generate'])
            ->name('tenant-reports.generate');

        Route::get('reports/{report}/status', [
            TenantReportController::class,
            'status',
        ])->name('tenant-reports.status');

Route::get('reports/{report}/download-csv', [TenantReportController::class, 'downloadCsv'])
    ->name('tenant-reports.download-csv');
    });
});
});
