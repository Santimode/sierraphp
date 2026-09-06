<?php

use Sierra\Http\FormRequest;
use Sierra\Application;
use Sierra\Http\Request;

class TestStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => 'required|string|min:3',
        ];
    }
}

test('FormRequest auto-validates and returns 422 json on failure', function () {
    $app = new Application(__DIR__);
    
    $app->post('/store', function (TestStoreRequest $request) {
        return ['status' => 'success', 'title' => $request->input('title')];
    });

    $request = new Request('POST', '/store', [], ['title' => 'a'], [], [], []);
    
    ob_start();
    $app->run($request);
    $output = ob_get_clean();
    
    expect($output)->toBeJson();
    $data = json_decode($output, true);
    expect($data)->toHaveKey('errors');
    expect($data['errors'])->toHaveKey('title');
});

test('FormRequest passes validation in router', function () {
    $app = new Application(__DIR__);
    
    $app->post('/store', function (TestStoreRequest $request) {
        return ['status' => 'success', 'title' => $request->input('title')];
    });

    $request = new Request('POST', '/store', [], ['title' => 'Hello World'], [], [], []);
    
    ob_start();
    $app->run($request);
    $output = ob_get_clean();
    
    expect($output)->toBeJson();
    expect(json_decode($output, true)['title'])->toBe('Hello World');
});
