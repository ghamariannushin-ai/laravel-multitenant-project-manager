<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ProjectController;
use App\Http\Controllers\Api\V1\TaskController;
use App\Http\Controllers\Api\V1\TenantReportController;

Route::prefix('v1')->group(function () {
    // مسیرهای عمومی
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    // مسیرهای محافظت‌شده
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);

        Route::apiResource('projects', ProjectController::class);
        Route::apiResource('tasks', TaskController::class);

        Route::post(
            '/tenant/reports',
            [TenantReportController::class, 'generate']
        )->name('tenant-reports.generate');

        Route::get(
            '/tenant/reports/{report}/status',
            [TenantReportController::class, 'status']
        )->name('tenant-reports.status');

        Route::get('tenant-reports/{report}/download-csv', [TenantReportController::class, 'downloadCsv'])
        ->name('tenant-reports.download-csv');
        });
});
