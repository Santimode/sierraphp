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

    public function add(string $method, string $uri, mixed $handler): Route
    {
        $fullUri = $this->groupPrefix . $uri;
        $route = new Route($method, $fullUri, $handler);
        $this->routes[] = $route;
        return $route;
    }

    public function get(string $uri, mixed $handler): Route { return $this->add('GET', $uri, $handler); }
    public function post(string $uri, mixed $handler): Route { return $this->add('POST', $uri, $handler); }
    public function put(string $uri, mixed $handler): Route { return $this->add('PUT', $uri, $handler); }
    public function patch(string $uri, mixed $handler): Route { return $this->add('PATCH', $uri, $handler); }
    public function delete(string $uri, mixed $handler): Route { return $this->add('DELETE', $uri, $handler); }

    public function group(string $prefix, \Closure $callback): void
    {
        $prev = $this->groupPrefix;
        $this->groupPrefix .= $prefix;
        $callback($this);
        $this->groupPrefix = $prev;
    }

    /** @return Route[] */
    public function getRoutes(): array { return $this->routes; }

    public function dispatch(string $method, string $uri): array
    {
        $dispatcher = simpleDispatcher(function(RouteCollector $r) {
            foreach ($this->routes as $route) {
                $r->addRoute($route->method, $route->uri, $route);
            }
        });

        return $dispatcher->dispatch($method, $uri);
    }
}
