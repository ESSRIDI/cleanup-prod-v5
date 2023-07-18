<?php

namespace App\Form;

use App\Entity\Address;
use App\Entity\Transporter;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user= $options['user'];
        $builder
            ->add('adresses',EntityType::class,[
                'class'=>Address::class,
                'label'=> 'Choose your delivery address',
                'required'=>true,
                'multiple'=>false,
                'choices'=>$user->getAddresses(),
                'expanded'=>false
            ])
            ->add('transporter',EntityType::class,
            ['class'=> Transporter::class,
            'label'=> 'Choose your delivery option',
            'required'=>true,
            'multiple'=>false,
            'expanded'=>true
            ])
        ;
    
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
            'user'=>[],
            'attr'=>['class'=>'order-form']
        ]);
    }
}
