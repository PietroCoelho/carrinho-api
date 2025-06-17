<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CartController;

Route::prefix('cart')->group(function () {
    Route::post('calculate-payment', [CartController::class, 'calculatePayment']);
});
