<?php

namespace Tests\Unit\Services;

use App\Enums\PaymentMethod;
use App\Services\CartService;
use InvalidArgumentException;
use Tests\TestCase;

class CartServiceTest extends TestCase
{
    private CartService $cartService;
    private array $validItems;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cartService = new CartService();
        $this->validItems = [
            [
                'name' => 'Product 1',
                'unitPrice' => '100,00',
                'quantity' => 2
            ],
            [
                'name' => 'Product 2',
                'unitPrice' => '50.00',
                'quantity' => 1
            ]
        ];
    }

    public function testCalculatePaymentWithPix()
    {
        $result = $this->cartService->calculatePayment(
            items: $this->validItems,
            paymentMethod: PaymentMethod::PIX
        );

        $this->assertEquals(250.00, $result['subtotal']);
        $this->assertEquals(25.00, $result['discount']);
        $this->assertEquals(0.00, $result['interest']);
        $this->assertEquals(225.00, $result['total']);
        $this->assertEquals('pix', $result['paymentMethod']);
        $this->assertNull($result['installments']);
        $this->assertNull($result['installmentValue']);
    }

    public function testCalculatePaymentWithCreditCardInCash()
    {
        $result = $this->cartService->calculatePayment(
            items: $this->validItems,
            paymentMethod: PaymentMethod::CREDIT_CARD,
            cardData: [
                'holderName' => 'John Doe',
                'number' => '4111111111111111',
                'expiryDate' => '12/25',
                'cvv' => '123'
            ],
            installments: 1
        );

        $this->assertEquals(250.00, $result['subtotal']);
        $this->assertEquals(25.00, $result['discount']);
        $this->assertEquals(0.00, $result['interest']);
        $this->assertEquals(225.00, $result['total']);
        $this->assertEquals('creditCard', $result['paymentMethod']);
        $this->assertEquals(1, $result['installments']);
        $this->assertNull($result['installmentValue']);
    }

    public function testCalculatePaymentWithCreditCardInstallments()
    {
        $result = $this->cartService->calculatePayment(
            items: $this->validItems,
            paymentMethod: PaymentMethod::CREDIT_CARD,
            cardData: [
                'holderName' => 'John Doe',
                'number' => '4111111111111111',
                'expiryDate' => '12/25',
                'cvv' => '123'
            ],
            installments: 6
        );

        $this->assertEquals(250.00, $result['subtotal']);
        $this->assertEquals(0.00, $result['discount']);
        $this->assertEquals(15.38, $result['interest']);
        $this->assertEquals(265.38, $result['total']);
        $this->assertEquals('creditCard', $result['paymentMethod']);
        $this->assertEquals(6, $result['installments']);
        $this->assertEquals(44.23, $result['installmentValue']);
    }

    public function testValidateItemsWithInvalidPrice()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Price at position 0 must be a positive number');

        $this->cartService->calculatePayment(
            items: [
                [
                    'name' => 'Product 1',
                    'unitPrice' => '-100',
                    'quantity' => 1
                ]
            ],
            paymentMethod: PaymentMethod::PIX
        );
    }

    public function testValidateItemsWithInvalidQuantity()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Quantity at position 0 must be greater than or equal to 1');

        $this->cartService->calculatePayment(
            items: [
                [
                    'name' => 'Product 1',
                    'unitPrice' => '100,00',
                    'quantity' => 0
                ]
            ],
            paymentMethod: PaymentMethod::PIX
        );
    }

    public function testValidateCardWithInvalidNumber()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid card number');

        $this->cartService->calculatePayment(
            items: $this->validItems,
            paymentMethod: PaymentMethod::CREDIT_CARD,
            cardData: [
                'holderName' => 'John Doe',
                'number' => '123',
                'expiryDate' => '12/25',
                'cvv' => '123'
            ],
            installments: 1
        );
    }

    public function testValidateCardWithInvalidExpiryDate()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expiry date must be in MM/YY format');

        $this->cartService->calculatePayment(
            items: $this->validItems,
            paymentMethod: PaymentMethod::CREDIT_CARD,
            cardData: [
                'holderName' => 'John Doe',
                'number' => '4111111111111111',
                'expiryDate' => '13/25',
                'cvv' => '123'
            ],
            installments: 1
        );
    }

    public function testValidateCardWithInvalidCvv()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('CVV must have 3 or 4 digits');

        $this->cartService->calculatePayment(
            items: $this->validItems,
            paymentMethod: PaymentMethod::CREDIT_CARD,
            cardData: [
                'holderName' => 'John Doe',
                'number' => '4111111111111111',
                'expiryDate' => '12/25',
                'cvv' => '12'
            ],
            installments: 1
        );
    }

    public function testValidateInstallmentsLimit()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Number of installments must be between 1 and 12');

        $this->cartService->calculatePayment(
            items: $this->validItems,
            paymentMethod: PaymentMethod::CREDIT_CARD,
            cardData: [
                'holderName' => 'John Doe',
                'number' => '4111111111111111',
                'expiryDate' => '12/25',
                'cvv' => '123'
            ],
            installments: 13
        );
    }

    public function testFormatUnitPriceWithComma()
    {
        $result = $this->cartService->calculatePayment(
            items: [
                [
                    'name' => 'Product 1',
                    'unitPrice' => '100,50',
                    'quantity' => 1
                ]
            ],
            paymentMethod: PaymentMethod::PIX
        );

        $this->assertEquals(100.50, $result['subtotal']);
    }

    public function testFormatUnitPriceWithDot()
    {
        $result = $this->cartService->calculatePayment(
            items: [
                [
                    'name' => 'Product 1',
                    'unitPrice' => '100.50',
                    'quantity' => 1
                ]
            ],
            paymentMethod: PaymentMethod::PIX
        );

        $this->assertEquals(100.50, $result['subtotal']);
    }
}
