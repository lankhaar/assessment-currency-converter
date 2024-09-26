<?php

namespace App\Tests\Form\Authentication;

use PHPUnit\Framework\Attributes\Test;
use App\Tests\Application\ApplicationTestCase;

class CurrencyConverterTest extends ApplicationTestCase
{
    #[Test]
    public function homeRedirectsToLoginWhenNotLoggedIn(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        $this->assertResponseRedirects('/auth/login');
    }

    #[Test]
    public function homeShowsCurrencyConverter(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getRegularUser());
        $client->request('GET', '/');

        $this->assertSelectorExists('select[name="from_currency_code"]');
        $this->assertSelectorExists('input[name="amount"]');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h3', 'Convert from');
    }

    #[Test]
    public function homeShowsConvertedCurrencies(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getRegularUser());
        $crawler = $client->request('GET', '/');

        $this->assertSelectorExists('table');
        $this->assertCount(2, $crawler->filter('tbody tr')); # Assert table has 2 rows
        $this->assertSelectorTextContains('tbody tr:first-child th', 'Currency');
        $this->assertSelectorTextContains('tbody tr:first-child td', 'GBP');
        $this->assertSelectorTextContains('tbody tr:last-child th', 'Amount');
        $this->assertSelectorTextContains('tbody tr:last-child td', '£0.88');

        $this->assertResponseIsSuccessful();
    }
}
