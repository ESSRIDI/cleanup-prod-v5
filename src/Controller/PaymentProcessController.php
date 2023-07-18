<?php

namespace App\Controller;

use Stripe\Stripe;
use Stripe\StripeClient;

use App\Entity\Order;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PaymentProcessController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager, private UrlGeneratorInterface $generatorUrl)
    {
      $this->entityManager = $entityManager;
      $this->generatorUrl = $generatorUrl;
  
    }
    #[Route('user/order/create-stripe-session/{reference}', name: 'app_payment_process_for_stripe')]    
    /**
     * Créér la session Stripe et procéder au paiement
     *
     * @param  mixed $reference
     * @return RedirectResponse
     */
    public function stripeCheckout($reference): RedirectResponse
    {
        $productForStripe = [];
        $order = $this->entityManager->getRepository(Order::class)->findOneBy(['reference' => $reference]);
        if (!$order) {
            return $this->redirectToRoute('app_shopping_cart');
          }
          foreach ($order->getOrderItem()->getValues() as $product) {
       
            $productData = $this->entityManager->getRepository(Product::class)->findOneBy(['name' => $product->getProductName()]);
            $productStripe[] = [
                'price_data' => [
                  'currency' => 'eur',
                  'unit_amount' => $product->getProductPriceWithTva() * 100  ,
                  'product_data' => [
                    'name' => $product->getProductName().' | VOL.'. $product->getProductVolume().$productData->getUnity().' | '.$productData->getScent().' | '.$productData->getQuality()
                    
                    
        
                  ],
               
                ],
                'quantity'=>$product->getProductQuantity()
              ];
            }
            $productStripe[] = [
                'price_data' => [
                  'currency' => 'eur',
                  'unit_amount' => $order->getTransporterPrice()*100,
                  'product_data' => [
                    'name' => 'LIVRAISON : '.$order->getTransporterName(),
          
                  ],
                ],
                  'quantity' => 1,
              ];
             
              Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);
              $checkout_session = \Stripe\Checkout\Session::create([
                'customer_email' =>$this->getUser()->getEmail(),
                'payment_method_types'=>['card'],
                'line_items' => [
                  [
                
                    $productStripe
                  ]
                ],
                'automatic_tax' => [
                  'enabled' => true,
                ],
                'mode' => 'payment',
                'success_url' => $this->generatorUrl->generate('payment_success', ['reference' =>$order->getReference()], UrlGeneratorInterface::ABSOLUTE_URL),
                'cancel_url' =>  $this->generatorUrl->generate('payment_error',['reference' => $order->getReference()], UrlGeneratorInterface::ABSOLUTE_URL),
              ]);
          
              $order->setStripeSessionId( $checkout_session->id);
              $this->entityManager->flush();
        return new RedirectResponse($checkout_session->url );
      }

      #[Route('user/order/success/{reference}', name: 'payment_success')]      
      /**
       * Redirection en cas de succès
       *
       * @param  mixed $reference
       * @param  mixed $session
       * @return Response
       */
      public function stripeSuccess($reference, SessionInterface $session): Response
      {
        $session->set('cart', []);
        $order = $this->entityManager->getRepository(Order::class)->findOneBy(['reference' => $reference]);
         $order->setStatus(Order::SAVED);
        $order->setIsPaid(1);
        $this->entityManager->persist($order);
         $this->entityManager->flush();
        return $this->render('order/success.html.twig');
      }
    
      #[Route('user/order/error/{reference}', name: 'payment_error')]      
      /**
       * Redirection en cas d'échec
       *
       * @param  mixed $reference
       * @return Response
       */
      public function stripeError($reference): Response
      {
        $order = $this->entityManager->getRepository(Order::class)->findOneBy(['reference' => $reference]);
        $order->setStatus(Order::WAITING_FOR_PAYMENT);
        $this->entityManager->persist($order);
         $this->entityManager->flush();
        return $this->render('order/error.html.twig');
      }
}
