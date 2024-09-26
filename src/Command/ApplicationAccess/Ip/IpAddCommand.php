<?php

namespace App\Command\ApplicationAccess\Ip;

use App\Entity\AllowedIp;
use App\Repository\AllowedIpRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:access:ip:add',
    description: 'Add a new IP address to the allowed list',
)]
class IpAddCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly AllowedIpRepository $allowedIpRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('ip', InputArgument::REQUIRED, 'The IP address to add');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $ip = $input->getArgument('ip');

        if ($this->allowedIpRepository->findOneBy(['ip' => $ip])) {
            $io->error('This IP address is already allowed.');
            return Command::FAILURE;
        }

        $this->allowedIpRepository->createAllowedIp($ip);

        $io->success('IP address successfully added.');

        return Command::SUCCESS;
    }
}
