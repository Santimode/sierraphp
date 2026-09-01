<?php
declare(strict_types=1);

use Sierra\Router\Router;

it('can register and dispatch GET route', function() {
    $router = new Router();
    $router->get('/hello', fn() => 'world');

    $info = $router->dispatch('GET', '/hello');
    expect($info[0])->toBe(\FastRoute\Dispatcher::FOUND);
});

it('can dispatch route with param', function() {
    $router = new Router();
    $router->get('/users/{id}', fn($req, $id) => $id);

    $info = $router->dispatch('GET', '/users/123');
    expect($info[0])->toBe(\FastRoute\Dispatcher::FOUND);
    expect($info[2]['id'])->toBe('123');
});

it('returns NOT_FOUND for missing route', function() {
    $router = new Router();
    $info = $router->dispatch('GET', '/missing');
    expect($info[0])->toBe(\FastRoute\Dispatcher::NOT_FOUND);
});

it('can group routes', function() {
    $router = new Router();
    $router->group('/api', function($r) {
        $r->get('/health', fn() => 'ok');
    });

    $info = $router->dispatch('GET', '/api/health');
    expect($info[0])->toBe(\FastRoute\Dispatcher::FOUND);
});
