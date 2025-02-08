<?php

namespace App\Controller\API;

use App\Entity\Ingredient;
use App\Repository\IngredientRepository;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class IngredientController extends AbstractController
{


    #[Route("/api/ingredient/", methods: ['GET'])]
    public function index(IngredientRepository $ingredientRepository): Response
    {
        $ingredients = $ingredientRepository->findAll();
        return $this->json($ingredients, 200, [], [
            'groups' => ['ingredient.index']
        ]);
    }

    #[Route("/api/ingredient/{id}", requirements: ['id' => Requirement::DIGITS], methods: ['GET'])]
    public function show(Ingredient $ingredient): Response
    {
        return $this->json($ingredient, 200, [], [
            'groups' => ['ingredient.index', 'ingredient.show']
        ]);
    }

    #[Route("/api/ingredient/", methods: ['POST'])]
    public function create(
        Request $request,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        EntityManagerInterface $entityManager
    ): Response {
        try {
            $data = json_decode($request->getContent(), true);

            // Créer un nouvel ingrédient
            $ingredient = new Ingredient();
            $ingredient->setNom($data['nom']);

            // Valider les données de l'ingrédient
            $errors = $validator->validate($ingredient);
            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[] = $error->getMessage();
                }
                return $this->json([
                    'errors' => $errorMessages
                ], Response::HTTP_BAD_REQUEST);
            }

            // Enregistrer en base
            $entityManager->persist($ingredient);
            $entityManager->flush();

            // Retourner une réponse JSON avec l'objet ingrédient sérialisé
            return $this->json([
                'message' => 'L\'ingrédient a été ajouté avec succès.',
                'ingredient' => $ingredient
            ], Response::HTTP_CREATED, [], ['groups' => ['ingredient.index']]);

        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erreur lors de la création de l\'ingrédient.',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route("/api/ingredient/edit/{id}", requirements: ['id' => Requirement::DIGITS], methods: ['PUT', 'PATCH'])]
    public function edit(
        int $id,
        Request $request,
        IngredientRepository $ingredientRepository,
        ValidatorInterface $validator,
        EntityManagerInterface $entityManager
    ): Response {
        $ingredient = $ingredientRepository->find($id);

        if (!$ingredient) {
            return $this->json([
                'error' => 'Ingrédient non trouvé.'
            ], Response::HTTP_NOT_FOUND);
        }

        try {
            $data = json_decode($request->getContent(), true);

            // Mise à jour des données
            if (isset($data['nom'])) {
                $ingredient->setNom($data['nom']);
            }
            

            // Validation des données
            $errors = $validator->validate($ingredient);
            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[] = $error->getMessage();
                }
                return $this->json([
                    'errors' => $errorMessages
                ], Response::HTTP_BAD_REQUEST);
            }

            $entityManager->flush();

            return $this->json([
                'message' => 'L\'ingrédient a été mis à jour avec succès.',
                'ingredient' => $ingredient
            ], Response::HTTP_OK, [], ['groups' => ['ingredient.index']]);

        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erreur lors de la mise à jour de l\'ingrédient.',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route("/api/ingredient/delete/{id}", requirements: ['id' => Requirement::DIGITS], methods: ['DELETE'])]
    public function delete(
        int $id,
        IngredientRepository $ingredientRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $ingredient = $ingredientRepository->find($id);

        if (!$ingredient) {
            return $this->json([
                'error' => 'Ingrédient non trouvé.'
            ], Response::HTTP_NOT_FOUND);
        }

        try {
            $entityManager->remove($ingredient);
            $entityManager->flush();

            return $this->json([
                'message' => 'L\'ingrédient a été supprimé avec succès.'
            ], Response::HTTP_OK);
            
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erreur lors de la suppression de l\'ingrédient.',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
