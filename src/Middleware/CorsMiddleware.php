<?php
declare(strict_types=1);
namespace Sierra\Middleware;

use Sierra\Http\Request;
use Sierra\Http\Response;

final class CorsMiddleware implements MiddlewareInterface
{
    /**
     * @param string[] $allowedOrigins
     * @param string[] $allowedMethods
     * @param string[] $allowedHeaders
     * @param string[] $exposedHeaders
     */
    public function __construct(
        private array $allowedOrigins = ['*'],
        private array $allowedMethods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
        private array $allowedHeaders = ['Content-Type', 'Authorization', 'X-Requested-With', 'X-HTTP-Method-Override', 'Accept'],
        private array $exposedHeaders = [],
        private bool $allowCredentials = false,
        private int $maxAge = 86400,
    ) {}

    public function process(Request $request, callable $next): Response
    {
        $origin = $request->header('Origin', '*');
        $isAllowedOrigin = in_array('*', $this->allowedOrigins, true) || in_array($origin, $this->allowedOrigins, true);
        $allowOriginValue = $isAllowedOrigin ? (in_array('*', $this->allowedOrigins, true) ? '*' : $origin) : '';

        $corsHeaders = [
            'Access-Control-Allow-Origin' => $allowOriginValue,
            'Access-Control-Allow-Methods' => implode(', ', $this->allowedMethods),
            'Access-Control-Allow-Headers' => implode(', ', $this->allowedHeaders),
            'Access-Control-Max-Age' => (string)$this->maxAge,
        ];

        if ($this->allowCredentials) {
            $corsHeaders['Access-Control-Allow-Credentials'] = 'true';
        }

        if (!empty($this->exposedHeaders)) {
            $corsHeaders['Access-Control-Expose-Headers'] = implode(', ', $this->exposedHeaders);
        }

        // Handle preflight OPTIONS request
        if ($request->isMethod('OPTIONS')) {
            return new Response('', 204, $corsHeaders);
        }

        $response = $next($request);

        foreach ($corsHeaders as $header => $value) {
            if ($value !== '') {
                $response->header($header, $value);
            }
        }

        return $response;
    }
}
