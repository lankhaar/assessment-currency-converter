<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\AllowedIp;
use App\Repository\AllowedIpRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class IpRestrictionListener
{
    public function __construct(
        #[Autowire('%app.allowed_ips%')]
        private readonly array $allowedIps,
        private readonly AllowedIpRepository $allowedIpRepository,
        private readonly LoggerInterface $logger
    ) {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $clientIp = $request->getClientIp();

        $this->verifyIp($clientIp);
    }

    protected function verifyIp(string $clientIp): void
    {
        foreach ($this->getAllowedIps() as $allowedIp) {
            if ($allowedIp === $clientIp) {
                return;
            }

            if ($this->isIpInRange($allowedIp, $clientIp)) {
                return;
            }
        }

        $this->logger->warning('Access denied for IP address {ip}', ['ip' => $clientIp]);

        throw new AccessDeniedHttpException('Access denied for your IP address.');
    }

    protected function getAllowedIps(): \Generator
    {
        // Check ip config first to possibly prevent unnecessary database queries
        yield from $this->allowedIps;

        yield from array_map(fn (AllowedIp $allowedIp) => $allowedIp->getIp(), $this->allowedIpRepository->findAll());
    }

    protected function isIpInRange(string $range, string $ip): bool
    {
        // Check if string is a valid subnet
        if (!str_contains($range, '/')) {
            return false;
        }

        [$network, $mask] = explode('/', $range);
        $networkLong = ip2long($network);
        $ipLong = ip2long($ip);
        $mask = ~((1 << (32 - (int)$mask)) - 1);
        return ($ipLong & $mask) === ($networkLong & $mask);
    }
}
