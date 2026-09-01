<?php
declare(strict_types=1);

use Sierra\Exceptions\Handler;
use Sierra\Http\HttpException;
use Sierra\Http\Request;
use Sierra\Http\Response;

it('renders HTML with 500 status in debug mode for generic exceptions when client expects HTML', function () {
    $handler = new Handler(debug: true);
    $exception = new RuntimeException('Database connection failed');
    $request = new Request('GET', '/page', [], [], ['Accept' => 'text/html'], [], []);

    $response = $handler->handle($exception, $request);

    expect($response)->toBeInstanceOf(Response::class)
        ->and($response->getStatusCode())->toBe(500)
        ->and($response->getHeader('Content-Type'))->toBe('text/html')
        ->and($response->getBody())->toContain('Database connection failed');
});

it('renders detailed JSON in debug mode when client expects JSON', function () {
    $handler = new Handler(debug: true);
    $exception = new RuntimeException('Database query syntax error');
    $request = new Request('GET', '/api/users', [], [], ['Accept' => 'application/json'], [], []);

    $response = $handler->handle($exception, $request);
    $data = json_decode((string)$response->getBody(), true);

    expect($response)->toBeInstanceOf(Response::class)
        ->and($response->getStatusCode())->toBe(500)
        ->and($response->getHeader('Content-Type'))->toBe('application/json')
        ->and($data['error']['message'])->toBe('Database query syntax error')
        ->and($data['error']['exception'])->toBe(RuntimeException::class)
        ->and($data['error']['file'])->not->toBeEmpty()
        ->and($data['framework'])->toBe('sierraPHP');
});

it('renders HTML and preserves status code in debug mode for HttpException', function () {
    $handler = new Handler(debug: true);
    $exception = new HttpException(404, 'Page not found');
    $request = new Request('GET', '/missing', [], [], ['Accept' => 'text/html'], [], []);

    $response = $handler->handle($exception, $request);

    expect($response)->toBeInstanceOf(Response::class)
        ->and($response->getStatusCode())->toBe(404)
        ->and($response->getHeader('Content-Type'))->toBe('text/html')
        ->and($response->getBody())->toContain('Page not found');
});

it('renders generic JSON and masks error details in production mode when client expects JSON', function () {
    $handler = new Handler(debug: false);
    $exception = new RuntimeException('Secret DB password failed');
    $request = new Request('POST', '/api/data', [], [], ['Accept' => 'application/json'], [], []);

    $response = $handler->handle($exception, $request);

    expect($response)->toBeInstanceOf(Response::class)
        ->and($response->getStatusCode())->toBe(500)
        ->and($response->getHeader('Content-Type'))->toBe('application/json')
        ->and($response->getBody())->not->toContain('Secret DB password')
        ->and(json_decode($response->getBody(), true))->toBe([
            'message' => 'Server Error',
            'framework' => 'sierraPHP',
        ]);
});

it('renders clean HTML page without leaking details in production mode when client expects HTML', function () {
    $handler = new Handler(debug: false);
    $exception = new RuntimeException('Internal secret API key failure');
    $request = new Request('GET', '/profile', [], [], ['Accept' => 'text/html'], [], []);

    $response = $handler->handle($exception, $request);

    expect($response)->toBeInstanceOf(Response::class)
        ->and($response->getStatusCode())->toBe(500)
        ->and($response->getHeader('Content-Type'))->toBe('text/html')
        ->and($response->getBody())->not->toContain('Internal secret API key failure')
        ->and($response->getBody())->toContain('500')
        ->and($response->getBody())->toContain('Server Error');
});

it('preserves HttpException status code and message in production mode', function () {
    $handler = new Handler(debug: false);
    $exception = new HttpException(403, 'Forbidden access');
    $request = new Request('GET', '/api/admin', [], [], ['Accept' => 'application/json'], [], []);

    $response = $handler->handle($exception, $request);

    expect($response)->toBeInstanceOf(Response::class)
        ->and($response->getStatusCode())->toBe(403)
        ->and($response->getHeader('Content-Type'))->toBe('application/json')
        ->and(json_decode($response->getBody(), true))->toBe([
            'message' => 'Forbidden access',
            'framework' => 'sierraPHP',
        ]);
});

it('reports exceptions via report method without throwing', function () {
    $handler = new Handler(debug: true);
    $exception = new Exception('Logged error');

    // Should execute report without throwing
    $handler->report($exception);

    expect(true)->toBeTrue();
});
