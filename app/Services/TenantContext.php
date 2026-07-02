<?php

namespace App\Services;

use App\Domain\Tenant\Models\Tenant;

class TenantContext
{
    protected static $tenant;

    public static function setTenant(Tenant $tenant)
    {
        self::$tenant = $tenant;
    }

    public static function getTenant()
    {
        return self::$tenant;
    }
}
