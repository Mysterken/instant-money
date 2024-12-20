<?php

namespace App\Entity;

use App\Repository\HistoricalCoinDataRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HistoricalCoinDataRepository::class)]
class HistoricalCoinData
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(length: 255)]
    private ?string $base_currency = null;

    #[ORM\ManyToOne(inversedBy: 'historicalCoinData')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Coin $currency = null;

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

    public function getBaseCurrency(): ?string
    {
        return $this->base_currency;
    }

    public function setBaseCurrency(?string $base_currency): static
    {
        $this->base_currency = $base_currency;

        return $this;
    }

    public function getCurrency(): ?Coin
    {
        return $this->currency;
    }

    public function setCurrency(?Coin $currency): static
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
