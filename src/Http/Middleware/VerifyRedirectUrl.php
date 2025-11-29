<?php

declare(strict_types=1);

namespace TapPay\Tap\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

use function in_array;
use function is_string;
use function parse_url;
use function preg_match;
use function str_starts_with;
use function strtolower;

class VerifyRedirectUrl
{
    /**
     * @throws AccessDeniedHttpException
     */
    public function handle(Request $request, Closure $next): Response
    {
        $redirect = $request->query('redirect');

        if (
            is_string($redirect)
            && $redirect !== ''
            && ! $this->isValidRedirectUrl($redirect, $request->getHost())
        ) {
            throw new AccessDeniedHttpException('Invalid redirect URL.');
        }

        return $next($request);
    }

    protected function isValidRedirectUrl(string $redirect, string $currentHost): bool
    {
        $currentHostLower = strtolower($currentHost);

        if (str_starts_with($redirect, '//')) {
            $url = parse_url('https:' . $redirect);

            return $url !== false
                && isset($url['host'])
                && strtolower($url['host']) === $currentHostLower;
        }

        if (preg_match('/^[a-zA-Z][a-zA-Z0-9+.-]*:/i', $redirect)) {
            $url = parse_url($redirect);

            return $url !== false
                && isset($url['scheme'])
                && in_array(strtolower($url['scheme']), ['http', 'https'], true)
                && ! empty($url['host'])
                && strtolower($url['host']) === $currentHostLower;
        }

        return true;
    }
}
