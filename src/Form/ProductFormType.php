<?php

namespace App\Form;

use App\Entity\Product;


use App\Form\VolumeFormType;
use PhpParser\Node\Stmt\Label;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Contracts\Service\Attribute\Required;

class ProductFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name',TextType::class,['label' => 'Product name'])
            ->add('description',TextareaType::class)
            // ->add('image',TextType::class)
            // ->add('createdAt')
            ->add('scent',TextType::class)
            ->add('quality', ChoiceType::class, [
            'choices'  => [
                'Original' => 'Original',
                'Essential' => 'essential'
              
    
            ]])
            ->add('image', FileType::class, [
                


                // unmapped means that this field is not associated to any entity property
                'mapped' => false,

                // make it optional so you don't have to re-upload the PDF file
                // every time you edit the Product details
                'required' => false,

                // unmapped fields can't define their validation using annotations
                // in the associated entity, so you can use the PHP constraint classes
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/*',
                        ],
                        'mimeTypesMessage' => 'Svp,choisir une image',
                    ])
                ],
            ])
            ->add('barcode', FileType::class, [
               
                // 'placeholder'=>'test',

                // unmapped means that this field is not associated to any entity property
                'mapped' => false,

                // make it optional so you don't have to re-upload the PDF file
                // every time you edit the Product details
                'required' => false,

                // unmapped fields can't define their validation using annotations
                // in the associated entity, so you can use the PHP constraint classes
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/*',
                        ],
                        'mimeTypesMessage' => 'Svp,choisir une image du code barre',
                    ])
                ],
            ])
     ->add('volumes',CollectionType::class, [
        'label'=> false, 
        'entry_type' => VolumeFormType::class,
        'entry_options' => ['label' => false],
        'allow_add' => true,
        'by_reference' => false,
        'allow_delete' => true,

    ])

    ->add('unity', ChoiceType::class, [
        'choices'  => [
            'L' => 'L',
            'mL' => 'mL',
            'Kg' => 'Kg',
            'g' => 'g',
            'u'=>'u',
            '6u/Box'=>'6u_carton',
            '12u/Box'=>'12u_Carton',
            'Empty'=>' '

        ]])
            ->add('isAvailable',CheckboxType::class, ['required'=>false])
            ->add('category', null)
            ->add('submit',SubmitType::class,['label'=> 'Save'])
            
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
