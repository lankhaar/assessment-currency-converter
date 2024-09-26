<?php

namespace App\Command\CurrencyExchange;

use App\Service\CurrencyExchangeService;
use App\Message\FetchExchangeRatesMessage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Repository\CurrencyExchangeRateRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:currencies:update',
    description: 'Update the exchange rates for the given currencies. You can provide not yet supported currency codes to add them.',
)]
class CurrenciesUpdateCommand extends Command
{
    public function __construct(
        private readonly CurrencyExchangeRateRepository $currencyExchangeRateRepository,
        private readonly CurrencyExchangeService $currencyExchangeService,
        private readonly MessageBusInterface $messageBus,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('currencies', InputArgument::IS_ARRAY, 'Currencies to update')
            ->addOption('all', null, InputOption::VALUE_NONE, 'Update all existing currencies')
            ->addOption('async', null, InputOption::VALUE_NONE, 'Update currencies asynchronously')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $currencies = $input->getArgument('currencies');
        $all = $input->getOption('all');

        if ($currencies !== [] && $all === true) {
            $io->error('Cannot use both currencies and all options');
            return Command::FAILURE;
        }

        if ($currencies === [] && $all === false) {
            $io->error('You must specify at least one currency or use the --all option');
            return Command::FAILURE;
        }

        if ($all === true) {
            $currencies = $this->currencyExchangeRateRepository->getSupportedCurrencyCodes();
        }

        $io->info('Updating currencies: ' . implode(', ', $currencies));

        if ($input->getOption('async')) {
            $this->messageBus->dispatch(new FetchExchangeRatesMessage($currencies));
            $io->success('Async update dispatched.');
            return Command::SUCCESS;
        }

        $this->currencyExchangeService->updateExchangeRates($currencies);
        $io->success('Currencies updated.');
        return Command::SUCCESS;
    }
}
