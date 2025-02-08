<?php

namespace App\Controller\API;

use App\Entity\Categorie;
use App\Service\SlugService;
use App\Repository\CategorieRepository;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route; 
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CategorieController extends AbstractController
{
    private SlugService $slugService;
    public function __construct(SlugService $slugService)
    {
        $this->slugService = $slugService;
    }
    #[Route("/api/categorie/", methods: ['GET'])] 
    public function index(CategorieRepository $categorieRepository, SerializerInterface $serializer)
    {
        $categories = $categorieRepository->findAll();
        return $this->json($categories, 200, [], [
            'groups' => ['categorie.index']
        ]);
    }

    #[Route("/api/categorie/{id}", requirements: ['id' => Requirement::DIGITS])] 
    public function show(Categorie $categorie)
    {
        return $this->json($categorie, 200, [], [
            'groups' => ['categorie.index', 'categorie.show']
        ]);
    }

    #[Route("/api/categorie/", methods: ['POST'])]
    public function create(
        Request $request,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        EntityManagerInterface $entityManager
    ): Response {
        $data = $request->getContent();

        try {
            // Désérialisation du JSON en objet Categorie
            $categorie = $serializer->deserialize($data, Categorie::class, 'json');

            // Si le slug est vide, on le génère à partir du nom
            if (!$categorie->getSlug()) {
                $categorie->setSlug($this->slugService->generateSlug($categorie->getNom()));
            }

            // Validation des données
            $errors = $validator->validate($categorie);
            if (count($errors) > 0) {
                return $this->json($errors, Response::HTTP_BAD_REQUEST);
            }

            // Ajout des timestamps
            $categorie->setCreatedAt(new \DateTimeImmutable());
            $categorie->setUpdatedAt(new \DateTimeImmutable());

            // Persistance en base de données
            $entityManager->persist($categorie);
            $entityManager->flush();

            return $this->json([
                'message' => 'La catégorie a été ajoutée avec succès.',
                'categorie' => $categorie
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erreur lors de la création de la catégorie.',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }
    #[Route("/api/categorie/edit/{id}", requirements: ['id' => Requirement::DIGITS], methods: ['PUT', 'PATCH'])]
    public function edit(
        int $id,
        Request $request,
        CategorieRepository $categorieRepository,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        EntityManagerInterface $entityManager
    ): Response {
        // Récupération de la catégorie via l'ID
        $categorie = $categorieRepository->find($id);
    
        // Si la catégorie n'existe pas, retourner une erreur 404
        if (!$categorie) {
            return $this->json([
                'error' => 'Catégorie non trouvée.'
            ], Response::HTTP_NOT_FOUND);
        }
    
        // Désérialisation du contenu de la requête en objet Categorie
        $data = $request->getContent();
        $serializer->deserialize($data, Categorie::class, 'json', ['object_to_populate' => $categorie]);
    
        // Si le slug est vide, on le génère à partir du nom
        if (!$categorie->getSlug()) {
            $categorie->setSlug($this->slugService->generateSlug($categorie->getNom()));
        }
    
        // Validation des données
        $errors = $validator->validate($categorie);
        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }
    
        // Mise à jour de la date de modification
        $categorie->setUpdatedAt(new \DateTimeImmutable());
    
        // Persistance des changements
        $entityManager->flush();
    
        return $this->json([
            'message' => 'La catégorie a été mise à jour avec succès.',
            'categorie' => $categorie
        ], Response::HTTP_OK);
    }
    #[Route("/api/categorie/delete/{id}", requirements: ['id' => Requirement::DIGITS], methods: ['DELETE'])]
    public function delete(
        int $id,
        CategorieRepository $categorieRepository,
        EntityManagerInterface $entityManager
    ): Response {
        // Récupération de la catégorie via l'ID
        $categorie = $categorieRepository->find($id);

        // Si la catégorie n'existe pas, retourner une erreur 404
        if (!$categorie) {
            return $this->json([
                'error' => 'Catégorie non trouvée.'
            ], Response::HTTP_NOT_FOUND);
        }

        // Suppression de la catégorie
        $entityManager->remove($categorie);
        $entityManager->flush();

        return $this->json([
            'message' => 'La catégorie a été supprimée avec succès.'
        ], Response::HTTP_OK);
    }
}