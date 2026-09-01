<?php
declare(strict_types=1);

use Sierra\Container\Container;

class DummyService {
    public function __construct(public string $name = 'sierra') {}
}

class NeedsDummy {
    public function __construct(public DummyService $dummy) {}
}

it('can bind and get', function() {
    $c = new Container();
    $c->bind('greeting', fn() => 'hello');
    expect($c->get('greeting'))->toBe('hello');
});

it('can singleton', function() {
    $c = new Container();
    $c->singleton(DummyService::class, fn() => new DummyService('single'));
    $a = $c->get(DummyService::class);
    $b = $c->get(DummyService::class);
    expect($a)->toBe($b);
});

it('can auto-wire', function() {
    $c = new Container();
    $obj = $c->get(NeedsDummy::class);
    expect($obj)->toBeInstanceOf(NeedsDummy::class);
    expect($obj->dummy)->toBeInstanceOf(DummyService::class);
});
