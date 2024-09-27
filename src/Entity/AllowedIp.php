<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\AllowedIpRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: AllowedIpRepository::class)]
class AllowedIp
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $ip = null;

    #[Assert\Callback]
    public function validate(ExecutionContextInterface $context) {
        $ip = $this->getIp();
        if (null === $ip || '' === $ip) {
            return;
        }

        if (preg_match('/^(\d{1,3}\.?){4}(\/\d{1,2})?$/', $ip, $matches) !== 1) {
            $context->buildViolation('The ip must be a valid ip address or ip subnet.')
                ->setParameter('{{ value }}', $ip)
                ->addViolation();
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function setIp(string $ip): self
    {
        $this->ip = $ip;

        return $this;
    }
}
