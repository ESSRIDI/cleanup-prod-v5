<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\Product;
use App\Entity\User;
use App\Form\UserFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
/**
 * Contient toute la logique de l'espace super administateur
 */
class SuperAdminController extends AbstractController
{
    #[Route('/users', name: 'app_super_admin')]
    public function index(Request $request,UserPasswordHasherInterface $userPasswordHasher,EntityManagerInterface $entityManager): Response
    {
        $isAdmin = in_array('ROLE_ADMIN', $this->getUser()->getRoles(), true) || in_array('ROLE_SUPER_ADMIN', $this->getUser()->getRoles(), true);


        $users= $entityManager->getRepository(User::class)->findAll();
        $products= $entityManager->getRepository(Product::class)->findAll();
        $customers =  $entityManager->getRepository(User::class)->findAll();

        $userId = (int)$this->getUser()->getId();
        //parcourir tous toutes les commandes de l'utilisateur
        $allUserOrders = $entityManager->getRepository(Order::class)->findBy([ 'user' => $userId]);
        $ordersOfAllUsers=$entityManager->getRepository(Order::class)->findAll();
        
        $orderClass = new \ReflectionClass('App\Entity\order');
        $constants = $orderClass->getConstants();
        array_shift($constants) ;
       
        $user= $entityManager->find(User::class, $userId);
             $userForm= $this->createForm(UserFormType::class,$user);
             $userForm->handleRequest($request);
             if ($userForm->isSubmitted() && $userForm->isValid()) {
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $userForm->get('password')->getData()
                    )
                );
                $entityManager->persist($user);
                $entityManager->flush();
                $this->addFlash('success', 'Vos coordonnées viennent d\'être modifié');
                
                return $this->redirectToRoute('app_super_admin');
            }
        return $this->render('super_admin/index.html.twig', [
            'isAdmin' => $isAdmin,
            'userForm' => $userForm->createView(),
            'customers' =>  $customers,
                 'users' => $users,
            'products'=>$products,
            'userOrders'=>$allUserOrders,
            'usersOrders'=>$ordersOfAllUsers,
            'constants'=>$constants
          
        ]);
    }


    #[Route('/users/delete/{id}', name: 'app_user_delete', methods: ['POST', 'DELETE'])]
    /**
     * Supprimer un user par son ID.
     *
     * @param  mixed $id
     * @param  mixed $entityManager
     * @param  mixed $request
     * @return RedirectResponse
     */
    public function delete($id, EntityManagerInterface $entityManager, Request $request): RedirectResponse
    {
        $submittedToken = $request->request->get('token');

        if ($this->isCsrfTokenValid('delete-item', $submittedToken)) {

            $user = $entityManager->find(User::class, $id);

            $entityManager->remove($user);
            $entityManager->flush();
            $this->addFlash('notice', 'l\'utilisateur vient d\'être supprimée');
            return $this->redirectToRoute('app_super_admin');
        } 
    }

    #[Route('/users/validate/{userId}/{decision}', name: 'app_validate_user', methods: ['GET','POST'])]
    public function validate($userId,$decision, EntityManagerInterface $entityManager, Request $request): JsonResponse
    {
        $user = $entityManager->find(User::class, $userId);
        $user->setIsProfessional($decision);
        $entityManager->persist($user);
        $entityManager->flush();
        return new JsonResponse('approuved',200);
    }


}
