<?php

namespace App\Controller\API;

use App\Entity\Plat;
use App\Entity\Categorie;
use App\Entity\Ingredient;
use App\Repository\PlatRepository;
use App\Repository\IngredientRepository;
use App\Service\SlugService;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route; 
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PlatController extends AbstractController
{
    private SlugService $slugService;

    public function __construct(SlugService $slugService)
    {
        $this->slugService = $slugService;
    }

    #[Route("/api/plat/", methods: ['GET'])]
    public function index(PlatRepository $platRepository): Response
    {
        $plats = $platRepository->findAll();
        return $this->json($plats, 200, [], [
            'groups' => ['plat.index', 'ingredient.index']
        ]);
    }

    #[Route("/api/plat/{id}", requirements: ['id' => Requirement::DIGITS], methods: ['GET'])]
    public function show(Plat $plat): Response
    {
        return $this->json($plat, 200, [], [
            'groups' => ['plat.index', 'plat.show', 'ingredient.index']
        ]);
    }

    #[Route("/api/plat/", methods: ['POST'])]
    public function create(
        Request $request,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        EntityManagerInterface $entityManager,
        IngredientRepository $ingredientRepository
    ): Response {
        $data = json_decode($request->getContent(), true);

        // Vérifier si 'categorie' est bien présent dans les données
        if (!isset($data['categorie']) || !isset($data['categorie']['nom'])) {
            return $this->json([
                'error' => 'La clé "categorie" et son attribut "nom" sont manquants dans les données.'
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            // Vérifier si la catégorie existe déjà
            $categorieRepository = $entityManager->getRepository(Categorie::class);
            $categorie = $categorieRepository->findOneBy(['nom' => $data['categorie']['nom']]);

            // Si la catégorie n'existe pas, on la crée
            if (!$categorie) {
                $categorie = new Categorie();
                $categorie->setNom($data['categorie']['nom']);
                $categorie->setSlug(strtolower(str_replace(' ', '-', $data['categorie']['nom'])));
                $categorie->setCreatedAt(new \DateTimeImmutable());
                $categorie->setUpdatedAt(new \DateTimeImmutable());

                // Persister la nouvelle catégorie
                $entityManager->persist($categorie);
            }

            // Créer un plat et l'associer à la catégorie
            $plat = new Plat();
            $plat->setNom($data['nom']);
            $plat->setSlug(strtolower(str_replace(' ', '-', $data['nom'])));
            $plat->setDescription($data['description'] ?? null);
            $plat->setDuration($data['duration'] ?? null);
            $plat->setCreatedAt(new \DateTimeImmutable());
            $plat->setUpdatedAt(new \DateTimeImmutable());
            $plat->setCategorie($categorie);

            // Gestion des ingrédients
            if (isset($data['ingredients']) && is_array($data['ingredients'])) {
                foreach ($data['ingredients'] as $ingredientData) {
                    if (isset($ingredientData['id'])) {
                        $ingredient = $ingredientRepository->find($ingredientData['id']);
                        if ($ingredient) {
                            $plat->addIngredient($ingredient);
                        }
                    }
                }
            }

            // Valider les données du plat
            $errors = $validator->validate($plat);
            if (count($errors) > 0) {
                // Formater les erreurs dans un tableau lisible
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[] = $error->getMessage();
                }

                return $this->json([
                    'errors' => $errorMessages
                ], Response::HTTP_BAD_REQUEST);
            }

            // Enregistrer en base
            $entityManager->persist($plat);
            $entityManager->flush();

            // Retourner une réponse JSON avec l'objet plat sérialisé
            return $this->json([
                'message' => 'Le plat a été ajouté avec succès.',
                'plat' => $plat
            ], Response::HTTP_CREATED, [], ['groups' => ['plat.index', 'ingredient.index']]);

        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erreur lors de la création du plat.',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route("/api/plat/edit/{id}", requirements: ['id' => Requirement::DIGITS], methods: ['PUT', 'PATCH'])]
    public function edit(
        Request $request,
        int $id,
        PlatRepository $platRepository,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        EntityManagerInterface $entityManager,
        IngredientRepository $ingredientRepository
    ): Response {
        $plat = $platRepository->find($id);

        if (!$plat) {
            return $this->json([
                'error' => 'Plat non trouvé.'
            ], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        // Gérer la catégorie avant la désérialisation
        if (isset($data['categorie']) && isset($data['categorie']['nom'])) {
            $categorieRepository = $entityManager->getRepository(Categorie::class);
            $categorie = $categorieRepository->findOneBy(['nom' => $data['categorie']['nom']]);

            if (!$categorie) {
                // Créer une nouvelle catégorie avec tous les champs requis
                $categorie = new Categorie();
                $categorie->setNom($data['categorie']['nom']);
                $categorie->setSlug($data['categorie']['slug'] ?? strtolower(str_replace(' ', '-', $data['categorie']['nom'])));
                $categorie->setCreatedAt(new \DateTimeImmutable());
                $categorie->setUpdatedAt(new \DateTimeImmutable());
                $entityManager->persist($categorie);
            }

            // Mettre à jour le plat avec la catégorie
            $plat->setCategorie($categorie);
        }

        // Mettre à jour les autres champs du plat
        if (isset($data['nom'])) {
            $plat->setNom($data['nom']);
            $plat->setSlug(strtolower(str_replace(' ', '-', $data['nom'])));
        }
        if (isset($data['description'])) {
            $plat->setDescription($data['description']);
        }
        if (isset($data['duration'])) {
            $plat->setDuration($data['duration']);
        }

        // Gestion des ingrédients
        if (isset($data['ingredients']) && is_array($data['ingredients'])) {
            // Supprimer les ingrédients existants
            foreach ($plat->getIngredients() as $ingredient) {
                $plat->removeIngredient($ingredient);
            }

            // Ajouter les nouveaux ingrédients
            foreach ($data['ingredients'] as $ingredientData) {
                if (isset($ingredientData['id'])) {
                    $ingredient = $ingredientRepository->find($ingredientData['id']);
                    if ($ingredient) {
                        $plat->addIngredient($ingredient);
                    }
                }
            }
        }

        $plat->setUpdatedAt(new \DateTimeImmutable());

        // Validation des données
        $errors = $validator->validate($plat);
        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $entityManager->flush();

        return $this->json([
            'message' => 'Le plat a été mis à jour avec succès.',
            'plat' => $plat
        ], Response::HTTP_OK, [], ['groups' => ['plat.index', 'ingredient.index']]);
    }

    #[Route("/api/plat/delete/{id}", requirements: ['id' => Requirement::DIGITS], methods: ['DELETE'])]
    public function delete(
        int $id,
        PlatRepository $platRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $plat = $platRepository->find($id);

        if (!$plat) {
            return $this->json([
                'error' => 'Plat non trouvé.'
            ], Response::HTTP_NOT_FOUND);
        }

        $entityManager->remove($plat);
        $entityManager->flush();

        return $this->json([
            'message' => 'Le plat a été supprimé avec succès.'
        ], Response::HTTP_OK);
    }
}
