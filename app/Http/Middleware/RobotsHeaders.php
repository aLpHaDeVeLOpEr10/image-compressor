<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sends X-Robots-Tag for responses that must never be indexed: every response when the site is not
 * indexable (staging, local), and utility endpoints such as the health check and the admin area in production.
 */
class RobotsHeaders
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! config('site.indexable')) {
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
        } elseif ($request->is('admin', 'admin/*', config('site.admin_login_path'))) {
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
        } elseif ($request->is('up', 'process/*', 'storage/*')) {
            $response->headers->set('X-Robots-Tag', 'noindex');
        }

        return $response;
    }
}
