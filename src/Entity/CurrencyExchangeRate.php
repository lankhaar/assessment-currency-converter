<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Traits\HasTimestamps;
use App\Repository\CurrencyExchangeRateRepository;

#[ORM\Entity(repositoryClass: CurrencyExchangeRateRepository::class)]
class CurrencyExchangeRate
{
    use HasTimestamps;

    #[ORM\Id]
    #[ORM\Column]
    private ?string $id = null;

    #[ORM\Column(length: 3)]
    private string $fromCurrencyCode;

    #[ORM\Column(length: 3)]
    private string $toCurrencyCode;

    #[ORM\Column]
    private float $exchangeRate;

    #[ORM\Column]
    private float $invertedExchangeRate;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getFromCurrencyCode(): string
    {
        return $this->fromCurrencyCode;
    }

    public function setFromCurrencyCode(string $fromCurrencyCode): self
    {
        $this->fromCurrencyCode = strtoupper($fromCurrencyCode);

        return $this;
    }

    public function getToCurrencyCode(): string
    {
        return $this->toCurrencyCode;
    }

    public function setToCurrencyCode(string $toCurrencyCode): self
    {
        $this->toCurrencyCode = strtoupper($toCurrencyCode);

        return $this;
    }

    public function getExchangeRate(): float
    {
        return $this->exchangeRate;
    }

    public function setExchangeRate(float $exchangeRate): self
    {
        $this->exchangeRate = $exchangeRate;

        return $this;
    }

    public function getInvertedExchangeRate(): float
    {
        return $this->invertedExchangeRate;
    }

    public function setInvertedExchangeRate(float $invertedExchangeRate): self
    {
        $this->invertedExchangeRate = $invertedExchangeRate;

        return $this;
    }
}
