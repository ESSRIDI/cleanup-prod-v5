<?php

namespace App\Controller;

use App\Entity\Category;

use App\Entity\Price;
use App\Entity\Product;
use App\Entity\Volume;

use App\Form\ProductFormType;
use App\Form\VolumeFormType;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;


class ProductController extends AbstractController
{

    #[Route(path: '/products', name: 'app_product_list', methods: ['GET'])]
    /**
     * Retourner la liste totale des produits
     *
     * @param  mixed $entityManager
     * @return Response
     */
    public function index(EntityManagerInterface $entityManager): Response
    {
        $products = $entityManager->getRepository(Product::class)->findAll();
        $categories = $entityManager->getRepository(Category::class)->findAll();
     
 
        
        if ($this->getUser()) {
            $isAdmin = in_array('ROLE_ADMIN', $this->getUser()->getRoles(), true) || in_array('ROLE_SUPER_ADMIN', $this->getUser()->getRoles(), true);
            $isUser = in_array('ROLE_USER', $this->getUser()->getRoles());
        } else {
            $isAdmin = null;
            $isUser = null;
        }

        return $this->render('product/index.html.twig', [
            'products' => $products,
            'categories' => $categories,
            'isAdmin' => $isAdmin,
            'isUser' => $isUser,

        ]);
    }

    #[Route(path: 'product/show/{id}', name: 'app_product_show', methods: ['GET', 'POST'])]
    /**
     * Retourner un produit par son ID
     *
     * @param  mixed $id
     * @param  mixed $entityManager
     * @param  mixed $request
     * @return Response
     */
    public function show($id, EntityManagerInterface $entityManager, Request $request): Response
    {
    
      
        $product = $entityManager->getRepository(Product::class)->findBy(['id' => $id]);
    
        

        return $this->render('product/show.html.twig', [
            'product' => $product,
            
            'id' => $id
        ]);
    }

