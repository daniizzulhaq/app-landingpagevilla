<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class SetPublicLocale
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->is('admin','admin/*','media/*')) {
            app()->setLocale('en');
            return $next($request);
        }
        $requested=$request->query('lang');
        if ($requested!==null && (!is_string($requested) || !in_array($requested,['en','id'],true))) abort(400,'Unsupported language.');
        $locale=$requested ?? $request->cookie('propertyme_locale','en');
        if (!in_array($locale,['en','id'],true)) $locale='en';
        app()->setLocale($locale);
        if ($requested!==null) Cookie::queue(cookie('propertyme_locale',$locale,525600,null,null,$request->isSecure(),true,false,'lax'));
        $response=$next($request);
        $response->headers->set('Content-Language',$locale);
        $response->setVary('Cookie',false);
        return $response;
    }
}
