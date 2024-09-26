<?php

namespace App\Command\ApplicationAccess\Ip;

use App\Entity\AllowedIp;
use App\Repository\AllowedIpRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:access:ip:remove',
    description: 'Remove an IP address from the allowed list',
)]
class IpRemoveCommand extends Command
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
            ->addArgument('ip', InputArgument::REQUIRED, 'The IP address to remove');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $ip = $input->getArgument('ip');

        $allowedIp = $this->allowedIpRepository->findOneBy(['ip' => $ip]);

        if (!$allowedIp instanceof AllowedIp) {
            $io->error('IP address not found.');
            return Command::FAILURE;
        }

        $this->entityManager->remove($allowedIp);
        $this->entityManager->flush();

        $io->success('IP address successfully removed.');

        return Command::SUCCESS;
    }
}
