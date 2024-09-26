<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserAccountFixtures extends Fixture
{
    public const USER_INFORMATION = [
        [
            'email' => 'user@example.com',
            'password' => 'password',
            'roles' => ['ROLE_USER'],
        ],
        [
            'email' => 'admin@example.com',
            'password' => 'admin',
            'roles' => ['ROLE_USER', 'ROLE_ADMIN'],
        ],
    ];

    public function __construct(private readonly UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        foreach (self::USER_INFORMATION as $userInformation) {
            $user = new User();

            $user->setEmail($userInformation['email']);
            $user->setPassword($this->passwordHasher->hashPassword($user, $userInformation['password']));
            $user->setRoles($userInformation['roles']);

            $manager->persist($user);
        }

        $manager->flush();
    }
}
