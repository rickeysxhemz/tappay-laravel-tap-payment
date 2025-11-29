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
     * Allowed URL schemes for redirects
     */
    private const ALLOWED_SCHEMES = ['http', 'https', ''];

    /**
     * Handle an incoming request.
     *
     * @throws AccessDeniedHttpException
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($redirect = $request->query('redirect')) {
            if (! $this->isValidRedirectUrl($redirect, $request->getHost())) {
                throw new AccessDeniedHttpException('Invalid redirect URL.');
            }
        }

        return $next($request);
    }

    /**
     * Validate that the redirect URL is safe.
     */
    protected function isValidRedirectUrl(string $redirect, string $currentHost): bool
    {
        $url = parse_url($redirect);

        // Block dangerous schemes (javascript:, data:, vbscript:, etc.)
        if (isset($url['scheme']) && ! in_array(strtolower($url['scheme']), self::ALLOWED_SCHEMES, true)) {
            return false;
        }

        // If host is specified, it must match current host
        if (isset($url['host']) && strtolower($url['host']) !== strtolower($currentHost)) {
            return false;
        }

        return true;
    }
}
