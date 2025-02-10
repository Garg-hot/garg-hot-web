<?php

namespace App\Controller\API;

use App\Entity\Vente;
use App\Repository\VenteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Doctrine\ORM\EntityManagerInterface;



class VenteController extends AbstractController
{
    #[Route("/api/vente/", methods: ['GET'])]
    public function index(VenteRepository $venteRepository, SerializerInterface $serializer)
    {
        $ventes = $venteRepository->findAll();
        return $this->json($ventes, 200, [], [
            'groups' => ['vente.index']
        ]);
    }

    #[Route("/api/vente/{id}", methods: ['GET'])]
    public function show(Vente $vente)
    {
        return $this->json($vente, 200, [], [
            'groups' => ['vente.index', 'vente.show']
        ]);
    }

    #[Route("/api/vente/", methods: ['POST'])]
    public function create(Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManager)
    {
        $vente = $serializer->deserialize($request->getContent(), Vente::class, 'json');
        $vente->setCreatedAt(new \DateTimeImmutable()); // Assurez-vous de définir la date de création
        $entityManager->persist($vente);
        $entityManager->flush();

        return $this->json($vente, Response::HTTP_CREATED, [], ['groups' => ['vente.show']]);
    }

    #[Route("/api/vente/{id}", methods: ['PUT'])]
    public function update(Request $request, Vente $vente, SerializerInterface $serializer, EntityManagerInterface $entityManager)
    {
        $serializer->deserialize($request->getContent(), Vente::class, 'json', ['object_to_populate' => $vente]);
        $entityManager->flush();

        return $this->json($vente, Response::HTTP_OK, [], ['groups' => ['vente.show']]);
    }

    #[Route("/api/vente/{id}", methods: ['DELETE'])]
    public function delete(Vente $vente, EntityManagerInterface $entityManager)
    {
        $entityManager->remove($vente);
        $entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}