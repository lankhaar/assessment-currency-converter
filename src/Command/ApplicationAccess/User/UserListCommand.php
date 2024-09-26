<?php

namespace App\Command\ApplicationAccess\User;

use App\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:access:user:list',
    description: 'List all users',
)]
class UserListCommand extends Command
{
    public function __construct(
        private readonly UserRepository $userRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        // No arguments or options needed for this command
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $users = $this->userRepository->findAll();

        if ($users === []) {
            $io->warning('No users found.');
            return Command::SUCCESS;
        }

        $tableRows = [];
        foreach ($users as $user) {
            $tableRows[] = [$user->getId(), $user->getEmail(), implode(', ', $user->getRoles())];
        }

        $io->table(['ID', 'Email', 'Roles'], $tableRows);

        return Command::SUCCESS;
    }
}
