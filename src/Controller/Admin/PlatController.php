<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Requirement\Requirement;

use App\Repository\PlatRepository;
use App\Entity\Plat;
use App\Form\PlatType;
use App\Repository\CategorieRepository;

#[Route('/admin/plat', name: 'admin.plat.')]
final class PlatController extends AbstractController
{
        public function __construct(private PlatRepository $repository)
        {
            
        }
        #[Route('/', name: 'index')]
        public function index( EntityManagerInterface $entityManager, CategorieRepository $categorieRepository): Response
        {   
            
            $plats = $this->repository->findAll();
            $minutes_max = 20;
            $plats_10min = $this->repository->findDurationLowerThan($minutes_max);
            return $this->render('admin/plat/index.html.twig',[
                'plats' => $plats,
                'plats_10min' => $plats_10min,
                'minute_max' => $minutes_max
           ]);    
        }
        #[Route('/create', name:'create')]
        public function create(Request $request, EntityManagerInterface $entityManager){
            $plat = new Plat();
            $plat2= new Plat();
            $form= $this->createForm(PlatType::class, $plat);
            $form->handleRequest($request);
            if($form->isSubmitted() && $form->isValid()){
                $entityManager->persist($plat);
                $entityManager->flush();
                $this->addFlash('success', 'Le plat a été ajouté avec succès');
                return $this->redirectToRoute('admin.plat.index');
            }
            return $this->render('admin/plat/create.html.twig', [
                'form' => $form
            ]);
        }
        #[Route('/edit/{id}', name:'edit',requirements: ['id' => Requirement::DIGITS])]
        public function edit(Plat $plat, Request $request,EntityManagerInterface $entityManager){
            //dd($plat);
            $form= $this->createForm(PlatType::class, $plat);
            $form->handleRequest($request);
            if($form->isSubmitted() && $form->isValid()){
                $entityManager->flush();
                $this->addFlash('success', 'Le plat a été modifié avec succès');
                return $this->redirectToRoute('admin.plat.index');
            }
            return $this->render('admin/plat/edit.html.twig', [
                'plat' => $plat,
                'form' => $form
            ]);
        }
        #[Route('/delete/{id}', name:'delete', requirements: ['id' => Requirement::DIGITS])]
        public function delete(Plat $plat, EntityManagerInterface $entityManager){
            $entityManager->remove($plat);
            $entityManager->flush();
            $this->addFlash('success', 'Le plat a été supprimée avec succès');
            return $this->redirectToRoute('admin.plat.index');
        }
        #[Route('/{slug}-{id}', name: 'show', requirements: ['id' => '\d+', 'slug' => '[a-z0-9\-]+'])]
        public function show(Request $request, string $slug, int $id): Response
        {
            $plat = $this->repository->find($id);
            if ($plat -> getSlug() !== $slug) {
                return $this->redirectToRoute('admin.plat.show', [
                    'slug' => $plat -> getSlug(),
                    'id' => $plat -> getId()
                ]);
            }
            // dd($plat);
            return $this->render('admin/plat/show.html.twig', [
                'plat' => $plat
            ]);
        }
}
