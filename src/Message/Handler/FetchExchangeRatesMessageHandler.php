<?php

namespace App\Message\Handler;

use App\Service\CurrencyExchangeService;
use App\Message\FetchExchangeRatesMessage;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class FetchExchangeRatesMessageHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly CurrencyExchangeService $currencyExchangeService,
    ) {
    }

    public function __invoke(FetchExchangeRatesMessage $message)
    {
        $this->currencyExchangeService->updateExchangeRates($message->currencyCodes);
    }
}
