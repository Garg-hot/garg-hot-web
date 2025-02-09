<?php

namespace App\Entity;

use App\Repository\PrixRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PrixRepository::class)]
class Prix
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['plat.index', 'prix.index'])]
    private ?int $id = null;

    #[ORM\Column(type: "datetime")]
    #[Groups(['prix.index'])]
    private ?\DateTimeInterface $datePrix = null;

    #[ORM\ManyToOne(targetEntity: Plat::class, inversedBy: 'prix')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['prix.index'])]
    private ?Plat $plat = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Groups(['prix.index', 'plat.index'])]
    private ?string $montant = null;

    public function __construct()
    {
        $this->datePrix = new \DateTime();
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

    public function getPlat(): ?array // Retourne les détails du plat
    {
        return $this->plat ? [
            'id' => $this->plat->getId(),
            'name' => $this->plat->getNom(), // Assurez-vous que getName() existe dans Plat
        ] : null;
    }

    public function setPlat(?Plat $plat): static
    {
        $this->plat = $plat;
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