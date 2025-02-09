<?php

namespace App\Controller\API;

use App\Entity\Commande;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CommandeRepository;
use App\Repository\PlatRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CommandeController extends AbstractController
{
    #[Route("/api/commandes/", methods: ['GET'])]
    public function index(CommandeRepository $commandeRepository,PlatRepository $platRepository): Response
    {
        // Récupération des commandes avec leurs relations (plats et ingrédients)
        $commandes = $commandeRepository->findAllWithRelations();
    
        // Transformation des données pour correspondre au format souhaité
        $response = [];
        foreach ($commandes as $commande) {
            $plats = [];
            foreach ($commande->getPlats() as $plat) {
                $ingredients = [];
                foreach ($plat->getIngredients() as $ingredient) {
                    // Ajout des ingrédients au plat
                    $ingredients[] = ['nom' => $ingredient->getNom()];
                }
                
                // Transformation du plat pour ajouter le nom sous 'nom_plat'
                $plats[] = [
                    'id' => $plat->getId(),
                    'nom_plat' => $plat->getNom(),
                    'ingredients' => $ingredients,
                ];
            }
    
            // Structure de la réponse pour la commande avec les plats et ingrédients
            $response[] = [
                'commande_id' => $commande->getId(),
                'createdAt' => $commande->getCreatedAt()->format('c'),  // format ISO8601
                'statut' => $commande->getStatut(),
                'id_client' => $commande->getIdClient(),
                'plats' => $plats,
            ];
        }
    
        return $this->json($response, 200);
    }
    

    #[Route("/api/commandes/{id}", requirements: ['id' => Requirement::DIGITS], methods: ['GET'])]
    public function show(Commande $commande): Response
    {
        return $this->json($commande, 200, [], [
            'groups' => ['commande.index']
        ]);
    }
    #[Route("/api/commandes/", methods: ['POST'])]
    public function create(
        Request $request,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        CommandeRepository $commandeRepository,
        PlatRepository $platRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $data = json_decode($request->getContent(), true);

        $commande = new Commande();
        $commande->setStatut($data['statut']);
        $commande->setIdClient($data['id_client']);

        // Ajoutez votre logique pour gérer les plats
        if (isset($data['plats']) && is_array($data['plats'])) {
            foreach ($data['plats'] as $platData) {
                $plat = $platRepository->find($platData['id']);
                if ($plat) {
                    for ($i = 0; $i < $platData['quantite']; $i++) {
                        $commande->addPlat($plat); // Assurez-vous que cette méthode existe dans l'entité Commande
                    }
                }
            }
        }

        $errors = $validator->validate($commande);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        // Utiliser l'EntityManager pour persister et flusher la commande
        $entityManager->persist($commande);
        $entityManager->flush();

        return $this->json($commande, Response::HTTP_CREATED, [], [
            'groups' => ['commande.index']
        ]);
    }
    
    

    // #[Route("/api/commandes/{id}", methods: ['PUT'])]
    // public function update(Request $request, Commande $commande, CommandeRepository $commandeRepository): Response
    // {
    //     $data = json_decode($request->getContent(), true);
        
    //     // Mise à jour des champs de la commande
    //     if (isset($data['statut'])) {
    //         $commande->setStatut($data['statut']);
    //     }
    //     if (isset($data['id_client'])) {
    //         $commande->setIdClient($data['id_client']);
    //     }

    //     $commandeRepository->save($commande, true);

    //     return $this->json($commande, Response::HTTP_OK, [], [
    //         'groups' => ['commande.index']
    //     ]);
    // }

    #[Route("/api/commandes/{id}", methods: ['DELETE'])]
    public function delete(Commande $commande, CommandeRepository $commandeRepository): Response
    {
        $commandeRepository->remove($commande, true);

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
    #[Route("/api/commandes/{id}/plats", methods: ['GET'])]
    public function getPlatsByCommandeId(int $id, CommandeRepository $commandeRepository): Response
    {
        $commande = $commandeRepository->find($id);
        
        if (!$commande) {
            return $this->json(['error' => 'Commande non trouvée'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($commande->getPlats(), 200, [], [
            'groups' => ['plat.index']
        ]);
    }
}