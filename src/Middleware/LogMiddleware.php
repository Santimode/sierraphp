<?php
declare(strict_types=1);
namespace Sierra\Middleware;

use Sierra\Http\Request;
use Sierra\Http\Response;

final class LogMiddleware implements MiddlewareInterface
{
    public function process(Request $request, callable $next): Response
    {
        $start = microtime(true);
        $response = $next($request);
        $time = round((microtime(true) - $start) * 1000, 2);
        
        error_log(sprintf(
            '[sierraPHP] %s %s -> %d (%s ms)',
            $request->getMethod(),
            $request->getUri(),
            $response->getStatusCode(),
            $time
        ));

        return $response->header('X-Sierra-Time', $time . 'ms');
    }
}
