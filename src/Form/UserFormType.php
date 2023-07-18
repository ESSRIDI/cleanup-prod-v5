<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email',EmailType::class)
            // ->add('roles')
            ->add('password',PasswordType::class)
            ->add('password', RepeatedType::class,
                ['type' => PasswordType::class,'first_options'  => ['label' => 'Password'],
                'second_options' => ['label' => 'Repeat Password']])
            // ->add('isVerified')
            ->add('firstName',TextType::class)
            ->add('lastName',TextType::class)
            ->add('phoneOne',TextType::class, ['label'=>'Mobile phone'])
            ->add('phoneTwo',TextType::class, ['label'=>'Landline phone' , 'required'=>false])
            // ->add('createdAt')
            // ->add('updatedAt')
            ->add('addresses',CollectionType::class, [
                'entry_type' => AddressFormType::class,
                'entry_options' => ['label' => false],])
            ->add('submit',SubmitType::class,['label'=> 'Sauvegarder'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
