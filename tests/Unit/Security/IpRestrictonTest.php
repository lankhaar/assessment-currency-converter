<?php

namespace App\Tests\Unit\Authentication;

use App\Entity\AllowedIp;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Repository\AllowedIpRepository;
use App\Security\IpRestrictionListener;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class IpRestrictonTest extends TestCase
{
    private array $allowedIpsConfig = ['127.0.0.1'];
    private array $constructorArgs;
    private MockObject&AllowedIpRepository $allowedIpRepository;
    private IpRestrictionListener $ipRestrictionListener;

    protected function setUp(): void
    {
        parent::setUp();

        $this->constructorArgs = [
            $this->allowedIpsConfig, 
            $this->allowedIpRepository = $this->createMock(AllowedIpRepository::class),
        ];
        $this->ipRestrictionListener = new IpRestrictionListener(...$this->constructorArgs);
    }

    #[Test]
    public function ensureGetAllowedIpsNotExecutingUnnecessaryQueries(): void
    {
        $this->allowedIpRepository
            ->expects($this->never())
            ->method('findAll')
        ;

        // Call verifyIp method with an IP from config
        $this->getReflectionMethodForClass('verifyIp', IpRestrictionListener::class)->invoke($this->ipRestrictionListener, '127.0.0.1');
    }

    #[Test]
    public function ensureGetAllowedIpsFetchesAllIps(): void
    {
        $this->allowedIpRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn([
                (new AllowedIp())->setIp('192.168.1.1'),
                (new AllowedIp())->setIp('192.168.1.2'),
            ])
        ;

        // Call verifyIp method with an IP from config
        $this->getReflectionMethodForClass('verifyIp', IpRestrictionListener::class)->invoke($this->ipRestrictionListener, '192.168.1.1');
    }

    #[Test]
    public function canVerifyIpSubnet(): void
    {
        $reflectionMethod = $this->getReflectionMethodForClass('isIpInRange', IpRestrictionListener::class);
        $ipInSubnet = $reflectionMethod->invoke($this->ipRestrictionListener, '192.168.1.1/24', '192.168.1.1');
        $ipNotInSubnet = $reflectionMethod->invoke($this->ipRestrictionListener, '192.168.1.1/24', '192.168.2.1');

        $this->assertTrue($ipInSubnet);
        $this->assertFalse($ipNotInSubnet);
    }

    #[Test]
    public function onKernelRequestVerifiesIp(): void
    {
        /** @var RequestEvent&MockObject $eventMock */
        $eventMock = $this->createMock(RequestEvent::class);
        $eventMock
            ->expects($this->once())
            ->method('getRequest')
            ->willReturn($request = new Request())
        ;

        $request->server->set('REMOTE_ADDR', '127.0.0.1');

        $partialMock = $this->getIpRestrictionListenerPartialMock(['verifyIp']);
        $partialMock
            ->expects($this->once())
            ->method('verifyIp')
            ->with('127.0.0.1')
        ;
        $partialMock->onKernelRequest($eventMock);
    }

    protected function getReflectionMethodForClass(string $method, string $class): \ReflectionMethod
    {
        return (new \ReflectionClass($class))->getMethod($method);
    }

    
    protected function getIpRestrictionListenerPartialMock(array $methods): IpRestrictionListener&MockObject
    {
        return $this->getMockBuilder(IpRestrictionListener::class)
            ->disableOriginalClone()
            ->onlyMethods($methods)
            ->setConstructorArgs($this->constructorArgs)
            ->getMock();
    }
}
