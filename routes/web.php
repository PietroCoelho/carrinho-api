<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/api-info', function () {
    return response()->json([
        'message' => 'Shopping Cart API',
        'version' => '1.0.0',
        'endpoints' => [
            'POST /api/cart' => 'Create cart',
            'GET /api/cart/{id}' => 'Get cart',
            'POST /api/cart/{id}/items' => 'Add item',
            'DELETE /api/cart/{id}/items/{itemId}' => 'Remove item',
            'PUT /api/cart/{id}/items/{itemId}' => 'Update item',
            'POST /api/cart/{id}/calculate-payment' => 'Calculate payment',
            'POST /api/cart/{id}/calculate-all-payments' => 'Calculate all methods',
            'DELETE /api/cart/{id}' => 'Clear cart'
        ],
        'documentation' => 'See README_API.md for more details'
    ]);
});
