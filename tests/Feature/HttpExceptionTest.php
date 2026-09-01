<?php
declare(strict_types=1);

use Sierra\Http\HttpException;

it('instantiates with custom status code and message', function () {
    $e = new HttpException(404, 'Resource not found');

    expect($e->getStatusCode())->toBe(404)
        ->and($e->getMessage())->toBe('Resource not found')
        ->and($e->getCode())->toBe(404);
});

it('defaults to status code 500 and empty message', function () {
    $e = new HttpException();

    expect($e->getStatusCode())->toBe(500)
        ->and($e->getMessage())->toBe('')
        ->and($e->getCode())->toBe(500);
});

it('preserves previous exception chaining', function () {
    $prev = new Exception('Root cause');
    $e = new HttpException(503, 'Service unavailable', $prev);

    expect($e->getPrevious())->toBe($prev)
        ->and($e->getStatusCode())->toBe(503);
});
