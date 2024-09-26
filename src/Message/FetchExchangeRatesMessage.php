<?php

namespace App\Message;

final class FetchExchangeRatesMessage
{
    /**
     * @param string[] $currencyCodes
     */
    public function __construct(public array $currencyCodes)
    {
    }
}
