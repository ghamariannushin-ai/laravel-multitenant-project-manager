<?php

use App\Services\TenantContext;

function tenant()
{
    return TenantContext::getTenant();
}
