<?php

declare(strict_types=1);

namespace App\Form\Authentication;

use App\Entity\User;
use Twig\Environment;
use Symfony\Component\Mime\Email;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\String\ByteString;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

class ForgotPasswordFormHandler
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly MailerInterface $mailer,
        private readonly Environment $twigRenderer,
    ) {
    }

    public function handle(FormInterface $form)
    {
        $data = $form->getData();
        $user = $this->userRepository->findOneBy(['email' => $data['email']]);

        if (!$user instanceof User) {
            throw new UserNotFoundException('User not found.');
        }

        $token = (string) ByteString::fromRandom(32);
        $user->setResetPasswordToken($token);

        $this->entityManager->flush();

        $htmlBody = $this->twigRenderer->render(
            'mail/authentication/forgot_password.html.twig',
            ['token' => $token]
        );

        $message = (new Email())
            ->from('noreply@currencyconverter.com')
            ->to($user->getEmail())
            ->subject('Password Reset Request')
            ->text(strip_tags($htmlBody))
            ->html($htmlBody)
        ;

        $this->mailer->send($message);
    }
}
