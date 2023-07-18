<?php

namespace App\Controller;

use App\Entity\Address;
use App\Entity\Order;
use App\Entity\User;
use App\Form\AddressFormType;
use App\Form\UserFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/user')]
class UserController extends AbstractController
{
    #[Route('/', name: 'app_user')]    
    /**
     * Contient toute la logique de l'espace utilisateur
     *
     * @param  mixed $request
     * @param  mixed $userPasswordHasher
     * @param  mixed $entityManager
     * @return Response
     */
    public function index(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $userId = (int) $this->getUser()->getId();


        
        //parcourir tous toutes les commandes de l'utilisateur
        $allUserOrders = $entityManager->getRepository(Order::class)->findBy([ 'user' => $userId]);

        
        $user = $entityManager->find(User::class, $userId);
        $userForm = $this->createForm(UserFormType::class, $user);
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

            return $this->redirectToRoute('app_user');
        }
        return $this->render('user/index.html.twig', [
            'userForm' => $userForm->createView(),
            'userOrders'=>$allUserOrders,
          

        ]);
    }
    #[Route('/order/details/{id}', name: 'app_user_order_details')]
    public function detailOrder($id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $hasSuperAdminRole = in_array('ROLE_SUPER_ADMIN', $this->getUser()->getRoles(), true);
        $hasAdminRole = in_array('ROLE_ADMIN', $this->getUser()->getRoles(), true);
        $hasUserRole = in_array('ROLE_USER', $this->getUser()->getRoles(), true);

        $order = $entityManager->getRepository(Order::class)->findBy([ 'id' => $id]);
        $items= $order[0]->getOrderItem();
        $total= 0;
        foreach ($items as $item) {
            $total += $item->getProductTotalPrice() ;
        }
        $tva= $total * Order::TVA / 100;
        $total += $tva;
        return $this->render('user/detail_order.html.twig', [
            'items' => $items,
            'order' =>$order,
            'total' =>$total,
            'tva' =>$tva,
            'hasSuperAdminRole' =>$hasSuperAdminRole,
            'hasAdminRole'=>$hasAdminRole,
            'hasUserRole'=>$hasUserRole
            

        ]);
    }

    #[Route('/address', name: 'app_address')]
    public function address(Request $request, EntityManagerInterface $entityManager): Response
    {

        $address = new Address;
        $addressForm = $this->createForm(AddressFormType::class, $address);
        $addressForm->handleRequest($request);
        if ($addressForm->isSubmitted() && $addressForm->isValid()) {


            // Eviter la duplication des address a faire ? 
            $address = $addressForm->getData();

            $address->addUser($this->getUser());


            $entityManager->persist($address);
            $entityManager->flush();
            $this->addFlash('success', 'Votre address vient d\'être ajoutée');
            return $this->redirectToRoute('app_user');
        }
        return $this->render('user/address.html.twig', [
            'addressForm' => $addressForm,
        ]);
    }

    #[Route('/delete/{id}', name: 'app_address_delete', methods: ['GET', 'DELETE'])]
    public function delete($id, EntityManagerInterface $entityManager): Response
    {
        $address = $entityManager->find(Address::class, $id);
       
        $entityManager->remove($address);
        $entityManager->flush();
        $this->addFlash('notice', 'Votre address vient d\'être supprimée');
        return $this->redirectToRoute('app_user');
    }



}