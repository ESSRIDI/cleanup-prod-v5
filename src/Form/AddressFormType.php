<?php

namespace App\Form;

use App\Entity\Address;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class AddressFormType extends AbstractType
{
    public function __construct(
        private Security $security,
    ) {
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        
        $user = $this->security->getUser();
        $builder
        // ->add('id',IntegerType::class, ['attr'=>['class'=>'no-visible'],'label'=>false])
            ->add('type',ChoiceType::class, [
                'choices' => [
                    'Home' => 'home',
                    'Work' => 'work',
                    'Other' => 'other',
                ]],)
            ->add('lineOne')
            ->add('lineTwo')
            ->add('PostalCode')
            ->add('city')
            ->add('country')
            // ->add('user')
            // ->add('submit', SubmitType::class, ['attr' => ['label'=> 'Enregister']])
        ;

       
     

          
      
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Address::class,
            
        ]);
    }
}
