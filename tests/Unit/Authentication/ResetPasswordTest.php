<?php

namespace App\Tests\Unit\Authentication;

use App\Entity\User;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\Test\FormInterface;
use App\Form\Authentication\ResetPasswordFormHandler;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ResetPasswordTest extends TestCase
{
    private MockObject&EntityManagerInterface $entityManager;
    private MockObject&UserPasswordHasherInterface $passwordHasher;
    private ResetPasswordFormHandler $resetPasswordFormHandler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->passwordHasher = $this->createMock(UserPasswordHasherInterface::class);

        $this->resetPasswordFormHandler = new ResetPasswordFormHandler(
            $this->entityManager,
            $this->passwordHasher,
        );
    }

    #[Test]
    public function ensurePasswordIsHashed(): void
    {
        /** @var MockObject&User $user */
        $user = $this->createMock(User::class);
        /** @var MockObject&FormInterface $form */
        $form = $this->createMock(FormInterface::class);
        $form->expects($this->once())
            ->method('getData')
            ->willReturn(['password' => 'plainPassword'])
        ;

        $this->passwordHasher->expects($this->once())
            ->method('hashPassword')
            ->with($user, 'plainPassword')
            ->willReturn('hashedPassword')
        ;

        $user->expects($this->once())
            ->method('setPassword')
            ->with('hashedPassword')
            ->willReturnSelf();
        ;

        $this->entityManager
            ->expects($this->once())
            ->method('flush')
        ;

        $this->resetPasswordFormHandler->handle($form, $user);
    }

    #[Test]
    public function ensureResetPasswordTokenIsRemoved(): void
    {
        /** @var MockObject&User $user */
        $user = $this->createMock(User::class);
        /** @var MockObject&FormInterface $form */
        $form = $this->createMock(FormInterface::class);
        $form->expects($this->once())
            ->method('getData')
            ->willReturn(['password' => 'plainPassword'])
        ;

        $user->expects($this->once())
            ->method('setResetPasswordToken')
            ->with(null)
        ;

        $this->resetPasswordFormHandler->handle($form, $user);
    }
}
