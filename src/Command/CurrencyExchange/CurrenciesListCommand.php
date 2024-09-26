<?php

namespace App\Command\CurrencyExchange;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Repository\CurrencyExchangeRateRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:currencies:list',
    description: 'Add a short description for your command',
)]
class CurrenciesListCommand extends Command
{
    public function __construct(
        private readonly CurrencyExchangeRateRepository $currencyExchangeRateRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->success('Currently supported currencies:' . implode(', ', $this->currencyExchangeRateRepository->getSupportedCurrencyCodes()));
        $io->info('You can add more currencies to the list of supported currencies with `php bin/console app:currencies:update [currency codes]`');
        return Command::SUCCESS;
    }
}
