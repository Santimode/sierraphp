<?php
declare(strict_types=1);

use Sierra\Container\Container;
use Sierra\Http\Request;
use Sierra\Http\Response;
use Sierra\Middleware\CorsMiddleware;
use Sierra\Middleware\LogMiddleware;
use Sierra\Middleware\MiddlewareInterface;
use Sierra\Middleware\SecurityHeadersMiddleware;
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

it('CorsMiddleware handles preflight OPTIONS request with 204 status', function () {
    $container = new Container();
    $stack = new Stack($container);

    $request = new Request('OPTIONS', '/api/data', [], [], ['Origin' => 'https://example.com'], [], []);
    $core = fn(Request $req) => new Response('Should not be called', 200);

    $response = $stack->run([CorsMiddleware::class], $request, $core);

    expect($response->getStatusCode())->toBe(204)
        ->and($response->getHeader('Access-Control-Allow-Origin'))->toBe('*')
        ->and($response->getHeader('Access-Control-Allow-Methods'))->toContain('POST')
        ->and($response->getHeader('Access-Control-Allow-Headers'))->toContain('Content-Type');
});

it('CorsMiddleware appends CORS headers to standard responses', function () {
    $cors = new CorsMiddleware(allowedOrigins: ['https://app.example.com'], allowCredentials: true);
    $request = new Request('GET', '/api/users', [], [], ['Origin' => 'https://app.example.com'], [], []);
    $core = fn(Request $req) => (new Response())->json(['users' => []]);

    $response = $cors->process($request, $core);

    expect($response->getStatusCode())->toBe(200)
        ->and($response->getHeader('Access-Control-Allow-Origin'))->toBe('https://app.example.com')
        ->and($response->getHeader('Access-Control-Allow-Credentials'))->toBe('true');
});

it('SecurityHeadersMiddleware injects standard security headers', function () {
    $container = new Container();
    $stack = new Stack($container);

    $request = new Request('GET', '/', [], [], [], [], []);
    $core = fn(Request $req) => new Response('<h1>Home</h1>', 200);

    $response = $stack->run([SecurityHeadersMiddleware::class], $request, $core);

    expect($response->getStatusCode())->toBe(200)
        ->and($response->getHeader('X-Content-Type-Options'))->toBe('nosniff')
        ->and($response->getHeader('X-Frame-Options'))->toBe('SAMEORIGIN')
        ->and($response->getHeader('X-XSS-Protection'))->toBe('1; mode=block')
        ->and($response->getHeader('Referrer-Policy'))->toBe('strict-origin-when-cross-origin')
        ->and($response->getHeader('Permissions-Policy'))->toContain('camera=()');
});
