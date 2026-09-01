<?php
declare(strict_types=1);
namespace Sierra\Middleware;

use Sierra\Http\Request;
use Sierra\Http\Response;
use Sierra\Container\Container;

final class Stack
{
    public function __construct(private Container $container) {}

    public function run(array $middlewares, Request $request, callable $coreHandler): Response
    {
        $next = $coreHandler;

        foreach (array_reverse($middlewares) as $mw) {
            $next = function(Request $req) use ($mw, $next) {
                $instance = is_string($mw) ? $this->container->get($mw) : $mw;
                if ($instance instanceof MiddlewareInterface) {
                    return $instance->process($req, $next);
                }
                // closure middleware
                if (is_callable($instance)) {
                    return $instance($req, $next);
                }
                return $next($req);
            };
        }

        return $next($request);
    }
}
