<?php
declare(strict_types=1);

use Sierra\Http\Response;
use Sierra\Http\HttpException;
use Sierra\Container\Container;

if (!function_exists('abort')) {
    function abort(int $status, string $message = ''): never
    {
        throw new HttpException($status, $message);
    }
}

if (!function_exists('app')) {
    function app(?string $id = null): mixed
    {
        global $sierraApp, $sierraContainer;
        $container = $sierraApp?->getContainer() ?? ($sierraContainer ??= new Container());
        return $id ? $container->get($id) : $container;
    }
}

if (!function_exists('response')) {
    function response(mixed $content = '', int $status = 200, array $headers = []): Response
    {
        return new Response($content, $status, $headers);
    }
}

if (!function_exists('request')) {
    function request(?string $key = null, mixed $default = null): mixed
    {
        global $sierraRequest;
        $req = $sierraRequest ?? \Sierra\Http\Request::fromGlobals();
        if ($key === null) return $req;
        return $req->input($key, $default);
    }
}

if (!function_exists('view')) {
    function view(string $name, array $data = [], int $status = 200): Response
    {
        global $sierraApp;
        $base = $sierraApp?->getBasePath() ?? dirname(__DIR__, 2);
        $path = $base . "/resources/views/" . str_replace('.', '/', $name) . ".php";
        if (!file_exists($path)) {
            return new Response("View [{$name}] not found at {$path}", 500);
        }
        extract($data);
        ob_start();
        include $path;
        $content = ob_get_clean();
        return new Response($content, $status, ['Content-Type' => 'text/html']);
    }
}

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        return $_ENV[$key] ?? $_SERVER[$key] ?? $default;
    }
}

if (!function_exists('config')) {
    function config(string $key, mixed $default = null): mixed
    {
        global $sierraApp;
        $config = $sierraApp?->getConfig($key);
        return $config ?? $default;
    }
}

if (!function_exists('logger')) {
    function logger(?string $message = null, array $context = [], string $level = 'info'): mixed
    {
        global $sierraApp, $sierraLogger;
        /** @var \Sierra\Log\LoggerInterface $logger */
        $logger = $sierraApp?->getContainer()->has(\Sierra\Log\LoggerInterface::class)
            ? $sierraApp->getContainer()->get(\Sierra\Log\LoggerInterface::class)
            : ($sierraLogger ??= new \Sierra\Log\Logger());

        if ($message === null) {
            return $logger;
        }

        $logger->log($level, $message, $context);
        return null;
    }
}
