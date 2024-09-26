<?php

declare(strict_types=1);

namespace App\Tests\Application;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ApplicationTestCase extends WebTestCase
{
    protected function getRegularUser(): User
    {
        return static::getContainer()->get(UserRepository::class)->findOneBy(['email' => 'user@example.com']);
    }

    protected function getAdminUser(): User
    {
        return static::getContainer()->get(UserRepository::class)->findOneBy(['email' => 'admin@example.com']);
    }
}
