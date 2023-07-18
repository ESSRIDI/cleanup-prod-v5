<?php

namespace App\Controller;

use App\Entity\Price;
use App\Entity\Product;
use App\Entity\User;
use App\Entity\Volume;
use App\Form\PriceFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/users/price')]
/**
 * lister tous les prix et les utilisateurs
 */
class PriceController extends AbstractController
{
    #[Route('/', name: 'app_price')]

    public function index(EntityManagerInterface $entityManager): Response
    {
        $users = $entityManager->getRepository(User::class)->findAll();
        $products = $entityManager->getRepository(Product::class)->findAll();
        
        return $this->render('price/index.html.twig', [
            'users' => $users,
            'products' => $products
        ]);
    }





    #[Route(path: '/new', name: 'app_price_new', methods: ['GET', 'POST'])]    
    /**
     * Attribuer un nouveau prix en fonction client & produit & volume
     *
     * @param  mixed $request
     * @param  mixed $entityManager
     * @return Response
     */
    public function newPrice(Request $request, EntityManagerInterface $entityManager): Response
    {




        $price = new Price();
        $priceForm = $this->createForm(PriceFormType::class, $price);
      
        $priceForm->handleRequest($request);

        if ($priceForm->isSubmitted() && $priceForm->isValid()) {


           
            $userId = $priceForm->get('user')->getData()->getId();
            $productId = $priceForm->get('product')->getData()->getId();
            $volumeId = $priceForm->get('volume')->getData()->getId();
            $hasPrice = $entityManager->getRepository(Price::class)->findBy(['product' => $productId, 'user' => $userId, 'volume' => $volumeId]);
            //si un prix trouvé qui corrrespond aux selections on fait une redirect vers la page edit price
            if (!empty($hasPrice)) {
                return $this->redirectToRoute('app_price_edit', ['id' => $hasPrice[0]->getId()]);
                // $price->setId($hasPrice[0]->getId());

            }

            
            $entityManager->persist($price);
         
            $entityManager->flush();

            $this->addFlash('success', 'Votre prix vient d\'être ajouté');
            // return $this->redirectToRoute('app_product_list', [], Response::HTTP_SEE_OTHER);
            if (in_array('ROLE_SUPER_ADMIN', $this->getUser()->getRoles(), true)) {
                return $this->redirectToRoute('app_super_admin');
            }
            if (in_array('ROLE_ADMIN', $this->getUser()->getRoles(), true)) {
                return $this->redirectToRoute('app_admin');
            }
        }
        return $this->render('price/new-edit.html.twig', [
            'title' => 'Ajouter un prix',
            'priceForm' => $priceForm->createView(),

        ]);
    }



    #[Route(path: '/edit/{id}', name: 'app_price_edit', methods: ['GET', 'POST'])]    
    /**
     * Modifier prix en fonction client & produit & volume
     *
     * @param  mixed $id
     * @param  mixed $request
     * @param  mixed $entityManager
     * @return Response
     */
    public function editPrice($id, Request $request, EntityManagerInterface $entityManager): Response
    {




        $price = $entityManager->find(Price::class, $id);
        $priceForm = $this->createForm(PriceFormType::class, $price);
    
        $priceForm->handleRequest($request);

        if ($priceForm->isSubmitted() && $priceForm->isValid()) {

         
            $entityManager->persist($price);

            $entityManager->flush();
            $this->addFlash('success', 'Votre prix vient d\'être modifié');
            // return $this->redirectToRoute('app_product_list', [], Response::HTTP_SEE_OTHER);
            if (in_array('ROLE_SUPER_ADMIN', $this->getUser()->getRoles(), true)) {
                return $this->redirectToRoute('app_super_admin');
            }
            if (in_array('ROLE_ADMIN', $this->getUser()->getRoles(), true)) {
                return $this->redirectToRoute('app_admin');
            }
        }
        return $this->render('price/new-edit.html.twig', [
            'title' => 'Editer le prix',
            'priceForm' => $priceForm->createView(),

        ]);
    }

    #[Route(path: '/get-list-volumes-of-product/{productId}', name: 'list_volumes-of-product', methods: ['GET'])]    
    /**
     * Renvoie sous forme JSON les volumes associés à un produit
     *
     * @param  mixed $productId
     * @param  mixed $request
     * @param  mixed $entityManager
     * @return JsonResponse
     */
    public function listVolumesOfProduct($productId, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $product = $entityManager->getRepository(Product::class)->findOneBy(['id' => $productId]);
        $volumes = $product->getVolumes();
        $volumesArray = [];
        foreach ($volumes as $volume) {
            $volumesArray[] = [
                "id" => $volume->getId(),
                "volume" => $volume->getVolume().' '.$product->getUnity()
            ]
            ;
        }
        return new JsonResponse($volumesArray);
    }


    


}