<?php

namespace App\Tests\Unit\Authentication;

use App\Entity\User;
use Twig\Environment;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mime\Email;
use App\Repository\UserRepository;
use PHPUnit\Framework\Attributes\Test;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Form\Test\FormInterface;
use App\Form\Authentication\ForgotPasswordFormHandler;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

class ForgotPasswordTest extends TestCase
{
    private MockObject&UserRepository $userRepositoryMock;
    private MockObject&EntityManagerInterface $entityManagerMock;
    private MockObject&MailerInterface $mailerMock;
    private MockObject&Environment $twigRendererMock;
    private ForgotPasswordFormHandler $forgotPasswordFormHandler;

    public function setUp(): void
    {
        parent::setUp();

        $this->userRepositoryMock = $this->createMock(UserRepository::class);
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $this->mailerMock = $this->createMock(MailerInterface::class);
        $this->twigRendererMock = $this->createMock(Environment::class);
        $this->forgotPasswordFormHandler = new ForgotPasswordFormHandler(
            $this->userRepositoryMock,
            $this->entityManagerMock,
            $this->mailerMock,
            $this->twigRendererMock,
        );
    }

    #[Test]
    public function handleThrowsExceptionWhenUserNotFound()
    {
        /** @var MockObject&FormInterface $formMock */
        $formMock = $this->createMock(FormInterface::class);
        $formMock
            ->expects($this->once())
            ->method('getData')
            ->willReturn(['email' => 'test@example.com'])
        ;

        $this->userRepositoryMock
            ->expects($this->once())
            ->method('findOneBy')
            ->willReturn(null)
        ;

        $this->expectException(UserNotFoundException::class);
        $this->forgotPasswordFormHandler->handle($formMock);
    }

    #[Test]
    public function handleUpdatesUserResetPasswordToken()
    {
        /** @var MockObject&FormInterface $formMock */
        $formMock = $this->createMock(FormInterface::class);
        $formMock
            ->expects($this->once())
            ->method('getData')
        ;
        
        $userMock = $this->createMock(User::class);
        $userMock
            ->expects($this->once())
            ->method('setResetPasswordToken')
            ->with($this->isType('string'))
        ;
        $userMock
            ->method('getEmail')
            ->willReturn('user@example.com')
        ;

        $this->userRepositoryMock
            ->expects($this->once())
            ->method('findOneBy')
            ->willReturn($userMock)
        ;

        $this->entityManagerMock
            ->expects($this->once())
            ->method('flush')
        ;

        $this->forgotPasswordFormHandler->handle($formMock);
    }

    #[Test]
    public function handleSendsEmail()
    {
        /** @var MockObject&FormInterface $formMock */
        $formMock = $this->createMock(FormInterface::class);
        $formMock
            ->expects($this->once())
            ->method('getData')
        ;
        
        $userMock = $this->createMock(User::class);
        $userMock
            ->expects($this->once())
            ->method('getEmail')
            ->willReturn('user@example.com')
        ;

        $this->userRepositoryMock
            ->expects($this->once())
            ->method('findOneBy')
            ->willReturn($userMock)
        ;

        $this->twigRendererMock
            ->expects($this->once())
            ->method('render')
            ->willReturn('htmlBody')
        ;

        $this->mailerMock
            ->expects($this->once())
            ->method('send')
            ->with($this->callback(function ($email) {
                $this->assertSame('user@example.com', current($email->getTo())->getAddress());
                $this->assertSame('htmlBody', $email->getHtmlBody());
                return true;
            }))
        ;

        $this->forgotPasswordFormHandler->handle($formMock);
    }
}
