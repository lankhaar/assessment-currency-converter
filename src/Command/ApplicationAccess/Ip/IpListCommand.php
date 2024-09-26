<?php

namespace App\Command\ApplicationAccess\Ip;

use App\Repository\AllowedIpRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:access:ip:list',
    description: 'List all allowed IP addresses',
)]
class IpListCommand extends Command
{
    public function __construct(
        private readonly AllowedIpRepository $allowedIpRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $ips = $this->allowedIpRepository->findAll();

        if ($ips === []) {
            $io->warning('No IP addresses found.');
            return Command::SUCCESS;
        }

        $tableRows = [];
        foreach ($ips as $ip) {
            $tableRows[] = [$ip->getId(), $ip->getIp()];
        }

        $io->table(['ID', 'IP Address'], $tableRows);

        return Command::SUCCESS;
    }
}
