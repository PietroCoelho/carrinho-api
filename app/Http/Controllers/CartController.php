<?php

namespace App\Http\Controllers;

use App\Enums\HttpCode;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\CartService;
use App\Enums\PaymentMethod;
use Exception;

class CartController extends Controller
{
    private CartService $cartService;

    public function __construct()
    {
        $this->cartService = new CartService();
    }

    public function calculatePayment(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'paymentMethod' => 'required|string|in:pix,creditCard',
                'items' => 'required|array',
                'items.*.name' => 'required|string',
                'items.*.unitPrice' => 'required|string',
                'items.*.quantity' => 'required|numeric',
                'card' => 'required_if:paymentMethod,creditCard|array',
                'card.holderName' => 'required_if:paymentMethod,creditCard|string',
                'card.number' => 'required_if:paymentMethod,creditCard|string',
                'card.expiryDate' => 'required_if:paymentMethod,creditCard|string',
                'card.cvv' => 'required_if:paymentMethod,creditCard|string',
                'installments' => 'integer|min:2|max:12'
            ]);
            $paymentMethod = PaymentMethod::tryFrom($request->paymentMethod);
            $card = $request->card ?? null;
            $installments = $request->installments ?? null;
            $items = $request->items;
            $result = $this->cartService->calculatePayment($items, $paymentMethod, $card, $installments);
            return response()->json([
                'status' => true,
                'data' => $result
            ], HttpCode::SUCCESS_REQUEST->value);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error calculating payment: ' . $e->getMessage()
            ], HttpCode::BAD_REQUEST->value);
        }
    }
}
