<?php

namespace App\Domain\Tenant\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Tenant extends Model
{
    protected $fillable = [
        'name',
        'domain',
        'database',
    ];

    /**
     * تنظیم کانکشن دیتابیس تنانت به عنوان دیتابیس فعال
     */
    public function makeCurrent(): void
    {
        // ۱. مقدار پایگاه داده تنانت را در کانفیگ قرار بده
        config(['database.connections.tenant.database' => $this->database]);

        // ۲. پاک کردن کش کانکشن
        DB::purge('tenant');

        // ۳. ریکانکت شدن با دیتابیس تنانت
        DB::reconnect('tenant');

        // ۴. قرار دادن کانکشن پیش‌فرض روی کانکشن تنانت
        DB::setDefaultConnection('tenant');
    }

    /**
     * بازگشت به دیتابیس مرکزی (Landlord)
     */
    public function forgetCurrent(): void
    {
        // بازگرداندن کانکشن پیش‌فرض به mysql (دیتابیس مرکزی)
        DB::setDefaultConnection('mysql');

        // پاک کردن کش کانکشن تنانت
        DB::purge('tenant');
    }
}
