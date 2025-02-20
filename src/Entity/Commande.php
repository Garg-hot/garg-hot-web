<?php

namespace App\Entity;

use App\Repository\CommandeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CommandeRepository::class)]
class Commande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['commande.index', 'commande.show','vente.index', 'vente.show'])]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups(['commande.index', 'commande.show','vente.index', 'vente.show'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['commande.index', 'commande.show','vente.index', 'vente.show'])]
    private ?int $statut = null;

    #[ORM\Column(length: 255)]
    #[Groups(['commande.index', 'commande.show','vente.index', 'vente.show'])]
    private ?string $id_client = null;

    /**
     * @var Collection<int, Plat>
     */
    #[ORM\ManyToMany(targetEntity: Plat::class, inversedBy: 'commandes',cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(['commande.index', 'commande.show','vente.index', 'vente.show'])]
    private Collection $plats;

    public function __construct()
    {
        $this->plats = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable(); // Initialiser la date de création
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getStatut(): ?int
    {
        return $this->statut;
    }

    public function setStatut(int $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getIdClient(): ?string
    {
        return $this->id_client;
    }

    public function setIdClient(string $id_client): static
    {
        $this->id_client = $id_client;

        return $this;
    }

    /**
     * @return Collection<int, Plat>
     */
    public function getPlats(): Collection
    {
        return $this->plats;
    }

    public function addPlat(Plat $plat): static
    {
        if (!$this->plats->contains($plat)) {
            $this->plats->add($plat);
        }

        return $this;
    }

    public function removePlat(Plat $plat): static
    {
        $this->plats->removeElement($plat);

        return $this;
    }
}