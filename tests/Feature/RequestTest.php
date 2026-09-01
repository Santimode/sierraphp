<?php
declare(strict_types=1);

use Sierra\Http\Request;

it('creates from globals and reads query', function() {
    $req = new Request('GET', '/test', ['foo' => 'bar'], [], [], [], []);
    expect($req->query('foo'))->toBe('bar');
    expect($req->query())->toBe(['foo' => 'bar']);
});

it('withAttribute is immutable', function() {
    $req = new Request('GET', '/', [], [], [], [], []);
    $req2 = $req->withAttribute('name', 'Santi');
    
    expect($req->getAttribute('name'))->toBeNull();
    expect($req2->getAttribute('name'))->toBe('Santi');
});

it('withAttributes merges', function() {
    $req = new Request('GET', '/', [], [], [], [], []);
    $req2 = $req->withAttributes(['id' => '123', 'name' => 'Santi']);
    expect($req2->getAttribute('id'))->toBe('123');
    expect($req2->getAttribute('name'))->toBe('Santi');
});
