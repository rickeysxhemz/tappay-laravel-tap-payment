<?php

declare(strict_types=1);

namespace TapPay\Tap\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class VerifyRedirectUrl
{
    /**
     * Handle an incoming request.
     *
     * @throws AccessDeniedHttpException
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($redirect = $request->query('redirect')) {
            $url = parse_url($redirect);

            if (isset($url['host']) && strtolower($url['host']) !== strtolower($request->getHost())) {
                throw new AccessDeniedHttpException('Invalid redirect URL.');
            }
        }

        return $next($request);
    }
}