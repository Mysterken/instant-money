<?php

namespace App\Entity;

use App\Repository\CurrencyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CurrencyRepository::class)]
class Currency
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $symbol = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $symbol_native = null;

    #[ORM\Column]
    private ?int $decimal_digits = null;

    #[ORM\Column]
    private ?int $rounding = null;

    #[ORM\Column(length: 255)]
    private ?string $code = null;

    #[ORM\Column(length: 255)]
    private ?string $name_plural = null;

    /**
     * @var Collection<int, HistoricalExchangeRate>
     */
    #[ORM\OneToMany(targetEntity: HistoricalExchangeRate::class, mappedBy: 'base_currency')]
    private Collection $historicalExchangeRates;

    public function __construct()
    {
        $this->historicalExchangeRates = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSymbol(): ?string
    {
        return $this->symbol;
    }

    public function setSymbol(string $symbol): static
    {
        $this->symbol = $symbol;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getSymbolNative(): ?string
    {
        return $this->symbol_native;
    }

    public function setSymbolNative(string $symbol_native): static
    {
        $this->symbol_native = $symbol_native;

        return $this;
    }

    public function getDecimalDigits(): ?int
    {
        return $this->decimal_digits;
    }

    public function setDecimalDigits(int $decimal_digits): static
    {
        $this->decimal_digits = $decimal_digits;

        return $this;
    }

    public function getRounding(): ?int
    {
        return $this->rounding;
    }

    public function setRounding(int $rounding): static
    {
        $this->rounding = $rounding;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function getNamePlural(): ?string
    {
        return $this->name_plural;
    }

    public function setNamePlural(string $name_plural): static
    {
        $this->name_plural = $name_plural;

        return $this;
    }

    /**
     * @return Collection<int, HistoricalExchangeRate>
     */
    public function getHistoricalExchangeRates(): Collection
    {
        return $this->historicalExchangeRates;
    }

    public function addHistoricalExchangeRate(HistoricalExchangeRate $historicalExchangeRate): static
    {
        if (!$this->historicalExchangeRates->contains($historicalExchangeRate)) {
            $this->historicalExchangeRates->add($historicalExchangeRate);
            $historicalExchangeRate->setBaseCurrency($this);
        }

        return $this;
    }

    public function removeHistoricalExchangeRate(HistoricalExchangeRate $historicalExchangeRate): static
    {
        if ($this->historicalExchangeRates->removeElement($historicalExchangeRate)) {
            // set the owning side to null (unless already changed)
            if ($historicalExchangeRate->getBaseCurrency() === $this) {
                $historicalExchangeRate->setBaseCurrency(null);
            }
        }

        return $this;
    }
}
