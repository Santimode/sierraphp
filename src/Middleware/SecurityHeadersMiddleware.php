<?php
declare(strict_types=1);
namespace Sierra\Middleware;

use Sierra\Http\Request;
use Sierra\Http\Response;

final class SecurityHeadersMiddleware implements MiddlewareInterface
{
    /**
     * @param array<string, string> $customHeaders
     */
    public function __construct(private array $customHeaders = []) {}

    public function process(Request $request, callable $next): Response
    {
        $response = $next($request);

        $defaultHeaders = [
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'SAMEORIGIN',
            'X-XSS-Protection' => '1; mode=block',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Permissions-Policy' => 'geolocation=(), camera=(), microphone=()',
        ];

        $headers = array_merge($defaultHeaders, $this->customHeaders);

        foreach ($headers as $name => $value) {
            $response->header($name, $value);
        }

        return $response;
    }
}
