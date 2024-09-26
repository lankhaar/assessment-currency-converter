<?php

namespace App\Tests\Form\Authentication;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Depends;
use App\Tests\Application\ApplicationTestCase;

class ForgotPasswordTest extends ApplicationTestCase
{
    #[Test]
    public function forgotPasswordRedirectsToHomepageWhenLoggedIn(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getRegularUser());
        $client->request('GET', '/auth/forgot-password');

        $this->assertResponseRedirects('/');
    }

    #[Test]
    public function canSeeForgotPasswordFormView(): void
    {
        $client = static::createClient();
        $client->request('GET', '/auth/forgot-password');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.card-header', 'Forgot Password');
    }

    #[Test]
    public function forgotPasswordFormSubmissionWithInvalidEmail(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/auth/forgot-password');
        
        $form = $crawler->selectButton('Request Password Reset')->form();
        $form['forgot_password_form[email]'] = 'invalid@email.com';

        $client->submit($form);

        $this->assertStringContainsString('User not found.', $client->getResponse()->getContent());
    }

    #[Test]
    public function forgotPasswordFormSubmission(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/auth/forgot-password');
        
        $form = $crawler->selectButton('Request Password Reset')->form();
        $form['forgot_password_form[email]'] = 'user@example.com';

        $client->submit($form);

        $this->assertResponseRedirects('/auth/login');
    }

    #[Test]
    public function resetPasswordWithInvalidToken(): void
    {
        $client = static::createClient();
        $client->request('GET', '/auth/reset-password/invalid-token');

        $this->assertResponseRedirects('/auth/login');
    }

    #[Test]
    #[Depends('forgotPasswordFormSubmission')]
    public function resetPasswordView(): void
    {
        $client = static::createClient();
        $user = $this->getRegularUser();
        $client->request('GET', '/auth/reset-password/' . $user->getResetPasswordToken());

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.card-header', 'Reset Password');
    }

    #[Test]
    #[Depends('resetPasswordView')]
    public function resetPasswordSubmission(): void
    {
        $client = static::createClient();
        $user = $this->getRegularUser();
        $crawler = $client->request('GET', '/auth/reset-password/' . $user->getResetPasswordToken());
        
        $form = $crawler->selectButton('Reset Password')->form();
        $form['reset_password_form[password]'] = 'newpassword';

        $client->submit($form);

        $this->assertResponseRedirects('/auth/login');
    }
}
