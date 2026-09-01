<?php
declare(strict_types=1);

use Sierra\Http\HttpException;
use Sierra\Http\Response;
use Sierra\Http\Request;
use Sierra\Container\Container;

it('abort throws HttpException with given status code and message', function () {
    expect(fn () => abort(404, 'Post not found'))
        ->toThrow(HttpException::class, 'Post not found');
});

it('abort throws HttpException with default empty message', function () {
    try {
        abort(403);
    } catch (HttpException $e) {
        expect($e->getStatusCode())->toBe(403)
            ->and($e->getMessage())->toBe('');
    }
});

it('response helper returns Response instance', function () {
    $res = response('hello world', 201, ['X-Custom' => 'Value']);

    expect($res)->toBeInstanceOf(Response::class)
        ->and($res->getStatusCode())->toBe(201)
        ->and($res->getBody())->toBe('hello world')
        ->and($res->getHeader('X-Custom'))->toBe('Value');
});

it('request helper returns current Request or input', function () {
    global $sierraRequest;
    $sierraRequest = new Request('GET', '/test', ['name' => 'Sierra'], [], [], [], []);

    expect(request())->toBeInstanceOf(Request::class)
        ->and(request('name'))->toBe('Sierra')
        ->and(request('missing', 'default'))->toBe('default');
});

it('app helper returns Container or resolved binding', function () {
    $container = app();
    expect($container)->toBeInstanceOf(Container::class);

    $container->bind('test_key', fn() => 'test_value');
    expect(app('test_key'))->toBe('test_value');
});

it('env helper reads environment variables with fallback', function () {
    $_ENV['SIERRA_TEST_ENV'] = 'active';

    expect(env('SIERRA_TEST_ENV'))->toBe('active')
        ->and(env('SIERRA_NON_EXISTENT', 'fallback_val'))->toBe('fallback_val');
});
