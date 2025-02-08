<?php

namespace App\Entity;

use App\Repository\PrixRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PrixRepository::class)]
class Prix
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $datePrix = null;

    /**
     * @var Collection<int, Plat>
     */
    #[ORM\OneToMany(targetEntity: Plat::class, mappedBy: 'prix')]
    private Collection $plat;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $montant = null;

    public function __construct()
    {
        $this->plat = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDatePrix(): ?\DateTimeInterface
    {
        return $this->datePrix;
    }

    public function setDatePrix(\DateTimeInterface $datePrix): static
    {
        $this->datePrix = $datePrix;

        return $this;
    }

    /**
     * @return Collection<int, Plat>
     */
    public function getPlat(): Collection
    {
        return $this->plat;
    }

    public function addPlat(Plat $plat): static
    {
        if (!$this->plat->contains($plat)) {
            $this->plat->add($plat);
            $plat->setPrix($this);
        }

        return $this;
    }

    public function removePlat(Plat $plat): static
    {
        if ($this->plat->removeElement($plat)) {
            // set the owning side to null (unless already changed)
            if ($plat->getPrix() === $this) {
                $plat->setPrix(null);
            }
        }

        return $this;
    }

    public function getMontant(): ?string
    {
        return $this->montant;
    }

    public function setMontant(string $montant): static
    {
        $this->montant = $montant;

        return $this;
    }
}
