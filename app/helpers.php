<?php

use App\Services\TenantContext;

if (! function_exists('tenant')) {
    function tenant()
    {
        return TenantContext::getTenant();
    }
}
