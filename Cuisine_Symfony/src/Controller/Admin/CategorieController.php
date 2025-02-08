<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;

use App\Repository\CategorieRepository;
use App\Entity\Categorie;
use App\Form\CategorieType;

#[Route("/admin/categorie", name: 'admin.categorie.')]
class CategorieController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(CategorieRepository $categorieRepository, Request $request)
    {
        $page = $request->query->getInt('page', 1);
        $categories = $categorieRepository->paginateCategories($page);
        //dd($categories->count());
        return $this->render('admin/categorie/index.html.twig',[
            // 'categories' => $categorieRepository->findAll()
            'categories' => $categories
        ]);
    }
    #[Route('/create', name: 'create')]
    public function create(Request $request, EntityManagerInterface $entityManager)
    {
        $categorie = new Categorie();
        $form = $this->createForm(CategorieType::class, $categorie);
        $form ->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $entityManager->persist($categorie);
            $entityManager->flush();
            $this->addFlash('success', 'La categorie a été ajoutée avec succès');
            return $this->redirectToRoute('admin.categorie.index');
        }
        return $this->render('admin/categorie/create.html.twig',[
            'form' => $form
        ]);
    }
    #[Route('/edit/{id}', name: 'edit', requirements: ['id' => Requirement::DIGITS])]
    public function edit(Categorie $categorie, Request $request, EntityManagerInterface $entityManager)
    {
        $form = $this->createForm(CategorieType::class, $categorie);
        $form ->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            // dd($categorie);
            $entityManager->flush();
            $this->addFlash('success', 'La categorie a été modifier avec succès');
            return $this->redirectToRoute('admin.categorie.index');
        }
        return $this->render('admin/categorie/edit.html.twig',[
            'categorie' => $categorie,
            'form' => $form
        ]);
    }
    #[Route('/delete/{id}', name: 'delete', requirements: ['id' => Requirement::DIGITS])]
    public function delete(Categorie $categorie, EntityManagerInterface $entityManager)
    {
        $entityManager->remove($categorie);
        $entityManager->flush();
        $this->addFlash('success', 'La categorie a été supprimer avec succès');
        return $this->redirectToRoute('admin.categorie.index');
    }
    
}