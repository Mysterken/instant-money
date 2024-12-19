<?php

namespace App\Entity;

use App\Repository\HistoricalExchangeRateRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HistoricalExchangeRateRepository::class)]
class HistoricalExchangeRate
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\ManyToOne(inversedBy: 'historicalExchangeRates')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Currency $base_currency = null;

    #[ORM\ManyToOne(inversedBy: 'historicalExchangeRates')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Currency $currency = null;

    #[ORM\Column]
    private ?float $value = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getBaseCurrency(): ?Currency
    {
        return $this->base_currency;
    }

    public function setBaseCurrency(?Currency $base_currency): static
    {
        $this->base_currency = $base_currency;

        return $this;
    }

    public function getCurrency(): ?Currency
    {
        return $this->currency;
    }

    public function setCurrency(?Currency $currency): static
    {
        $this->currency = $currency;

        return $this;
    }

    public function getValue(): ?float
    {
        return $this->value;
    }

    public function setValue(float $value): static
    {
        $this->value = $value;

        return $this;
    }
}
