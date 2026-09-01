<?php
declare(strict_types=1);

use Sierra\Http\Request;

it('creates from globals and reads query and body', function () {
    $req = new Request('GET', '/test', ['foo' => 'bar'], ['title' => 'Framework'], [], [], []);
    expect($req->query('foo'))->toBe('bar')
        ->and($req->query())->toBe(['foo' => 'bar'])
        ->and($req->input('title'))->toBe('Framework')
        ->and($req->all())->toBe(['foo' => 'bar', 'title' => 'Framework']);
});

it('withAttribute is immutable', function () {
    $req = new Request('GET', '/', [], [], [], [], []);
    $req2 = $req->withAttribute('name', 'Santi');

    expect($req->getAttribute('name'))->toBeNull()
        ->and($req2->getAttribute('name'))->toBe('Santi');
});

it('withAttributes merges attributes immutably', function () {
    $req = new Request('GET', '/', [], [], [], [], []);
    $req2 = $req->withAttributes(['id' => '123', 'name' => 'Santi']);

    expect($req2->getAttribute('id'))->toBe('123')
        ->and($req2->getAttribute('name'))->toBe('Santi')
        ->and($req->getAttribute('id'))->toBeNull();
});

it('inspects headers with case insensitivity', function () {
    $req = new Request('GET', '/', [], [], ['X-API-KEY' => 'secret123'], [], ['HTTP_USER_AGENT' => 'SierraBot']);

    expect($req->header('x-api-key'))->toBe('secret123')
        ->and($req->header('X-Api-Key'))->toBe('secret123')
        ->and($req->header('User-Agent'))->toBe('SierraBot')
        ->and($req->header('Non-Existent', 'fallback'))->toBe('fallback');
});

it('inspects HTTP method with isMethod helper', function () {
    $req = new Request('POST', '/submit', [], [], [], [], []);

    expect($req->isMethod('POST'))->toBeTrue()
        ->and($req->isMethod('post'))->toBeTrue()
        ->and($req->isMethod('GET'))->toBeFalse();
});

it('detects json request content type and accept headers', function () {
    $jsonReq = new Request('POST', '/api', [], [], ['Content-Type' => 'application/json', 'Accept' => 'application/json'], [], []);
    $htmlReq = new Request('GET', '/page', [], [], ['Content-Type' => 'text/html', 'Accept' => 'text/html'], [], []);

    expect($jsonReq->isJson())->toBeTrue()
        ->and($jsonReq->expectsJson())->toBeTrue()
        ->and($htmlReq->isJson())->toBeFalse()
        ->and($htmlReq->expectsJson())->toBeFalse();
});

it('supports HTTP method spoofing via _method in POST body', function () {
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SERVER['REQUEST_URI'] = '/posts/1';
    $_POST = ['_method' => 'DELETE', 'reason' => 'spam'];

    $req = Request::fromGlobals();

    expect($req->getMethod())->toBe('DELETE')
        ->and($req->input('reason'))->toBe('spam');
});

it('supports HTTP method spoofing via X-HTTP-Method-Override header', function () {
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SERVER['REQUEST_URI'] = '/posts/1';
    $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] = 'PUT';
    $_POST = ['title' => 'Updated'];

    $req = Request::fromGlobals();

    expect($req->getMethod())->toBe('PUT')
        ->and($req->input('title'))->toBe('Updated');
});
