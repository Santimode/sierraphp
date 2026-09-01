<?php
declare(strict_types=1);
namespace Sierra;

use Sierra\Container\Container;
use Sierra\Router\Router;
use Sierra\Http\Request;
use Sierra\Http\Response;
use Sierra\Middleware\Stack;
use Sierra\Exceptions\Handler;
use Sierra\Log\Logger;
use Sierra\Log\LoggerInterface;

final class Application
{
    private Container $container;
    private Router $router;
    private string $basePath;
    private array $config = [];
    private Handler $exceptionHandler;
    private LoggerInterface $logger;

    public function __construct(string $basePath)
    {
        $this->basePath = rtrim($basePath, '/');
        $this->container = new Container();
        $this->router = new Router();

        $this->container->instance(Container::class, $this->container);
        $this->container->instance(self::class, $this);
        $this->container->instance(Router::class, $this->router);

        if (file_exists($this->basePath . '/.env') && class_exists(\Dotenv\Dotenv::class)) {
            \Dotenv\Dotenv::createImmutable($this->basePath)->safeLoad();
        }

        $configFile = $this->basePath . '/config/app.php';
        if (file_exists($configFile)) {
            $this->config = require $configFile;
        }

        $logPath = $this->config['log_path'] ?? ($this->basePath . '/storage/logs/sierra.log');
        $this->logger = new Logger($logPath);
        $this->container->instance(LoggerInterface::class, $this->logger);
        $this->container->instance(Logger::class, $this->logger);

        $debug = (bool)($this->config['debug'] ?? env('APP_DEBUG', true));
        $this->exceptionHandler = new Handler($debug, $this->logger);
        $this->container->instance(Handler::class, $this->exceptionHandler);
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

    public function get(string $uri, mixed $handler): \Sierra\Router\Route { return $this->router->get($uri, $handler); }
    public function post(string $uri, mixed $handler): \Sierra\Router\Route { return $this->router->post($uri, $handler); }
    public function put(string $uri, mixed $handler): \Sierra\Router\Route { return $this->router->put($uri, $handler); }
    public function patch(string $uri, mixed $handler): \Sierra\Router\Route { return $this->router->patch($uri, $handler); }
    public function delete(string $uri, mixed $handler): \Sierra\Router\Route { return $this->router->delete($uri, $handler); }
    public function options(string $uri, mixed $handler): \Sierra\Router\Route { return $this->router->options($uri, $handler); }
    public function head(string $uri, mixed $handler): \Sierra\Router\Route { return $this->router->head($uri, $handler); }

    /**
     * @param string[] $methods
     * @return \Sierra\Router\Route[]
     */
    public function match(array $methods, string $uri, mixed $handler): array { return $this->router->match($methods, $uri, $handler); }

    /** @return \Sierra\Router\Route[] */
    public function any(string $uri, mixed $handler): array { return $this->router->any($uri, $handler); }

    public function group(string|array $attributes, \Closure $callback): void { $this->router->group($attributes, $callback); }

    public function run(?Request $request = null): void
    {
        try {
            $request = $request ?? Request::fromGlobals();
            global $sierraRequest;
            $sierraRequest = $request;

            $routeInfo = $this->router->dispatch($request->getMethod(), $request->getUri());

            switch ($routeInfo[0]) {
                case \FastRoute\Dispatcher::NOT_FOUND:
                    throw new \Sierra\Http\HttpException(404, "Route [{$request->getMethod()} {$request->getUri()}] not found");
                case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                    $allowedMethods = (array)($routeInfo[1] ?? []);
                    throw new \Sierra\Http\HttpException(405, "Method [{$request->getMethod()}] not allowed for [{$request->getUri()}]. Allowed: " . implode(', ', $allowedMethods));
                case \FastRoute\Dispatcher::FOUND:
                    $route = $routeInfo[1];
                    $vars = $routeInfo[2];
                    $request = $request->withAttributes($vars);

                    $stack = new Stack($this->container);
                    $core = function(Request $req) use ($route, $vars) {
                        $handler = $route->handler;
                        if ($handler instanceof \Closure) {
                            $result = $handler(...array_merge([$req], array_values($vars)));
                        } elseif (is_array($handler)) {
                            $controller = $this->container->get($handler[0]);
                            $result = $controller->{$handler[1]}(...array_merge([$req], array_values($vars)));
                        } elseif (is_callable($handler)) {
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
        } catch (\Throwable $e) {
            $response = $this->exceptionHandler->handle($e, $request ?? null);
            $response->send();
        }
    }
}
