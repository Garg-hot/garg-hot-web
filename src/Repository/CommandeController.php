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
        $commandes = $commandeRepository->findAll();
        return $this->json($commandes, 200, [], [
            'groups' => ['commande.index']
        ]);
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
        CommandeRepository $commandeRepository
    ): Response {
        $data = json_decode($request->getContent(), true);
        
        // Validation et création de la commande
        $commande = new Commande();
        $commande->setIdUtilisateur($data['idUtilisateur']);
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

    #[Route("/api/commandes/{id}", methods: ['PUT'])]
    public function update(Request $request, Commande $commande, CommandeRepository $commandeRepository): Response
    {
        $data = json_decode($request->getContent(), true);
        
        // Mise à jour des champs de la commande
        if (isset($data['idUtilisateur'])) {
            $commande->setIdUtilisateur($data['idUtilisateur']);
        }
        if (isset($data['statut'])) {
            $commande->setStatut($data['statut']);
        }
        if (isset($data['id_client'])) {
            $commande->setIdClient($data['id_client']);
        }

        $commandeRepository->save($commande, true);

        return $this->json($commande, Response::HTTP_OK, [], [
            'groups' => ['commande.index']
        ]);
    }

    #[Route("/api/commandes/{id}", methods: ['DELETE'])]
    public function delete(Commande $commande, CommandeRepository $commandeRepository): Response
    {
        $commandeRepository->remove($commande, true);

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}