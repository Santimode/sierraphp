<?php
declare(strict_types=1);
namespace Sierra;

use Sierra\Container\Container;
use Sierra\Router\Router;
use Sierra\Http\Request;
use Sierra\Http\Response;
use Sierra\Middleware\Stack;

final class Application
{
    private Container $container;
    private Router $router;
    private string $basePath;
    private array $config = [];

    public function __construct(string $basePath)
    {
        $this->basePath = rtrim($basePath, '/');
        $this->container = new Container();
        $this->router = new Router();

        $this->container->instance(Container::class, $this->container);
        $this->container->instance(self::class, $this);
        $this->container->instance(Router::class, $this->router);

        // load .env if exists
        $envFile = $this->basePath . '/.env';
        if (file_exists($envFile) && class_exists(\Dotenv\Dotenv::class)) {
            \Dotenv\Dotenv::createImmutable($this->basePath)->safeLoad();
        }

        // load config
        $configFile = $this->basePath . '/config/app.php';
        if (file_exists($configFile)) {
            $this->config = require $configFile;
        }
    }

    public static function create(string $basePath): self
    {
        global $sierraApp;
        $app = new self($basePath);
        $sierraApp = $app;
        return $app;
    }

    public function getContainer(): Container { return $this->container; }
    public function getRouter(): Router { return $this->router; }
    public function getBasePath(): string { return $this->basePath; }

    public function getConfig(?string $key = null): mixed
    {
        if ($key === null) return $this->config;
        $parts = explode('.', $key);
        $val = $this->config;
        foreach ($parts as $p) {
            if (!is_array($val) || !array_key_exists($p, $val)) return null;
            $val = $val[$p];
        }
        return $val;
    }

    // proxy helpers for routes
    public function get(string $uri, mixed $handler) { return $this->router->get($uri, $handler); }
    public function post(string $uri, mixed $handler) { return $this->router->post($uri, $handler); }

    public function run(?Request $request = null): void
    {
        $request = $request ?? Request::fromGlobals();
        global $sierraRequest;
        $sierraRequest = $request;

        $routeInfo = $this->router->dispatch($request->getMethod(), $request->getUri());

        switch ($routeInfo[0]) {
            case \FastRoute\Dispatcher::NOT_FOUND:
                (new Response("404 Not Found", 404))->send();
                return;
            case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                (new Response("405 Method Not Allowed", 405))->send();
                return;
            case \FastRoute\Dispatcher::FOUND:
                /** @var \Sierra\Router\Route $route */
                $route = $routeInfo[1];
                $vars = $routeInfo[2];
                $request = $request->withAttributes($vars);

                $stack = new Stack($this->container);
                $core = function(Request $req) use ($route, $vars) {
                    $handler = $route->handler;
                    // Closure
                    if ($handler instanceof \Closure) {
                        $result = $handler(...array_merge([$req], array_values($vars)));
                    }
                    // [Class, method]
                    elseif (is_array($handler)) {
                        $controller = $this->container->get($handler[0]);
                        $result = $controller->{$handler[1]}(...array_merge([$req], array_values($vars)));
                    }
                    // callable string
                    elseif (is_callable($handler)) {
                        $result = $handler($req);
                    } else {
                        $result = $handler;
                    }

                    if ($result instanceof Response) return $result;
                    if (is_array($result)) return (new Response())->json($result);
                    return new Response((string)$result);
                };

                $response = $stack->run($route->middleware, $request, $core);
                $response->send();
                return;
        }
    }
}
