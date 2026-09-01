<?php
declare(strict_types=1);

use Sierra\Exceptions\Handler;
use Sierra\Http\HttpException;
use Sierra\Http\Response;

it('renders HTML with 500 status in debug mode for generic exceptions', function () {
    $handler = new Handler(debug: true);
    $exception = new RuntimeException('Database connection failed');

    $response = $handler->handle($exception);

    expect($response)->toBeInstanceOf(Response::class)
        ->and($response->getStatusCode())->toBe(500)
        ->and($response->getHeader('Content-Type'))->toBe('text/html')
        ->and($response->getBody())->toContain('Database connection failed');
});

it('renders HTML and preserves status code in debug mode for HttpException', function () {
    $handler = new Handler(debug: true);
    $exception = new HttpException(404, 'Page not found');

    $response = $handler->handle($exception);

    expect($response)->toBeInstanceOf(Response::class)
        ->and($response->getStatusCode())->toBe(404)
        ->and($response->getHeader('Content-Type'))->toBe('text/html')
        ->and($response->getBody())->toContain('Page not found');
});

it('renders generic JSON and masks error details in production mode', function () {
    $handler = new Handler(debug: false);
    $exception = new RuntimeException('Secret DB password failed');

    $response = $handler->handle($exception);

    expect($response)->toBeInstanceOf(Response::class)
        ->and($response->getStatusCode())->toBe(500)
        ->and($response->getHeader('Content-Type'))->toBe('application/json')
        ->and($response->getBody())->not->toContain('Secret DB password')
        ->and(json_decode($response->getBody(), true))->toBe([
            'message' => 'Server Error',
            'framework' => 'sierraPHP',
        ]);
});

it('preserves HttpException status code in production mode', function () {
    $handler = new Handler(debug: false);
    $exception = new HttpException(403, 'Forbidden access');

    $response = $handler->handle($exception);

    expect($response)->toBeInstanceOf(Response::class)
        ->and($response->getStatusCode())->toBe(403)
        ->and($response->getHeader('Content-Type'))->toBe('application/json')
        ->and(json_decode($response->getBody(), true))->toBe([
            'message' => 'Server Error',
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
