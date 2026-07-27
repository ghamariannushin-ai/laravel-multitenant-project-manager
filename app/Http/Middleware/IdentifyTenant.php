<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use App\Domain\Tenant\Models\Tenant;

class IdentifyTenant
{
    public function handle($request, Closure $next)
    {
        $host = $request->getHost();

        $tenant = Tenant::where('domain', $host)->first();

        if (! $tenant) {
            abort(404, 'Tenant not found for host: ' . $host);
        }

        Config::set('database.connections.tenant.database', $tenant->database);

        DB::purge('tenant');
        DB::reconnect('tenant');


        app()->instance('tenant', $tenant);

        return $next($request);
    }
}
