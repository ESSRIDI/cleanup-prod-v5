<?php

namespace App\Tests\Unit;

use App\Entity\Category;
use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ProductTest extends KernelTestCase
{    
    /**
     * teste la validité de l'entité
     *
     * @return void
     */
    public function testEntityIsValid(): void
    {
        self::bootKernel();
       $container= static::getContainer();
       $product = new Product();
       $product->setName('produit #1');
       $product->setDescription('Description de mon produit 1');
       $product->setQuality('Original');
       $category = new Category();
       $category->setLabel('catégorie #1');
       $product->setCategory($category);
       $product->setCreatedAt(new \DateTimeImmutable());
       $product->setUpdatedAt(new \DateTimeImmutable());
       $product->setUnity('L');
       $errors = $container->get('validator')->validate($product);
     $this->assertCount(0, $errors);
    }
    
    /**
     * Vérifier la validité du nom du produit : il ne doit pas être vide et comporter au minimum 5 caractères.
     * ici on s'attend à 2 errerus
     * 
     * @return void
     */
    public function testInvalidProductName(): void
    {
        self::bootKernel();
       $container= static::getContainer();
       $product = new Product();
       $product->setName('');
       $product->setDescription('Description de mon produit 1');
       $product->setQuality('Original');
       $category = new Category();
       $category->setLabel('catégorie #1');
       $product->setCategory($category);
       $product->setCreatedAt(new \DateTimeImmutable());
       $product->setUpdatedAt(new \DateTimeImmutable());
       $product->setUnity('L');
       $errors = $container->get('validator')->validate($product);
     $this->assertCount(2, $errors);
    }
}
