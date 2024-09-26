<?php

namespace App\Tests\Form\Authentication;

use PHPUnit\Framework\Attributes\Test;
use App\Tests\Application\ApplicationTestCase;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class IpRestrictonTest extends ApplicationTestCase
{
    #[Test]
    public function failOnAccessWithRestrictedIp(): void
    {
        $client = static::createClient(useRestrictedIp: true);

        $this->expectException(AccessDeniedHttpException::class);
        $client->request('GET', '/auth/login');
        
        $this->assertResponseStatusCodeSame(403);
    }
}
