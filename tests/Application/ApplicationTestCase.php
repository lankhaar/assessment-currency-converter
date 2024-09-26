<?php

declare(strict_types=1);

namespace App\Tests\Application;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ApplicationTestCase extends WebTestCase
{
    protected static function createClient(array $options = [], array $server = [], bool $useRestrictedIp = false): KernelBrowser
    {
        if ($useRestrictedIp) {
            return parent::createClient(...func_get_args());
        }

        $server['REMOTE_ADDR'] = '192.168.1.1';

        return parent::createClient($options, $server);
    }

    protected function getRegularUser(): User
    {
        return static::getContainer()->get(UserRepository::class)->findOneBy(['email' => 'user@example.com']);
    }

    protected function getAdminUser(): User
    {
        return static::getContainer()->get(UserRepository::class)->findOneBy(['email' => 'admin@example.com']);
    }
}
