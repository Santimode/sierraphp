<?php

use Sierra\Database\Connection;

beforeEach(function () {
    $this->db = new Connection([
        'driver' => 'sqlite',
        'database' => ':memory:',
    ]);
    
    $this->db->statement("
        CREATE TABLE users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL
        )
    ");
});

test('it can insert and retrieve records using query builder', function () {
    $inserted = $this->db->table('users')->insert([
        'name' => 'Alice',
        'email' => 'alice@example.com',
    ]);
    expect($inserted)->toBeTrue();
    
    $users = $this->db->table('users')->get();
    expect($users)->toHaveCount(1);
    expect($users[0]->name)->toBe('Alice');
});

test('it can retrieve single record using first', function () {
    $this->db->table('users')->insert([
        'name' => 'Bob',
        'email' => 'bob@example.com',
    ]);
    
    $user = $this->db->table('users')->where('email', 'bob@example.com')->first();
    expect($user)->not->toBeNull();
    expect($user->name)->toBe('Bob');
});

test('it can update records', function () {
    $this->db->table('users')->insert([
        'name' => 'Charlie',
        'email' => 'charlie@example.com',
    ]);
    
    $affected = $this->db->table('users')->where('email', 'charlie@example.com')->update(['name' => 'Charles']);
    expect($affected)->toBe(1);
    
    $user = $this->db->table('users')->where('email', 'charlie@example.com')->first();
    expect($user->name)->toBe('Charles');
});

test('it can delete records', function () {
    $this->db->table('users')->insert([
        'name' => 'Dave',
        'email' => 'dave@example.com',
    ]);
    
    $affected = $this->db->table('users')->where('name', 'Dave')->delete();
    expect($affected)->toBe(1);
    
    $user = $this->db->table('users')->where('name', 'Dave')->first();
    expect($user)->toBeNull();
});
