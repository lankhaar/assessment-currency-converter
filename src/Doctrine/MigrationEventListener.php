<?php

declare(strict_types=1);

namespace App\Doctrine;

use App\Service\CurrencyExchangeService;
use App\Message\FetchExchangeRatesMessage;
use Doctrine\Migrations\Event\MigrationsVersionEventArgs;
use DoctrineMigrations\Version20240926152003;
use Symfony\Component\Messenger\MessageBusInterface;

class MigrationEventListener
{
    public function __construct(private readonly MessageBusInterface $messageBus)
    {
    }

    public function onMigrationsVersionExecuted(MigrationsVersionEventArgs $args): void
    {
        if ($args->getPlan()->getVersion()->__toString() !== Version20240926152003::class) {
            return;
        }

        foreach (CurrencyExchangeService::DEFAULT_SUPPORTED_CURRENCY_CODES as $currencyCode) {
            $this->messageBus->dispatch(new FetchExchangeRatesMessage([$currencyCode]));
        }
    }
}