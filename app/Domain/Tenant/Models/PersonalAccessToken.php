<?php

namespace App\Domain\Tenant\Models;

use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;

class PersonalAccessToken extends SanctumPersonalAccessToken
{
    // این خط بسیار حیاتی است تا لاراول بفهمد توکن‌ها در دیتابیس تنانت هستند نه دیتابیس مرکزی
    protected $connection = 'tenant';
}
