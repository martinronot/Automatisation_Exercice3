<?php

namespace Tests\Entity;

use App\Entity\Product;
use PHPUnit\Framework\TestCase;

class ProductTest extends TestCase
{
    /**
     * @dataProvider validProductDataProvider
     */
    public function testConstructorWithValidData(string $name, array $prices, string $type): void
    {
        $product = new Product($name, $prices, $type);
        $this->assertSame($name, $product->getName());
        $this->assertSame($type, $product->getType());
        $this->assertSame($prices, $product->getPrices());
    }

    public function testConstructorWithInvalidType(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid type');
        new Product('Test Product', ['EUR' => 10.0], 'invalid_type');
    }

    /**
     * @dataProvider pricesProvider
     */
    public function testSetPrices(array $inputPrices, array $expectedPrices): void
    {
        $product = new Product('Test Product', [], 'food');
        $product->setPrices($inputPrices);
        $this->assertSame($expectedPrices, $product->getPrices());
    }

    public function testGetTVA(): void
    {
        $foodProduct = new Product('Food Item', ['EUR' => 10.0], 'food');
        $this->assertSame(0.1, $foodProduct->getTVA());

        $techProduct = new Product('Tech Item', ['EUR' => 10.0], 'tech');
        $this->assertSame(0.2, $techProduct->getTVA());
    }

    public function testListCurrencies(): void
    {
        $prices = ['EUR' => 10.0, 'USD' => 12.0];
        $product = new Product('Test Product', $prices, 'food');
        $currencies = $product->listCurrencies();
        sort($currencies); 
        $this->assertSame(['EUR', 'USD'], $currencies);
    }

    /**
     * @dataProvider getPriceProvider
     */
    public function testGetPrice(array $prices, string $currency, float $expectedPrice): void
    {
        $product = new Product('Test Product', $prices, 'food');
        $this->assertSame($expectedPrice, $product->getPrice($currency));
    }

    public function testGetPriceWithInvalidCurrency(): void
    {
        $product = new Product('Test Product', ['EUR' => 10.0], 'food');
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid currency');
        $product->getPrice('GBP');
    }

    public function testGetPriceWithUnavailableCurrency(): void
    {
        $product = new Product('Test Product', ['EUR' => 10.0], 'food');
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Currency not available for this product');
        $product->getPrice('USD');
    }

    public function validProductDataProvider(): array
    {
        return [
            'Food product' => ['Apple', ['EUR' => 1.0], 'food'],
            'Tech product' => ['Phone', ['USD' => 999.99], 'tech'],
            'Alcohol product' => ['Wine', ['EUR' => 15.0, 'USD' => 18.0], 'alcohol'],
            'Other product' => ['Book', ['EUR' => 20.0], 'other']
        ];
    }

    public function pricesProvider(): array
    {
        return [
            'Valid prices' => [
                ['EUR' => 10.0, 'USD' => 12.0],
                ['EUR' => 10.0, 'USD' => 12.0]
            ],
            'Invalid currency filtered' => [
                ['EUR' => 10.0, 'GBP' => 8.0],
                ['EUR' => 10.0]
            ],
            'Negative prices filtered' => [
                ['EUR' => 10.0, 'USD' => -12.0],
                ['EUR' => 10.0]
            ],
            'Mixed valid and invalid' => [
                ['EUR' => 10.0, 'GBP' => 8.0, 'USD' => -12.0],
                ['EUR' => 10.0]
            ]
        ];
    }

    public function getPriceProvider(): array
    {
        return [
            'EUR price' => [['EUR' => 10.0, 'USD' => 12.0], 'EUR', 10.0],
            'USD price' => [['EUR' => 10.0, 'USD' => 12.0], 'USD', 12.0]
        ];
    }
}
