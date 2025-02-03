<?php

namespace Tests\Entity;

use App\Entity\Person;
use App\Entity\Product;
use PHPUnit\Framework\TestCase;

class PersonTest extends TestCase
{
    /**
     * @dataProvider constructorDataProvider
     */
    public function testConstructor(string $name, string $currency): void
    {
        $person = new Person($name, $currency);
        $this->assertSame($name, $person->getName());
        $this->assertSame($currency, $person->getWallet()->getCurrency());
        $this->assertSame(0.0, $person->getWallet()->getBalance());
    }

    public function testHasFund(): void
    {
        $person = new Person('John', 'EUR');
        $this->assertFalse($person->hasFund());

        $person->getWallet()->addFund(100.0);
        $this->assertTrue($person->hasFund());

        $person->getWallet()->removeFund(100.0);
        $this->assertFalse($person->hasFund());
    }

    /**
     * @dataProvider transferFundDataProvider
     */
    public function testTransfertFund(float $amount, float $initialBalance): void
    {
        $person1 = new Person('John', 'EUR');
        $person2 = new Person('Jane', 'EUR');
        
        $person1->getWallet()->addFund($initialBalance);
        $person1->transfertFund($amount, $person2);

        $this->assertSame($initialBalance - $amount, $person1->getWallet()->getBalance());
        $this->assertSame($amount, $person2->getWallet()->getBalance());
    }

    public function testTransfertFundWithDifferentCurrencies(): void
    {
        $person1 = new Person('John', 'EUR');
        $person2 = new Person('Jane', 'USD');
        
        $person1->getWallet()->addFund(100.0);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Can\'t give money with different currencies');
        $person1->transfertFund(50.0, $person2);
    }

    /**
     * @dataProvider divideWalletDataProvider
     */
    public function testDivideWallet(float $initialBalance, int $numRecipients, float $expectedShare): void
    {
        $person1 = new Person('John', 'EUR');
        $person1->getWallet()->addFund($initialBalance);

        $recipients = [];
        for ($i = 0; $i < $numRecipients; $i++) {
            $recipients[] = new Person("Recipient$i", 'EUR');
        }

        $person1->divideWallet($recipients);

        foreach ($recipients as $index => $recipient) {
            if ($index === 0) {
                $remainder = round($initialBalance - ($expectedShare * $numRecipients), 2);
                $this->assertSame($expectedShare + $remainder, $recipient->getWallet()->getBalance());
            } else {
                $this->assertSame($expectedShare, $recipient->getWallet()->getBalance());
            }
        }
        $this->assertSame(0.0, $person1->getWallet()->getBalance());
    }

    public function testDivideWalletWithDifferentCurrencies(): void
    {
        $person1 = new Person('John', 'EUR');
        $person1->getWallet()->addFund(100.0);

        $recipients = [
            new Person('Jane', 'USD'),
            new Person('Bob', 'EUR'),
            new Person('Alice', 'EUR')
        ];

        $person1->divideWallet($recipients);

        $this->assertSame(0.0, $person1->getWallet()->getBalance());
        $this->assertSame(0.0, $recipients[0]->getWallet()->getBalance()); 
        $this->assertSame(50.0, $recipients[1]->getWallet()->getBalance());
        $this->assertSame(50.0, $recipients[2]->getWallet()->getBalance());
    }

    /**
     * @dataProvider buyProductDataProvider
     */
    public function testBuyProduct(string $currency, float $initialBalance, array $prices, float $expectedBalance): void
    {
        $person = new Person('John', $currency);
        $person->getWallet()->addFund($initialBalance);

        $product = new Product('Test Product', $prices, 'tech');
        $person->buyProduct($product);

        $this->assertSame($expectedBalance, $person->getWallet()->getBalance());
    }

    public function testBuyProductWithInvalidCurrency(): void
    {
        $person = new Person('John', 'EUR');
        $person->getWallet()->addFund(100.0);

        $product = new Product('Test Product', ['USD' => 50.0], 'tech');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Can\'t buy product with this wallet currency');
        $person->buyProduct($product);
    }

    public function constructorDataProvider(): array
    {
        return [
            'EUR wallet' => ['John', 'EUR'],
            'USD wallet' => ['Jane', 'USD']
        ];
    }

    public function transferFundDataProvider(): array
    {
        return [
            'Transfer part' => [50.0, 100.0],
            'Transfer all' => [100.0, 100.0],
            'Transfer zero' => [0.0, 100.0]
        ];
    }

    public function divideWalletDataProvider(): array
    {
        return [
            'Two recipients' => [100.0, 2, 50.0],
            'Three recipients' => [100.0, 3, 33.33],
            'Four recipients' => [100.0, 4, 25.0]
        ];
    }

    public function buyProductDataProvider(): array
    {
        return [
            'Buy with EUR' => ['EUR', 100.0, ['EUR' => 50.0], 50.0],
            'Buy with USD' => ['USD', 200.0, ['USD' => 150.0], 50.0],
            'Buy with multiple currencies' => ['EUR', 100.0, ['EUR' => 75.0, 'USD' => 80.0], 25.0]
        ];
    }
}
