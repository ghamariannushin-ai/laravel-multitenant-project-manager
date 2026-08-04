<?php

namespace App\Http\Middleware;

use App\Domain\Tenant\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class IdentifyTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();

        // استفاده مستقیم از DB::connection برای دور زدن مشکلات کانکشن پیش‌فرض Eloquent
        $tenantData = DB::connection('central')
            ->table('tenants')
            ->where('domain', $host)
            ->first();

        if (! $tenantData) {
            abort(404, "Tenant not found for host: {$host}");
        }

        // ساخت مجدد نمونه کلاس مدل بر اساس داده‌های دریافت شده
        $tenant = new Tenant();
        $tenant->forceFill((array) $tenantData);
        $tenant->exists = true;

        Config::set(
            'database.connections.tenant.database',
            $tenant->database
        );

        DB::purge('tenant');
        DB::reconnect('tenant');

        app()->instance('currentTenant', $tenant);
        app()->instance(Tenant::class, $tenant);

        return $next($request);
    }
}
