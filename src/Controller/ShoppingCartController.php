<?php

namespace App\Controller;

use App\Entity\Price;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class ShoppingCartController extends AbstractController
{
    #[Route('user/shopping/cart', name: 'app_shopping_cart')]    
    /**
     * Récupère tous les élements du panier enregistrés dans la session 'cart'
     * Bien identifier les produits (prix en fonction de l'utilisateur, volume) et leurs quantités
     *
     * @param  mixed $session
     * @param  mixed $entityManager
     * @return Response
     */
    public function index(SessionInterface $session, EntityManagerInterface $entityManager): Response
    {
        $cart = $session->get('cart', []);
        

        $cartData = [];
        foreach ($cart as $id => $quantity) {
            $IDs = explode("_", $id);
            $cartData[] = [
                'price' => $entityManager->getRepository(Price::class)->findOneBy([
                    'product' => $IDs[0],
                    'user' => $this->getUser()->getId(),
                    'volume' => $IDs[1]
                ]),
                'quantity' => $quantity,
                'compositeId' => $id
            ];
        }
       
        $total = 0;
        foreach ($cartData as $item) {
            $total += $item['price']->getPrice() * $item['quantity'];
        }
  
        return $this->render('shopping_cart/index.html.twig', [
            'items' => $cartData,
            'total' => $total

        ]);
    }

    #[Route('user/shopping/cart/add/{id}', name: 'app_shopping_cart_add')]    
    /**
     * Ajouter unproduit au panier 'cart'
     * Création d'un ID unique lors de l'ajouter de produit au panier 
     * L'ID unique se compose de l'ID produit + l'ID volume
     *
     * @param  mixed $id
     * @param  mixed $session
     * @param  mixed $request
     * @param  mixed $entityManager
     * @return Response
     */
    public function addToCart($id, SessionInterface $session, Request $request, EntityManagerInterface $entityManager): Response
    {
        $volumeId = $request->request->get('volume-id');
        $hasPrice = false;
        if ($request->request->get('volume-id') != -1) {

            $hasPrice = $entityManager->getRepository(Price::class)->findOneBy(['product' => $id, 'user' => $this->getUser()->getId(), 'volume' => $volumeId]) != [] ? true : false;
        }
        $isAvailable = $entityManager->getRepository(Product::class)->findOneBy(['id' => $id])->isIsAvailable();
    
        if ($request->isMethod('post') && $request->request->get('volume-id') != -1 && $request->request->get('quantityDesired') > 0 && $hasPrice && $isAvailable) {

            $quantityProducts = $request->request->get('quantityDesired');

        } else {
            $this->addFlash('danger', 'Veuillez verifier la disponibilité du produit ⛔ et ensuite selectionner un volume ⛔ et de verifier si vous avez un prix attribué ⛔');
            return $this->redirectToRoute('app_product_show', ['id' => $id], Response::HTTP_SEE_OTHER);

        }


        $idUniq = $id . '_' . $volumeId;
     
        $cart = $session->get('cart', []);

        if (!empty($cart[$idUniq])) {

            $cart[$idUniq] = $cart[$idUniq] + $quantityProducts;
        } else {

            $cart[$idUniq] = $quantityProducts;
        }
        $session->set('cart', $cart);

      
        return $this->redirectToRoute('app_shopping_cart');
    }

    #[Route('user/shopping/cart/remove/{id}', name: 'app_shopping_cart_remove')]    
    /**
     * Supprimer un élement du panier 'cart'
     *
     * @param  mixed $id
     * @param  mixed $session
     * @param  mixed $request
     * @param  mixed $entityManager
     * @return Response
     */
    public function removeFromCart($id, SessionInterface $session, Request $request, EntityManagerInterface $entityManager): Response
    {
        $cart = $session->get('cart', []);
        if (!empty($cart[$id])) {
            unset($cart[$id]);
        }
        $session->set('cart', $cart);
        return $this->redirectToRoute('app_shopping_cart');
    }



    #[Route('user/shopping/cart/modify/{id}/{qty}', name: 'app_shopping_cart_modify_qty',methods: ['GET','POST'])]    
    /**
     * Modifier la quantité d'un élement du panier 'cart'
     *
     * @param  mixed $id
     * @param  mixed $qty
     * @param  mixed $session
     * @param  mixed $request
     * @param  mixed $entityManager
     * @return JsonResponse
     */
    public function modifyQtyCart($id,$qty,SessionInterface $session, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
    
        
        $cart = $session->get('cart', []);

        if (($cart[$id]) != $qty ) {

            $cart[$id] = (int)$qty ;
        } 
        $session->set('cart', $cart);

       
        return new JsonResponse($qty,200);
    }
}