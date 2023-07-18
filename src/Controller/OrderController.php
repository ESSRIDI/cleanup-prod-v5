<?php

namespace App\Controller;

use App\Entity\Address;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Price;
use App\Entity\Product;
use App\Entity\User;
use App\Entity\Volume;
use App\Form\OrderFormType;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Collection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class OrderController extends AbstractController
{
    #[Route('user/order', name: 'app_order')]
    /**
     * Afficher de le contenu du panier et les informations complémentaires pour établir la commande 
     *
     * @param  mixed $request
     * @param  mixed $session
     * @param  mixed $entityManager
     * @return Response
     */
    public function index(Request $request, SessionInterface $session, EntityManagerInterface $entityManager): Response
    {

        $cart = $session->get('cart', []);
        // dd($cart);

        $deliveryForm = $this->createForm(OrderFormType::class, null, ['user' => $this->getUser()]);

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
        //calcul du prix TTC
        $tva = $total * 20 / 100;
        $total += $tva;
        return $this->render('order/index.html.twig', [
            'deliveryForm' => $deliveryForm->createView(),
            'items' => $cartData,
            'total' => $total,
            'tva' => $tva
        ]);

    }

    #[Route('user/order/verify', name: 'order_prepare', methods: ['POST'])]
    /**
     * Persister toutes les infomations liées à la commande dans la base de donnée
     *
     * @param  mixed $request
     * @param  mixed $session
     * @param  mixed $entityManager
     * @return Response
     */
    public function prepareOrder(Request $request, SessionInterface $session, EntityManagerInterface $entityManager): Response
    {
        $hasAddress = $this->getUser()->getAddresses()[0];


        if ($hasAddress == null) {
            $this->addFlash('danger', 'Vous devez tout d\'abord entrer une adresse');
            return $this->redirectToRoute('app_address');
        }
        $deliveryForm = $this->createForm(OrderFormType::class, null, ['user' => $this->getUser()]);
        $deliveryForm->handleRequest($request);

        if ($deliveryForm->isSubmitted() && $deliveryForm->isValid()) {

            $transporter = $deliveryForm->get('transporter')->getData();
            $delivery = $deliveryForm->get('adresses')->getData();

            $order = new Order();
            $reference = strtoupper(substr($this->getUser()->getLastName(), 0, 2)) . strtoupper(substr($this->getUser()->getFirstName(), 0, 1)) . '-' . uniqid("CLN");
            $order->setReference($reference);
            $order->setTva(Order::TVA);
            $order->setStatus(Order::SAVED);
            $order->setUser($this->getUser());
            $order->setCreatedAt(new \DateTime('now'));
            $order->setDeliveryAddress($delivery);
            $order->setTransporterName($transporter->getCompanyName());
            $order->setTransporterPrice($transporter->getPrice());
            $order->setIsPaid(0);
            $entityManager->persist($order);


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
            foreach ($cartData as $item) {
                $compositeId = $item['compositeId'];
                $IDs = explode("_", $compositeId);
                $product = $entityManager->getRepository(Product::class)->findOneBy(['id' => $IDs[0]]);
                $volume = $entityManager->getRepository(Volume::class)->findOneBy(['id' => $IDs[1]]);

                $orderItem = new OrderItem();
                $orderItem->setOrderClient($order);
                $orderItem->setProductImage($product->getImage());
                $orderItem->setProductName($product->getName());
                $orderItem->setProductVolume($volume->getVolume());
                $htPrice = $item['price']->getPrice();
                $orderItem->setProductPrice($item['price']->getPrice());
                $orderItem->setProductPriceWithTva(round(($htPrice + (($htPrice * 20) / 100)), 2));
                $orderItem->setProductQuantity($item['quantity']);
                $orderItem->setProductTotalPrice($item['price']->getPrice() * $item['quantity']);
                $entityManager->persist($orderItem);
            }

            $entityManager->flush();
            return $this->redirectToRoute('app_payment_process_for_stripe', ['reference' => $order->getReference()]);
        }

        return $this->redirectToRoute('app_order');
    }


    #[Route('user/order/re/verify/{id}', name: 'order_re_prepare', methods: ['POST', 'GET'])]
    public function rePrepareOrder($id, SessionInterface $session, EntityManagerInterface $entityManager): Response
    {



        $order = $entityManager->getRepository(Order::class)->findOneBy(['id' => $id]);
        $order->setCreatedAt(new \DateTime('now'));





        return $this->redirectToRoute('app_payment_process_for_stripe', ['reference' => $order->getReference()]);
    }


}