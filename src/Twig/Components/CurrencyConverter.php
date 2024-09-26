<?php

namespace App\Twig\Components;

use App\Service\CurrencyExchangeService;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;

#[AsLiveComponent()]
final class CurrencyConverter
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public array $supportedCurrencies;

    #[LiveProp(writable: true)]
    public ?string $fromCurrencyCode = null;

    #[LiveProp(writable: true)]
    public ?float $amount = null;

    public function __construct(
        private readonly CurrencyExchangeService $currencyExchangeService,
    ) {
    }
    
    public function getConvertedCurrencies(): array
    {
        if ($this->fromCurrencyCode === null || $this->amount === null) {
            return [];
        }

        $exchangeRates = $this->currencyExchangeService->getCurrencyExchangeRatesForCurrencyCode($this->fromCurrencyCode);
        return array_map(function (array $exchangeRate) {
            return [
                'currency' => $exchangeRate['code'],
                'amount' => $this->amount * $exchangeRate['rate'],
            ];
        }, $exchangeRates);
    }
}
