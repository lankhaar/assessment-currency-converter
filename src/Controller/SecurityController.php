<?php

namespace App\Controller;

use App\Entity\User;
use Psr\Log\LoggerInterface;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\Authentication\ResetPasswordFormType;
use App\Form\Authentication\ForgotPasswordFormType;
use App\Form\Authentication\ResetPasswordFormHandler;
use App\Form\Authentication\ForgotPasswordFormHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

#[Route('/auth')]
class SecurityController extends AbstractController
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            $this->logger->info('User is already logged in, redirecting to home');
            return $this->redirectToRoute('app_home');
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        $this->logger->info('Rendering login page');
        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/forgot-password', name: 'app_forgot_password')]
    public function forgotPassword(Request $request, ForgotPasswordFormHandler $formHandler): Response
    {
        if ($this->getUser()) {
            $this->logger->info('User is already logged in, redirecting to home');
            return $this->redirectToRoute('app_home');
        }

        $form = $this->createForm(ForgotPasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->logger->debug('Form is submitted and valid');
                $formHandler->handle($form);

                $this->addFlash('success', 'A password reset link has been sent to your email.');
    
                $this->logger->info('Forgot password form handled successfully');
                return $this->redirectToRoute('app_login');
            } catch (UserNotFoundException $e) {
                $this->logger->info('User not found', ['exception' => $e]);
                $this->addFlash('danger', 'User not found.');
            }
        }

        return $this->render('security/forgot_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/reset-password/{token}', name: 'app_reset_password')]
    public function resetPassword(Request $request, string $token, UserRepository $userRepository, ResetPasswordFormHandler $formHandler): Response
    {
        $form = $this->createForm(ResetPasswordFormType::class);
        $form->handleRequest($request);

        $user = $userRepository->findOneBy(['resetPasswordToken' => $token]);
        if (!$user instanceof User) {
            $this->logger->error('User not found for token', ['token' => $token]);
            $this->addFlash('danger', 'Invalid token.');
            return $this->redirectToRoute('app_login');
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $this->logger->debug('Form is submitted and valid');
            $formHandler->handle($form, $user);

            $this->addFlash('success', 'Password has been reset.');

            $this->logger->info('Password reset form handled successfully');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/reset_password.html.twig', [
            'token' => $token,
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on the firewall.');
    }
}
