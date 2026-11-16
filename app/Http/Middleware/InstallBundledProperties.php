<?php

namespace App\Http\Middleware;

use App\Services\BundledPropertyInstaller;
use Closure;
use Illuminate\Http\Request;

class InstallBundledProperties
{
    public function __construct(private BundledPropertyInstaller $installer) {}

    public function handle(Request $request, Closure $next)
    {
        if (! app()->runningInConsole() && app()->isProduction()) {
            $this->installer->install();
        }
        return $next($request);
    }
}
