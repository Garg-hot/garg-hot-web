<?php

namespace App\Entity;

use App\Repository\IngredientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: IngredientRepository::class)]
#[UniqueEntity('nom', message: 'ce nom est deja utilisée')]
class Ingredient
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['ingredient.index', 'ingredient.create', 'plat.index'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['ingredient.index', 'ingredient.create', 'plat.index','commande.index'])]
    private ?string $nom = null;

    /**
     * @var Collection<int, Plat>
     */
    #[ORM\ManyToMany(targetEntity: Plat::class, mappedBy: 'ingredients',cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $plats;

    #[ORM\Column(length: 255)]
    #[Groups(['ingredient.index', 'ingredient.create', 'plat.index'])]
    private ?string $sprite = null;

    public function __construct()
    {
        $this->plats = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

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
            $plat->addIngredient($this);
        }

        return $this;
    }

    public function removePlat(Plat $plat): static
    {
        if ($this->plats->removeElement($plat)) {
            $plat->removeIngredient($this);
        }

        return $this;
    }

    public function getSprite(): ?string
    {
        return $this->sprite;
    }

    public function setSprite(string $sprite): static
    {
        if (empty($sprite)) {
            throw new \Exception("La valeur de sprite ne peut pas être vide");
        }
        $this->sprite = $sprite;
        return $this;
    }
}
