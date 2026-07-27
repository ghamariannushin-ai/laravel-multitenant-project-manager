<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Domain\Tenant\Models\TenantReport; // مطمئن شوید مسیر مدل درست است
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Http\Response;

class TenantReportController extends Controller
{
    // ... متدهای قبلی کنترلر شما ...

    /**
     * دانلود گزارش Tenant مشخص شده به صورت CSV
     *
     * @param TenantReport $report
     * @return StreamedResponse
     */
   public function downloadCsv(TenantReport $report): StreamedResponse
{
    // ۱. بررسی دقیق وضعیت گزارش برای راهنمایی بهتر در دیباگ
    if ($report->status === 'failed') {
        abort(Response::HTTP_BAD_REQUEST, 'این گزارش با خطا مواجه شده و قابل دانلود نیست.');
    }

    if ($report->status !== 'completed' || is_null($report->payload)) {
        abort(Response::HTTP_BAD_REQUEST, 'گزارش هنوز در حال پردازش است و آماده دانلود نیست.');
    }

    // ۲. تبدیل داده‌های payload به آرایه
    $data = is_array($report->payload) ? $report->payload : json_decode($report->payload, true);

    // نام فایل خروجی
    $fileName = 'tenant_report_' . $report->tenant_id . '_' . now()->format('Y_m_d_His') . '.csv';

    // ۳. ایجاد پاسخ استریم شده با کلیدهای واقعی دیتابیس شما
    $response = new StreamedResponse(function () use ($data) {
        $file = fopen('php://output', 'w');

        // هدر BOM برای پشتیبانی اکسل از زبان فارسی
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

        // هدر جدول
        fputcsv($file, [
            'شاخص (Metric)',
            'مقدار (Value)',
            'توضیحات (Description)'
        ]);

        // نوشتن اطلاعات کلی بر اساس کلیدهای واقعی دیتابیس شما
        if (isset($data['projects_count'])) {
            fputcsv($file, ['تعداد کل پروژه‌ها', $data['projects_count'], 'مجموع پروژه‌های تعریف شده در Tenant']);
        }

        if (isset($data['tasks_count'])) {
            fputcsv($file, ['تعداد کل وظایف', $data['tasks_count'], 'مجموع تسک‌های ثبت شده در سیستم']);
        }

        // اگر فیلد زمان تولید گزارش هم وجود دارد، اضافه شود
        if (isset($data['generated_at'])) {
            fputcsv($file, ['زمان تولید گزارش', $data['generated_at'], 'تاریخ و ساعت دقیق تولید داده‌ها']);
        }

        // اگر اطلاعات تفکیکی تسک‌ها وجود دارد
        if (isset($data['tasks_by_status']) && is_array($data['tasks_by_status'])) {
            foreach ($data['tasks_by_status'] as $status => $count) {
                fputcsv($file, [
                    "تعداد تسک‌های وضعیت: {$status}",
                    $count,
                    'تفکیک وضعیت تسک‌ها'
                ]);
            }
        }

        fclose($file);
    });

    // تنظیم هدرهای پاسخ HTTP
    $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
    $response->headers->set('Content-Disposition', 'attachment; filename="' . $fileName . '"');
    $response->headers->set('Pragma', 'no-cache');
    $response->headers->set('Expires', '0');

    return $response;
}
}
