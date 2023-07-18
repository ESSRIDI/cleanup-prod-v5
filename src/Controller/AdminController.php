<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Order;
use App\Entity\Product;
use App\Entity\User;
use App\Form\UserFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
       
    /**
     * Contient toute la logique de l'espace administateur
     *
     * @param  mixed $request
     * @param  mixed $userPasswordHasher
     * @param  mixed $entityManager
     * @return Response
     */
    public function index(Request $request,UserPasswordHasherInterface $userPasswordHasher,EntityManagerInterface $entityManager): Response
    {
         $userId = (int) $this->getUser()->getId();

              //parcourir tous toutes les commandes de l'utilisateur
        $allUserOrders = $entityManager->getRepository(Order::class)->findBy([ 'user' => $userId]);

        $isAdmin = in_array('ROLE_ADMIN', $this->getUser()->getRoles(), true) || in_array('ROLE_SUPER_ADMIN', $this->getUser()->getRoles(), true);
        $users= $entityManager->getRepository(User::class)->findAll();
        $products= $entityManager->getRepository(Product::class)->findAll();
        //!! code repeatation !!!
        $customers =  $entityManager->getRepository(User::class)->findAll();


        $userId = (int)$this->getUser()->getId();
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
                
                return $this->redirectToRoute('app_admin');
            }
        return $this->render('admin/index.html.twig', [
            'isAdmin' => $isAdmin,
            'userForm' => $userForm->createView(),
            'customers' =>  $customers,
            'users' => $users,
            'products'=>$products,
             'userOrders'=>$allUserOrders,
             'usersOrders'=>$ordersOfAllUsers,
            'constants'=>$constants,
          
        ]);
    }

    #[Route('/admin/track/{orderId}/{status}', name: 'app_tracking_order', methods: ['GET','POST'])]
    public function validate($orderId,$status, EntityManagerInterface $entityManager, Request $request): JsonResponse
    {
        $order = $entityManager->find(Order::class, $orderId);
        $order->setStatus($status);
        $entityManager->persist($order);
        $entityManager->flush();
        return new JsonResponse('status changed',200);
    }
}
