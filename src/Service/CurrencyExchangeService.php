<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\CurrencyExchangeRate;
use App\Repository\CurrencyExchangeRateRepository;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CurrencyExchangeService
{
    public const DEFAULT_SUPPORTED_CURRENCY_CODES = ['USD', 'EUR', 'AUD', 'CHF', 'AZN', 'SDG'];

    public function __construct(
        private readonly CurrencyExchangeRateRepository $currencyRateRepository,
        private readonly HttpClientInterface $client,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @param string[] $currencyCodes
     */
    public function updateExchangeRates(array $currencyCodes): void
    {
        $this->logger->info('Updating exchange rates for currency codes: {currencyCodes}', ['currencyCodes' => $currencyCodes]);

        $existingCurrencyCodes = $this->currencyRateRepository->getSupportedCurrencyCodes();
        $ratesToImport = array_unique(array_merge($existingCurrencyCodes, $currencyCodes, self::DEFAULT_SUPPORTED_CURRENCY_CODES));

        $this->logger->debug('Updating exchange rates for {currencyCodes} to {ratesToImport}', ['currencyCodes' => $currencyCodes, 'ratesToImport' => $ratesToImport]);

        foreach ($currencyCodes as $currencyCode) {
            try {
                $exchangeRates = $this->fetchExchangeRate($currencyCode);
                $this->logger->debug('Exchange rates for {currencyCode}: {exchangeRates}', ['currencyCode' => $currencyCode, 'exchangeRates' => $exchangeRates]);

                foreach ($ratesToImport as $rateToImport) {
                    $rateToImport = strtolower($rateToImport);

                    if (!array_key_exists($rateToImport, $exchangeRates)) {
                        $this->logger->error('Unsupported currency code: {currencyCode}', ['currencyCode' => $rateToImport]);
                        continue;
                    }

                    $exchangeRate = $exchangeRates[$rateToImport];
                    $this->importCurrencyExchangeRate($currencyCode, $exchangeRate);
                }
            } catch (\Throwable $e) {
                // Catch all exceptions so that 1 error won't stop the import of other currency codes
                $this->logger->error('Error updating exchange rates for currency code: {currencyCode}', ['currencyCode' => $currencyCode, 'error' => $e->getMessage()]);
            }
        }

        $this->logger->info('Exchange rates updated for currency codes: {currencyCodes}', ['currencyCodes' => $currencyCodes]);
    }

    public function getCurrencyExchangeRatesForCurrencyCode(string $currencyCode): array
    {
        $currencyRates = $this->currencyRateRepository->findByCurrencyCode($currencyCode);

        return array_map(function (CurrencyExchangeRate $currencyExchangeRate) use ($currencyCode) {
            return [
                'code' => $currencyExchangeRate->getToCurrencyCode() === $currencyCode ? $currencyExchangeRate->getFromCurrencyCode() : $currencyExchangeRate->getToCurrencyCode(),
                'rate' => $currencyExchangeRate->getFromCurrencyCode() === $currencyCode ? $currencyExchangeRate->getExchangeRate() : $currencyExchangeRate->getInvertedExchangeRate(),
            ];
        }, $currencyRates);
    }

    /**
     * @param string $currencyCode
     * @return array<string, array{
     *     code: string,
     *     name: string,
     *     rate: float,
     *     inverseRate: float,
     *     date: string,
     * }>
     */
    protected function fetchExchangeRate(string $currencyCode): array
    {
        $response = $this->client->request(
            'GET',
            sprintf('http://www.floatrates.com/daily/%s.json', strtolower($currencyCode)),
        );

        return $response->toArray();
    }

    /**
     * Import currency exchange rate into the database.
     * Function will always ensure that from -> to is in alphabetical order.
     * Eg. from USD to EUR will be stored as EUR -> USD, making it easier to track currency exchange rates and prevent duplicates.
     *
     * @param string $fromCurrencyCode
     * @param array $exchangeRateData
     * @return void
     */
    protected function importCurrencyExchangeRate(string $fromCurrencyCode, array $exchangeRateData): void
    {
        $currencyCodes = [strtolower($fromCurrencyCode), strtolower($exchangeRateData['code'])];
        $sortedCurrencyCodes = $currencyCodes;
        sort($sortedCurrencyCodes, SORT_STRING);

        $exchangeRate = $exchangeRateData['rate'];
        $inverseExchangeRate = $exchangeRateData['inverseRate'];

        // If order of currency code has flipped, we need to flip the rates
        if ($currencyCodes !== $sortedCurrencyCodes) {
            $exchangeRate = $exchangeRateData['inverseRate'];
            $inverseExchangeRate = $exchangeRateData['rate'];
        }

        // Create a unique identifier for the currency exchange rates
        $entityId = md5(implode('', $sortedCurrencyCodes));

        $currencyExchangeRate = new CurrencyExchangeRate();
        $currencyExchangeRate
            ->setId($entityId)
            ->setFromCurrencyCode($sortedCurrencyCodes[0])
            ->setToCurrencyCode($sortedCurrencyCodes[1])
            ->setExchangeRate($exchangeRate)
            ->setInvertedExchangeRate($inverseExchangeRate)
        ;

        $this->logger->info('Importing currency exchange rate with id: {entityId} ({fromCurrencyCode} -> {toCurrencyCode})', [
            'entityId' => $entityId,
            'fromCurrencyCode' => $fromCurrencyCode,
            'toCurrencyCode' => $exchangeRateData['code'],
        ]);
        $this->currencyRateRepository->upsert($currencyExchangeRate);
    }
}
