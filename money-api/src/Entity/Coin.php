<?php

namespace App\Entity;

use App\Repository\CoinRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CoinRepository::class)]
class Coin
{
    #[ORM\Id]
    #[ORM\Column]
    private ?string $id = null;

    #[ORM\Column(length: 255)]
    private ?string $symbol = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * @var Collection<int, HistoricalCoinData>
     */
    #[ORM\OneToMany(targetEntity: HistoricalCoinData::class, mappedBy: 'base_currency')]
    private Collection $historicalCoinData;

    public function __construct()
    {
        $this->historicalCoinData = new ArrayCollection();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): static
    {
        $this->id = $id;

        return $this;
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

    /**
     * @return Collection<int, HistoricalCoinData>
     */
    public function getHistoricalCoinData(): Collection
    {
        return $this->historicalCoinData;
    }

    public function addHistoricalCoinData(HistoricalCoinData $historicalCoinData): static
    {
        if (!$this->historicalCoinData->contains($historicalCoinData)) {
            $this->historicalCoinData->add($historicalCoinData);
            $historicalCoinData->setBaseCurrency($this);
        }

        return $this;
    }

    public function removeHistoricalCoinData(HistoricalCoinData $historicalCoinData): static
    {
        if ($this->historicalCoinData->removeElement($historicalCoinData)) {
            // set the owning side to null (unless already changed)
            if ($historicalCoinData->getBaseCurrency() === $this) {
                $historicalCoinData->setBaseCurrency(null);
            }
        }

        return $this;
    }
}
