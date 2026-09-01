<?php
declare(strict_types=1);

use FastRoute\Dispatcher;
use Sierra\Router\Route;
use Sierra\Router\Router;

it('can register and dispatch GET route', function () {
    $router = new Router();
    $router->get('/hello', fn () => 'world');

    $info = $router->dispatch('GET', '/hello');
    expect($info[0])->toBe(Dispatcher::FOUND);
    expect(($info[1]->handler)())->toBe('world');
});

it('can register and dispatch POST, PUT, PATCH, DELETE, OPTIONS, HEAD routes', function () {
    $router = new Router();
    $router->post('/posts', fn () => 'created');
    $router->put('/posts/{id}', fn ($req, $id) => 'updated ' . $id);
    $router->patch('/posts/{id}', fn ($req, $id) => 'patched ' . $id);
    $router->delete('/posts/{id}', fn ($req, $id) => 'deleted ' . $id);
    $router->options('/posts', fn () => 'options');
    $router->head('/posts', fn () => 'head');

    expect($router->dispatch('POST', '/posts')[0])->toBe(Dispatcher::FOUND)
        ->and($router->dispatch('PUT', '/posts/5')[0])->toBe(Dispatcher::FOUND)
        ->and($router->dispatch('PUT', '/posts/5')[2]['id'])->toBe('5')
        ->and($router->dispatch('PATCH', '/posts/5')[0])->toBe(Dispatcher::FOUND)
        ->and($router->dispatch('DELETE', '/posts/5')[0])->toBe(Dispatcher::FOUND)
        ->and($router->dispatch('OPTIONS', '/posts')[0])->toBe(Dispatcher::FOUND)
        ->and($router->dispatch('HEAD', '/posts')[0])->toBe(Dispatcher::FOUND);
});

it('can dispatch route with param', function () {
    $router = new Router();
    $router->get('/users/{id}', fn ($req, $id) => $id);

    $info = $router->dispatch('GET', '/users/123');
    expect($info[0])->toBe(Dispatcher::FOUND)
        ->and($info[2]['id'])->toBe('123');
});

it('returns NOT_FOUND for missing route', function () {
    $router = new Router();
    $info = $router->dispatch('GET', '/missing');
    expect($info[0])->toBe(Dispatcher::NOT_FOUND);
});

it('returns METHOD_NOT_ALLOWED for wrong HTTP verb', function () {
    $router = new Router();
    $router->post('/submit', fn () => 'done');

    $info = $router->dispatch('GET', '/submit');
    expect($info[0])->toBe(Dispatcher::METHOD_NOT_ALLOWED)
        ->and($info[1])->toContain('POST');
});

it('can match multiple specific HTTP verbs', function () {
    $router = new Router();
    $routes = $router->match(['GET', 'POST'], '/form', fn () => 'form');

    expect($routes)->toHaveCount(2)
        ->and($router->dispatch('GET', '/form')[0])->toBe(Dispatcher::FOUND)
        ->and($router->dispatch('POST', '/form')[0])->toBe(Dispatcher::FOUND)
        ->and($router->dispatch('DELETE', '/form')[0])->toBe(Dispatcher::METHOD_NOT_ALLOWED);
});

it('can register route for any HTTP verb', function () {
    $router = new Router();
    $routes = $router->any('/webhook', fn () => 'hooked');

    expect($routes)->toHaveCount(7);

    foreach (['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS', 'HEAD'] as $method) {
        expect($router->dispatch($method, '/webhook')[0])->toBe(Dispatcher::FOUND);
    }
});

it('can group routes with prefixes and nested groups', function () {
    $router = new Router();
    $router->group('/api', function (Router $r) {
        $r->get('/health', fn () => 'ok');

        $r->group('/v1', function (Router $r2) {
            $r2->get('/users', fn () => 'users list');
        });
    });

    expect($router->dispatch('GET', '/api/health')[0])->toBe(Dispatcher::FOUND)
        ->and($router->dispatch('GET', '/api/v1/users')[0])->toBe(Dispatcher::FOUND);
});

it('attaches group middleware to routes inside group', function () {
    $router = new Router();
    $router->group(['prefix' => '/admin', 'middleware' => ['AuthMiddleware']], function (Router $r) {
        $r->get('/dashboard', fn () => 'admin');
        $r->post('/settings', fn () => 'settings')->middleware('CsrfMiddleware');
    });

    $routes = $router->getRoutes();
    expect($routes[0]->middleware)->toBe(['AuthMiddleware'])
        ->and($routes[1]->middleware)->toBe(['AuthMiddleware', 'CsrfMiddleware']);
});
