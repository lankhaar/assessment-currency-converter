<?php

namespace App\DataFixtures;

use App\Entity\CurrencyExchangeRate;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class CurrencyExchangeRateFixtures extends Fixture
{
    public const CURRENCY_EXCHANGES = [
        'USD' => [
            'EUR' => 0.85,
            'GBP' => 0.75,
        ],
        'EUR' => [
            'USD' => 1.18,
            'GBP' => 0.88,
        ],
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::CURRENCY_EXCHANGES as $fromCurrencyCode => $exchangeRates) {
            foreach ($exchangeRates as $toCurrencyCode => $rate) {
                $currencyExchangeRate = new CurrencyExchangeRate();
                $currencyExchangeRate->setId(md5(strtolower($fromCurrencyCode . $toCurrencyCode)));
                $currencyExchangeRate->setFromCurrencyCode($fromCurrencyCode);
                $currencyExchangeRate->setToCurrencyCode($toCurrencyCode);
                $currencyExchangeRate->setExchangeRate($rate);
                $currencyExchangeRate->setInvertedExchangeRate(1 / $rate);
                $currencyExchangeRate->setUpdatedAt(new \DateTimeImmutable());
                $currencyExchangeRate->setCreatedAt(new \DateTimeImmutable());

                $manager->persist($currencyExchangeRate);
            }
        }

        $manager->flush();
    }
}
