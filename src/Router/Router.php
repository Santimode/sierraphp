<?php
declare(strict_types=1);
namespace Sierra\Router;

use FastRoute\RouteCollector;
use FastRoute\Dispatcher;
use function FastRoute\simpleDispatcher;

final class Router
{
    /** @var Route[] */
    private array $routes = [];
    private string $groupPrefix = '';
    private array $groupMiddleware = [];
    private bool $cacheEnabled = false;
    private string $cacheFile = '';

    public function setCacheConfig(bool $enabled, string $file): void
    {
        $this->cacheEnabled = $enabled;
        $this->cacheFile = $file;
    }

    public function getNamedRoute(string $name): ?Route
    {
        foreach ($this->routes as $route) {
            if ($route->name === $name) {
                return $route;
            }
        }
        return null;
    }

    public function generateUrl(string $name, array $params = []): string
    {
        $route = $this->getNamedRoute($name);
        if (!$route) {
            throw new \RuntimeException("Route [{$name}] not found");
        }

        $uri = $route->uri;
        foreach ($params as $key => $value) {
            $uri = preg_replace('/\{' . $key . '(:[^\}]+)?\}/', (string)$value, $uri);
        }

        if (preg_match('/\{[a-zA-Z0-9_]+(:[^\}]+)?\}/', $uri, $matches)) {
            throw new \RuntimeException("Missing parameter [{$matches[0]}] for route [{$name}]");
        }

        return $uri;
    }

    public function add(string $method, string $uri, mixed $handler): Route
    {
        $normalizedUri = '/' . ltrim($uri, '/');
        if ($normalizedUri !== '/' && str_ends_with($normalizedUri, '/')) {
            $normalizedUri = rtrim($normalizedUri, '/');
        }

        $fullUri = $this->groupPrefix . ($normalizedUri === '/' && $this->groupPrefix !== '' ? '' : $normalizedUri);
        $fullUri = '/' . ltrim($fullUri, '/');

        $route = new Route(strtoupper($method), $fullUri, $handler);
        if (!empty($this->groupMiddleware)) {
            $route->middleware($this->groupMiddleware);
        }

        $this->routes[] = $route;
        return $route;
    }

    public function get(string $uri, mixed $handler): Route { return $this->add('GET', $uri, $handler); }
    public function post(string $uri, mixed $handler): Route { return $this->add('POST', $uri, $handler); }
    public function put(string $uri, mixed $handler): Route { return $this->add('PUT', $uri, $handler); }
    public function patch(string $uri, mixed $handler): Route { return $this->add('PATCH', $uri, $handler); }
    public function delete(string $uri, mixed $handler): Route { return $this->add('DELETE', $uri, $handler); }
    public function options(string $uri, mixed $handler): Route { return $this->add('OPTIONS', $uri, $handler); }
    public function head(string $uri, mixed $handler): Route { return $this->add('HEAD', $uri, $handler); }

    /**
     * @param string[] $methods
     * @return Route[]
     */
    public function match(array $methods, string $uri, mixed $handler): array
    {
        $routes = [];
        foreach ($methods as $method) {
            $routes[] = $this->add(strtoupper($method), $uri, $handler);
        }
        return $routes;
    }

    /** @return Route[] */
    public function any(string $uri, mixed $handler): array
    {
        return $this->match(['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS', 'HEAD'], $uri, $handler);
    }

    public function group(string|array $attributes, \Closure $callback): void
    {
        $prefix = is_array($attributes) ? ($attributes['prefix'] ?? '') : $attributes;
        $middleware = is_array($attributes) ? (array)($attributes['middleware'] ?? []) : [];

        $prevPrefix = $this->groupPrefix;
        $prevMiddleware = $this->groupMiddleware;

        $normalizedPrefix = '/' . trim($prefix, '/');
        $this->groupPrefix = $prevPrefix . ($normalizedPrefix === '/' ? '' : $normalizedPrefix);
        $this->groupMiddleware = array_merge($prevMiddleware, $middleware);

        $callback($this);

        $this->groupPrefix = $prevPrefix;
        $this->groupMiddleware = $prevMiddleware;
    }

    /** @return Route[] */
    public function getRoutes(): array { return $this->routes; }

    public function dispatch(string $method, string $uri): array
    {
        $dispatcher = \FastRoute\cachedDispatcher(function(RouteCollector $r) {
            foreach ($this->routes as $index => $route) {
                $routeUri = $route->uri;
                foreach ($route->wheres as $param => $regex) {
                    $routeUri = preg_replace('/\{(' . $param . ')(:[^\}]+)?\}/', '{$1:' . $regex . '}', $routeUri);
                }
                $r->addRoute($route->method, $routeUri, $index);
            }
        }, [
            'cacheFile' => $this->cacheFile ?: sys_get_temp_dir() . '/routes.cache',
            'cacheDisabled' => !$this->cacheEnabled,
        ]);

        $routeInfo = $dispatcher->dispatch(strtoupper($method), $uri);

        if ($routeInfo[0] === Dispatcher::FOUND) {
            $index = $routeInfo[1];
            $routeInfo[1] = $this->routes[$index] ?? null;
        }

        return $routeInfo;
    }
}
