<?php
declare(strict_types=1);

use Sierra\Container\Container;
use Sierra\Http\Request;
use Sierra\Http\Response;
use Sierra\Middleware\LogMiddleware;
use Sierra\Middleware\MiddlewareInterface;
use Sierra\Middleware\Stack;

class HeaderMiddleware implements MiddlewareInterface
{
    public function process(Request $request, callable $next): Response
    {
        $response = $next($request);
        return $response->header('X-Custom-Middleware', 'Passed');
    }
}

it('runs middleware stack in order and passes through core handler', function () {
    $container = new Container();
    $stack = new Stack($container);

    $request = new Request('GET', '/test', [], [], [], [], []);
    $core = fn(Request $req) => new Response('OK', 200);

    $response = $stack->run([HeaderMiddleware::class], $request, $core);

    expect($response->getStatusCode())->toBe(200)
        ->and($response->getBody())->toBe('OK')
        ->and($response->getHeader('X-Custom-Middleware'))->toBe('Passed');
});

it('LogMiddleware sets X-Sierra-Time header', function () {
    $container = new Container();
    $stack = new Stack($container);

    $request = new Request('GET', '/profile', [], [], [], [], []);
    $core = fn(Request $req) => new Response('Profile Content', 200);

    $response = $stack->run([LogMiddleware::class], $request, $core);

    expect($response->getStatusCode())->toBe(200)
        ->and($response->getBody())->toBe('Profile Content')
        ->and($response->getHeader('X-Sierra-Time'))->not->toBeNull()
        ->and($response->getHeader('X-Sierra-Time'))->toContain('ms');
});
