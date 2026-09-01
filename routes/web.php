<?php
declare(strict_types=1);

use Sierra\Support\Facades\Route;
use Sierra\Http\Request;

Route::get('/', function() {
    return view('welcome', ['name' => 'Santi']);
});

Route::get('/api/health', function() {
    return response()->json([
        'status' => 'ok',
        'framework' => 'sierraPHP',
        'version' => '2.4.0',
        'time' => date('c')
    ]);
});

Route::group('/api', function($router) {
    $router->get('/hello/{name}', function(Request $req, string $name) {
        return response()->json(['hello' => $name, 'query' => $req->query()]);
    });

    $router->put('/items/{id}', function(Request $req, string $id) {
        return response()->json(['updated' => $id, 'data' => $req->all()]);
    });

    $router->delete('/items/{id}', function(Request $req, string $id) {
        if ($id === '0') {
            abort(404, 'Item not found');
        }
        return response()->json(['deleted' => $id]);
    });
});
