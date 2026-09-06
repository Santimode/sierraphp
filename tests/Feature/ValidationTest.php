<?php

use Sierra\Validation\Validator;
use Sierra\Validation\ValidationException;

test('validator validates required fields', function () {
    $validator = Validator::make(['name' => ''], ['name' => 'required']);
    expect($validator->fails())->toBeTrue();
    expect($validator->errors())->toHaveKey('name');
});

test('validator validates email', function () {
    $validator = Validator::make(['email' => 'invalid'], ['email' => 'email']);
    expect($validator->fails())->toBeTrue();
    
    $validator2 = Validator::make(['email' => 'test@example.com'], ['email' => 'email']);
    expect($validator2->fails())->toBeFalse();
});

test('validator validates string and int', function () {
    $validator = Validator::make(['age' => 'foo'], ['age' => 'int']);
    expect($validator->fails())->toBeTrue();
    
    $validator2 = Validator::make(['name' => 123], ['name' => 'string']);
    expect($validator2->fails())->toBeTrue();
});

test('validator validates min and max', function () {
    $validator = Validator::make(['pass' => '123'], ['pass' => 'min:5']);
    expect($validator->fails())->toBeTrue();
    
    $validator2 = Validator::make(['pass' => '1234567'], ['pass' => 'max:5']);
    expect($validator2->fails())->toBeTrue();
    
    $validator3 = Validator::make(['pass' => '12345'], ['pass' => 'min:3|max:8']);
    expect($validator3->fails())->toBeFalse();
});

test('validator throws ValidationException', function () {
    $validator = Validator::make(['name' => ''], ['name' => 'required']);
    $validator->validate();
})->throws(ValidationException::class);
