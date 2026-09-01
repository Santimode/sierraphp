<?php
declare(strict_types=1);
namespace Sierra\Support\Facades;

use Sierra\Router\Route as RouteObject;

/**
 * @method static RouteObject get(string $uri, mixed $handler)
 * @method static RouteObject post(string $uri, mixed $handler)
 * @method static RouteObject put(string $uri, mixed $handler)
 * @method static RouteObject patch(string $uri, mixed $handler)
 * @method static RouteObject delete(string $uri, mixed $handler)
 * @method static void group(string $prefix, \Closure $callback)
 */
final class Route
{
    public static function __callStatic(string $method, array $args): mixed
    {
        global $sierraApp;
        if (!$sierraApp) throw new \RuntimeException("Application not booted");
        $router = $sierraApp->getRouter();
        return $router->$method(...$args);
    }
}
