<?php

namespace App\Form;

use App\Entity\Price;

use App\Entity\Product;
use App\Entity\Volume;


use Doctrine\ORM\EntityManagerInterface;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;


use Symfony\Component\Form\Extension\Core\Type\NumberType;


use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PriceFormType extends AbstractType
{

    public function __construct(private EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
       
 // 3. Add 2 event listeners for the form
 $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onPreSetData']);
 $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit']);
 
 
        $builder
            ->add('price', NumberType::class, ['label' => 'Price'])
            // ->add('createdAt')
            // ->add('updatedAt')
            // ->add('product') //field 1
            ->add('user', null ,['label' => 'User'])
            // ->add('volume') // get resulat based on field 1 selection 
            
       
            ;
           

            
            
            // $builder->add('submit', SubmitType::class, ['label' => 'Enregistrer']);
    }

    protected function addElements(FormInterface $form, Product $product = null) {
        // 4. Add the province element
        $form->add('product', EntityType::class, [
            'label'=>'Product',
            'required' => true,
            'data' => $product,
            'placeholder' => 'Select a product...',
            'class' => Product::class,
        ]);
        $volumes =[];

        if ($product) {
  
            $repoVolume = $this->entityManager->getRepository(Volume::class);
            
            $volumes=$product->getVolumes();
        }
        $form->add('volume', EntityType::class, [
           
            'required' => true,
            'placeholder' => 'Select a product first ...',
            'class' => Volume::class,
            'choices' => $volumes
        ]);
  
    }

    function onPreSubmit(FormEvent $event) {
        $form = $event->getForm();
        $data = $event->getData();
       
        // Search for selected City and convert it into an Entity
        $product = $this->entityManager->getRepository(Product::class)->find($data['product']);
     
        $this->addElements($form, $product);
    }

    function onPreSetData(FormEvent $event) {
        $price = $event->getData();
   
        $form = $event->getForm();

        
        $product = $price->getProduct() ? $price->getProduct() : null;
  
        $this->addElements($form, $product);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Price::class,
        ]);
    }


}