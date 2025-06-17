<?php

namespace App\Services;

use App\Enums\PaymentMethod;
use InvalidArgumentException;

class CartService
{
    public function __construct() {}

    public function calculatePayment(
        array $items,
        PaymentMethod $paymentMethod,
        ?array $card = null,
        ?int $installments = null
    ): array {
        $this->validateItems($items);
        $subtotal = $this->calculateSubtotal($items);
        $discount = 0;
        $interest = 0;
        $total = $subtotal;

        if ($paymentMethod === PaymentMethod::CREDIT_CARD) {
            $this->validateCard($card, $installments);
        }

        if ($this->isPaymentInCash($paymentMethod, $installments)) {
            $discount = $subtotal * 0.10;
            $total = $subtotal - $discount;
        } elseif ($installments && $installments > 1) {
            $interestRate = 0.01;
            $total = $subtotal * pow(1 + $interestRate, $installments);
            $interest = $total - $subtotal;
        }

        return [
            'items' => $items,
            'subtotal' => round($subtotal, 2),
            'discount' => round($discount, 2),
            'interest' => round($interest, 2),
            'total' => round($total, 2),
            'paymentMethod' => $paymentMethod->value,
            'installments' => $installments,
            'installmentValue' => $installments && $installments > 1 ? round($total / $installments, 2) : null,
        ];
    }

    private function validateItems(array $items): void
    {
        foreach ($items as $index => $item) {
            if (!is_string($item['name']) || empty($item['name'])) {
                throw new InvalidArgumentException("Name at position {$index} must be a non-empty string");
            }

            $item['unitPrice'] = $this->formatUnitPrice($item['unitPrice']);
            if (!is_numeric($item['unitPrice']) || $item['unitPrice'] <= 0) {
                throw new InvalidArgumentException("Price at position {$index} must be a positive number");
            }

            if (!is_numeric($item['quantity']) || $item['quantity'] <= 0) {
                throw new InvalidArgumentException("Quantity at position {$index} must be greater than or equal to 1");
            }
        }
    }

    private function formatUnitPrice($price): float
    {
        $price = str_replace(',', '.', trim($price));
        return (float) $price;
    }

    private function calculateSubtotal(array $items): float
    {
        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += $this->formatUnitPrice($item['unitPrice']) * $item['quantity'];
        }
        return $subtotal;
    }

    private function validateCard(array $card, ?int $installments): void
    {
        $cardNumber = preg_replace('/\D/', '', $card['number']);
        if (strlen($cardNumber) < 13 || strlen($cardNumber) > 19) {
            throw new InvalidArgumentException("Invalid card number");
        }

        if (!preg_match('/^(0[1-9]|1[0-2])\/([0-9]{2})$/', $card['expiryDate'])) {
            throw new InvalidArgumentException("Expiry date must be in MM/YY format");
        }

        if (!preg_match('/^[0-9]{3,4}$/', $card['cvv'])) {
            throw new InvalidArgumentException("CVV must have 3 or 4 digits");
        }

        if ($installments !== null && $installments > 12) {
            throw new InvalidArgumentException("Number of installments must be between 1 and 12");
        }
    }

    private function isPaymentInCash(PaymentMethod $paymentMethod, ?int $installments): bool
    {
        return $paymentMethod === PaymentMethod::PIX ||
            ($paymentMethod === PaymentMethod::CREDIT_CARD && ($installments === null || $installments === 1));
    }
}