    #[Route(path: 'admin/product/new', name: 'app_product_new', methods: ['GET', 'POST'])]
    /**
     * Ajouter un nouveau produit.
     *
     * @param  mixed $request
     * @param  mixed $entityManager
     * @return Response
     */
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $product = new Product();
        $productForm = $this->createForm(ProductFormType::class, $product);
        $productForm->handleRequest($request);
        if ($productForm->isSubmitted() && $productForm->isValid()) {
            $newProduct = $productForm->getData();
            $imagePath = $productForm->get('image')->getData();
            if ($imagePath) {
                $newFileName = uniqid() . '.' . $imagePath->guessExtension();
                try {
                    $imagePath->move(
                        $this->getParameter('kernel.project_dir') . '/public/uploads',
                        $newFileName
                    );
                } catch (FileException $err) {

                    return new Response($err->getMessage());
                }
                $newProduct->setImage('/uploads/' . $newFileName);
            } else {
                $newProduct->setImage('/uploads/no_image' . '.' . 'png');
            }
            $barcodePath = $productForm->get('barcode')->getData();
            if ($barcodePath) {
                $newFileName = uniqid() . '.' . $barcodePath->guessExtension();

                try {
                    $barcodePath->move(
                        $this->getParameter('kernel.project_dir') . '/public/uploads',
                        $newFileName
                    );
                } catch (FileException $err) {

                    return new Response($err->getMessage());
                }
                $newProduct->setBarcode('/uploads/' . $newFileName);
            } else {
                $newProduct->setBarcode('/uploads/code-barres' . '.' . 'jpg');
            }

            $entityManager->persist($product);
    
            $entityManager->flush();
            $this->addFlash('success', 'Votre produit vient d\'être crée');
            return $this->redirectToRoute('app_product_list', [], Response::HTTP_SEE_OTHER);
        }
        return $this->render('product/new.html.twig', [
            'productForm' => $productForm->createView(),
        ]);
    }


    #[Route(path: 'admin/product/edit/{id}', name: 'app_product_edit', methods: ['GET', 'POST'])]
    /**
     * Modifier un produit par son ID.
     *
     * @param  mixed $id
     * @param  mixed $request
     * @param  mixed $entityManager
     * @param  mixed $slugger
     * @return Response
     */
    public function edit($id, Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $product = $entityManager->find(Product::class, $id);
        $productForm = $this->createForm(ProductFormType::class, $product);
        $productForm->handleRequest($request);
        if ($productForm->isSubmitted() && $productForm->isValid()) {
            $product->setName($productForm->get('name')->getData());
            $product->setDescription($productForm->get('description')->getData());
            /** @var UploadedFile $imageFile */
            $imageFile = $productForm->get('image')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();
                try {
                    $imageFile->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    return new Response($e->getMessage());
                }
                $product->setImage('/build/images/' . $newFilename);
            }

            $barcodeFile = $productForm->get('barcode')->getData();
            if ($barcodeFile) {
                $originalFilename = pathinfo($barcodeFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $barcodeFile->guessExtension();
                try {
                    $barcodeFile->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    return new Response($e->getMessage());
                }
                $product->setBarcode('/build/images/' . $newFilename);
            }

            $product->setIsAvailable($productForm->get('isAvailable')->getData());
            $product->setCategory($productForm->get('category')->getData());
            $entityManager->persist($product);
            $entityManager->flush();
            $this->addFlash('success', 'Votre produit vient d\'être modifié');
            return $this->redirectToRoute('app_product_list', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('product/edit.html.twig', [
            'productForm' => $productForm->createView(),
            'idProduct' => $id

        ]);
    }

    #[Route('admin/product/delete/{id}', name: 'app_product_delete', methods: ['POST', 'DELETE'])]
    /**
     * Supprimer un produit par son ID.
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

            $product = $entityManager->find(Product::class, $id);

            $entityManager->remove($product);
            $entityManager->flush();
            $this->addFlash('notice', 'Votre produit vient d\'être supprimée');
            return $this->redirectToRoute('app_product_list');
        } else {
            $this->addFlash('danger', 'Vous n\'êtes pas autorisé a supprimer le produit');
            return $this->redirectToRoute('app_product_list');
        }
    }



    #[Route(path: 'admin/product/volume/new', name: 'app_product_volume_new', methods: ['GET', 'POST'])]
    public function newVolume(Request $request, EntityManagerInterface $entityManager): Response
    {

        $volume = new Volume();
        $volumeForm = $this->createForm(VolumeFormType::class, $volume);

        $volumeForm->handleRequest($request);
        if ($volumeForm->isSubmitted() && $volumeForm->isValid()) {
            $volume = $volumeForm->getData();

            $entityManager->persist($volume);
            $entityManager->flush();
            // $this->addFlash('success', 'Votre produit vient d\'être crée');
            // return $this->redirectToRoute('app_product_list', [], Response::HTTP_SEE_OTHER);
        }
        return $this->render('product/new-volume.html.twig', [
            'VolumeForm' => $volumeForm->createView(),
        ]);
    }


    #[Route(path: '/user/get-client-price-for-selected-volume/{productId}/{volumeId}', name: 'client-price-for-selected-volume', methods: ['GET'])]    
    /**
     * Renvoie sous forme JSON le prix d'un produit attribué à un client en fonction du volume sélectionné
     *
     * @param  mixed $productId
     * @param  mixed $volumeId
     * @param  mixed $request
     * @param  mixed $entityManager
     * @return JsonResponse
     */
    public function clientPrice($productId, $volumeId, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {

        if ($this->getUser()) {
            $priceData = $entityManager->getRepository(Price::class)->findOneBy(['product' => $productId, 'user' => $this->getUser()->getId(), 'volume' => $volumeId]);


            if ($priceData != null) {
                $price = $priceData->getPrice();

            } else {
                $price = 'prix non définie encore';
            }

        }
        if ($volumeId == '-1') {
            $price = 'Veuillez séléctionner un volume';
        }


        return new JsonResponse($price, 200);
    }



}