<?php

declare(strict_types=1);

namespace App\Form\Authentication;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ResetPasswordFormHandler
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {}

    public function handle(FormInterface $form, User $user): void
    {
        $data = $form->getData();

        $password = $this->passwordHasher->hashPassword($user, $data['password']);
        $user->setPassword($password);
        $user->setResetPasswordToken(null);

        $this->entityManager->flush();
    }
}