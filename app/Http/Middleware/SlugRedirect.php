<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class SlugRedirect
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->isMethod('GET') && ! $request->is('admin', 'admin/*')) {
            $path = DB::table('slug_redirects')->where('from_path', '/'.$request->path())->value('to_path');
            if ($path) { return redirect($path, 301); }
        }
        return $next($request);
    }
}
