<?php

namespace Tests\Entity;

use App\Entity\Wallet;
use PHPUnit\Framework\TestCase;

class WalletTest extends TestCase
{
    /**
     * @dataProvider validCurrencyProvider
     */
    public function testConstructorWithValidCurrency(string $currency): void
    {
        $wallet = new Wallet($currency);
        $this->assertSame($currency, $wallet->getCurrency());
        $this->assertSame(0.0, $wallet->getBalance());
    }

    public function testConstructorWithInvalidCurrency(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid currency');
        new Wallet('GBP');
    }

    /**
     * @dataProvider validBalanceProvider
     */
    public function testSetBalanceWithValidAmount(float $balance): void
    {
        $wallet = new Wallet('EUR');
        $wallet->setBalance($balance);
        $this->assertSame($balance, $wallet->getBalance());
    }

    public function testSetBalanceWithInvalidAmount(): void
    {
        $wallet = new Wallet('EUR');
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid balance');
        $wallet->setBalance(-10.0);
    }

    /**
     * @dataProvider validAmountProvider
     */
    public function testAddFundWithValidAmount(float $amount, float $initialBalance, float $expectedBalance): void
    {
        $wallet = new Wallet('EUR');
        $wallet->setBalance($initialBalance);
        $wallet->addFund($amount);
        $this->assertSame($expectedBalance, $wallet->getBalance());
    }

    public function testAddFundWithInvalidAmount(): void
    {
        $wallet = new Wallet('EUR');
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid amount');
        $wallet->addFund(-10.0);
    }

    /**
     * @dataProvider validRemoveFundProvider
     */
    public function testRemoveFundWithValidAmount(float $amount, float $initialBalance, float $expectedBalance): void
    {
        $wallet = new Wallet('EUR');
        $wallet->setBalance($initialBalance);
        $wallet->removeFund($amount);
        $this->assertSame($expectedBalance, $wallet->getBalance());
    }

    public function testRemoveFundWithInvalidAmount(): void
    {
        $wallet = new Wallet('EUR');
        $wallet->setBalance(100.0);
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid amount');
        $wallet->removeFund(-10.0);
    }

    public function testRemoveFundWithInsufficientFunds(): void
    {
        $wallet = new Wallet('EUR');
        $wallet->setBalance(50.0);
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Insufficient funds');
        $wallet->removeFund(100.0);
    }

    public function validCurrencyProvider(): array
    {
        return [
            'EUR currency' => ['EUR'],
            'USD currency' => ['USD']
        ];
    }

    public function validBalanceProvider(): array
    {
        return [
            'Zero balance' => [0.0],
            'Positive balance' => [100.0],
            'Large balance' => [999999.99]
        ];
    }

    public function validAmountProvider(): array
    {
        return [
            'Add zero' => [0.0, 100.0, 100.0],
            'Add positive amount' => [50.0, 100.0, 150.0],
            'Add to zero balance' => [100.0, 0.0, 100.0]
        ];
    }

    public function validRemoveFundProvider(): array
    {
        return [
            'Remove zero' => [0.0, 100.0, 100.0],
            'Remove part' => [50.0, 100.0, 50.0],
            'Remove all' => [100.0, 100.0, 0.0]
        ];
    }
}
