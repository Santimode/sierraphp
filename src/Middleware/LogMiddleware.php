<?php
declare(strict_types=1);
namespace Sierra\Middleware;

use Sierra\Http\Request;
use Sierra\Http\Response;
use Sierra\Log\LoggerInterface;

final class LogMiddleware implements MiddlewareInterface
{
    public function __construct(private ?LoggerInterface $logger = null) {}

    public function process(Request $request, callable $next): Response
    {
        $start = microtime(true);
        $response = $next($request);
        $time = round((microtime(true) - $start) * 1000, 2);

        $msg = sprintf(
            '[sierraPHP] %s %s -> %d (%s ms)',
            $request->getMethod(),
            $request->getUri(),
            $response->getStatusCode(),
            $time
        );

        if ($this->logger) {
            $this->logger->info($msg, [
                'method' => $request->getMethod(),
                'uri' => $request->getUri(),
                'status' => $response->getStatusCode(),
                'duration_ms' => $time,
            ]);
        } else {
            error_log($msg);
        }

        return $response->header('X-Sierra-Time', $time . 'ms');
    }
}
