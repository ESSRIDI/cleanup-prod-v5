<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class CategoryController extends AbstractController
{
    #[Route('/admin/category', name: 'app_category_list', methods: ['GET'])]    
    /**
     * Afficher toute la liste des catégories
     *
     * @param  mixed $entityManager
     * @return Response
     */
    public function index(EntityManagerInterface $entityManager): Response
    {
        $categories = $entityManager->getRepository(Category::class)->findAll();
        

        $isAdmin = in_array('ROLE_ADMIN', $this->getUser()->getRoles(), true) || in_array('ROLE_SUPER_ADMIN', $this->getUser()->getRoles(), true);

       
        return $this->render('category/index.html.twig', [
            'categories' => $categories,
            'isAdmin' => $isAdmin
        ]);
    }

    #[Route('/admin/category/new', name: 'app_category_new', methods: ['GET', 'POST'])]    
    /**
     * Créer une nouvelle catégorie
     *
     * @param  mixed $request
     * @param  mixed $entityManager
     * @return Response
     */
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $category = new Category();
        $categoryForm = $this->createForm(CategoryFormType::class, $category);
        $categoryForm->handleRequest($request);

        if ($categoryForm->isSubmitted() && $categoryForm->isValid()) {
            // encode the plain password
            $category->setLabel($categoryForm->get('label')->getData());

            $entityManager->persist($category);
            $entityManager->flush();
            $this->addFlash('success', 'Votre catégorie vient d\'être crée');
            return $this->redirectToRoute('app_category_list', [], Response::HTTP_SEE_OTHER);
        }
        return $this->render('category/new.html.twig', [
            'categoryForm' => $categoryForm->createView(),

        ]);

    }


    #[Route('/admin/category/edit/{id}', name: 'app_category_edit', methods: ['GET', 'POST'])]    
    /**
     * Modifier une catégorie par son ID
     *
     * @param  mixed $id
     * @param  mixed $request
     * @param  mixed $entityManager
     * @return Response
     */
    public function edit($id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $category = $entityManager->find(Category::class, $id);
        // $category = new Category();
        $categoryForm = $this->createForm(CategoryFormType::class, $category);
        $categoryForm->handleRequest($request);

        if ($categoryForm->isSubmitted() && $categoryForm->isValid()) {
            // encode the plain password
            $category->setLabel($categoryForm->get('label')->getData());

            $entityManager->persist($category);
            $entityManager->flush();
            $this->addFlash('success', 'Votre catégorie vient d\'être modifé');
            return $this->redirectToRoute('app_category_list', [], Response::HTTP_SEE_OTHER);
        }
        return $this->render('category/edit.html.twig', [
            'categoryForm' => $categoryForm->createView(),

        ]);

    }


    #[Route('/admin/category/delete/{id}', name: 'app_category_delete', methods: ['POST', 'DELETE'])]    
    /**
     * Supprimer une catégorie par son ID
     *
     * @param  mixed $id
     * @param  mixed $entityManager
     * @param  mixed $request
     * @return Response
     */
    public function delete($id, EntityManagerInterface $entityManager, Request $request): Response
    {
        $submittedToken = $request->request->get('token');
        if ($this->isCsrfTokenValid('delete-item', $submittedToken)) {
            $category = $entityManager->find(Category::class, $id);

            $entityManager->remove($category);
            $entityManager->flush();
            $this->addFlash('notice', 'Votre catégorie vient d\'être supprimée');
            return $this->redirectToRoute('app_category_list');
        } else {
            $this->addFlash('danger', 'Vous n\'êtes pas autorisé a supprimer la catégorie');
            return $this->redirectToRoute('app_category_list');

        }

    }
}