<?php

namespace App\Controller\API;

use App\Entity\Prix;
use App\Repository\PrixRepository;
use App\Repository\PlatRepository;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PrixController extends AbstractController
{
    #[Route("/api/prix/", methods: ['GET'])]
    public function index(PrixRepository $prixRepository): Response
    {
        $prix = $prixRepository->findAll();
        return $this->json($prix, 200, [], [
            'groups' => ['prix.index','plat.index']
        ]);
    }

    #[Route("/api/prix/{id}", requirements: ['id' => Requirement::DIGITS], methods: ['GET'])]
    public function show(Prix $prix): Response
    {
        return $this->json($prix, 200, [], [
            'groups' => ['prix.index']
        ]);
    }

    #[Route("/api/prix/", methods: ['POST'])]
public function create(
    Request $request,
    SerializerInterface $serializer,
    ValidatorInterface $validator,
    EntityManagerInterface $entityManager,
    PlatRepository $platRepository
): Response {
    try {
        // Décoder le contenu JSON de la requête
        $data = json_decode($request->getContent(), true);

        // Vérifier si les champs requis sont présents
        if (!isset($data['plat']['id']) || !isset($data['montant'])) {
            return $this->json([
                'error' => 'Les champs platId et montant sont requis'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Récupérer le plat par son ID
        $plat = $platRepository->find($data['plat']['id']);
        if (!$plat) {
            return $this->json([
                'error' => 'Plat non trouvé'
            ], Response::HTTP_NOT_FOUND);
        }

        // Créer un nouvel objet Prix
        $prix = new Prix();
        $prix->setMontant($data['montant']);
        $prix->setPlat($plat);

        // Valider les données
        $errors = $validator->validate($prix);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return $this->json([
                'errors' => $errorMessages
            ], Response::HTTP_BAD_REQUEST);
        }

        // Persister le nouvel objet Prix
        $entityManager->persist($prix);
        $entityManager->flush();

        return $this->json($prix, Response::HTTP_CREATED, [], [
            'groups' => ['prix.index']
        ]);
    } catch (\Exception $e) {
        return $this->json([
            'error' => $e->getMessage()
        ], Response::HTTP_BAD_REQUEST);
    }
}

    #[Route("/api/prix/{id}", requirements: ['id' => Requirement::DIGITS], methods: ['PUT'])]
    public function edit(
        int $id,
        Request $request,
        PrixRepository $prixRepository,
        ValidatorInterface $validator,
        EntityManagerInterface $entityManager
    ): Response {
        try {
            $prix = $prixRepository->find($id);
            if (!$prix) {
                return $this->json([
                    'error' => 'Prix non trouvé'
                ], Response::HTTP_NOT_FOUND);
            }

            $data = json_decode($request->getContent(), true);

            if (isset($data['montant'])) {
                $prix->setMontant($data['montant']);
            }
            
            $prix->setDatePrix(new \DateTime()); // Mise à jour de la date lors de la modification

            // Valider les données
            $errors = $validator->validate($prix);
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

            return $this->json($prix, Response::HTTP_OK, [], [
                'groups' => ['prix.index']
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route("/api/prix/{id}", requirements: ['id' => Requirement::DIGITS], methods: ['DELETE'])]
    public function delete(
        int $id,
        PrixRepository $prixRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $prix = $prixRepository->find($id);
        if (!$prix) {
            return $this->json([
                'error' => 'Prix non trouvé'
            ], Response::HTTP_NOT_FOUND);
        }

        try {
            $entityManager->remove($prix);
            $entityManager->flush();

            return $this->json(null, Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Impossible de supprimer ce prix car il est utilisé par des plats'
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route("/api/prix/plat/{platId}", requirements: ['platId' => Requirement::DIGITS], methods: ['GET'])]
    public function getLatestPrixForPlat(
        int $platId,
        PrixRepository $prixRepository,
        PlatRepository $platRepository
    ): Response {
        // Vérifier si le plat existe
        $plat = $platRepository->find($platId);
        if (!$plat) {
            return $this->json([
                'error' => 'Plat non trouvé'
            ], Response::HTTP_NOT_FOUND);
        }

        // Récupérer le dernier prix
        $prix = $prixRepository->findLatestPrixForPlat($platId);
        
        if (!$prix) {
            return $this->json([
                'error' => 'Aucun prix trouvé pour ce plat'
            ], Response::HTTP_NOT_FOUND);
        }

        return $this->json($prix, 200, [], [
            'groups' => ['prix.index']
        ]);
    }
}
