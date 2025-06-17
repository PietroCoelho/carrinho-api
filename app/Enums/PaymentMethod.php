<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case PIX = 'pix';
    case CREDIT_CARD = 'creditCard';

    public function getDescription(): string
    {
        return match ($this) {
            self::PIX => 'PIX',
            self::CREDIT_CARD => 'Credit Card',
        };
    }
}
