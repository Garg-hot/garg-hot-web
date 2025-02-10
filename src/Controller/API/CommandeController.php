<?php

namespace App\Controller\API;

use App\Entity\Commande;
use App\Repository\CommandeRepository;
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
    public function index(CommandeRepository $commandeRepository): Response
    {
        $commandes = $commandeRepository->findAllWithRelations();
        return $this->json($commandes, 200, [], [
            'groups' => ['commande.index']
        ]);
    }
    #[Route("/api/commandes/", methods: ['POST'])]
    public function create(
        Request $request,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        CommandeRepository $commandeRepository
    ): Response {
        $data = json_decode($request->getContent(), true);
        
        // Validation et création de la commande
        $commande = new Commande();
        $commande->setCreatedAt(new \DateTimeImmutable());
        $commande->setStatut($data['statut']);
        $commande->setIdClient($data['id_client']);
        
        // Ajoutez ici la logique pour ajouter des plats à la commande si nécessaire
        
        $errors = $validator->validate($commande);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $commandeRepository->save($commande, true);

        return $this->json($commande, Response::HTTP_CREATED, [], [
            'groups' => ['commande.index']
        ]);
    }

    #[Route("/api/commandes/{id}", methods: ['GET'])]
    public function show(string $id, CommandeRepository $commandeRepository): Response
    {
        // Recherche de la commande par le token Firebase
        $commande = $commandeRepository->findOneBy(['token' => $id]);
    
        if (!$commande) {
            return $this->json(['error' => 'Commande non trouvée'], Response::HTTP_NOT_FOUND);
        }
    
        return $this->json($commande, 200, [], [
            'groups' => ['commande.index']
        ]);
    }
    
    
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