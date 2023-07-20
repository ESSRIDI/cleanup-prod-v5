<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bundle\SecurityBundle\Security;

class ContactFormType extends AbstractType
{
    public function __construct(
        private Security $security,
    ) {
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        
        $builder
            ->add('fullName', TextType::class, [
                'label' => 'Full name',
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email'
            ])
            ->add('phone', TelType::class, [
                'label' => 'Phone',
                'required' => false
            ])
            ->add('subject', ChoiceType::class, [
                'label' => 'Subject',
                'choices' => [
                    'Request for information' => 'request',
                    'Claim' => 'claim',
                    'Others' => 'others'


                ]
            ])
            ->add('message', TextareaType::class, [
                'attr' => ['rows' => 8, 'cols' => 30],
                'label' => 'Message',
            ])
            //recaptcha google v3
            ->add('submit', SubmitType::class, [
                'label' => 'Envoyer',
                'attr' => [
                    'id' => 'contact_form_submit',
                    'name' => 'contact_form[submit]',
                    'class' => 'g-recaptcha',
                    'data-sitekey' => $_ENV['RECAPTCHA_KEY'],
                    'data-callback' => 'onSubmit',
                    'data-action' => 'submit'
                ]
            ])
        ;

        $user = $this->security->getUser();
        if ($user) {
        $builder ->add('email', EmailType::class, [
            'label' => 'Email',
            'data' =>$user->getUserIdentifier(),
        ])
        ->add('fullName', TextType::class, [
            'label' => 'Full name',
            'data' =>$user->getFirstName().' '.$user->getLastName(),
        ])
        ->add('phone', TelType::class, [
            'label' => 'Phone',
            'required' => false,
            'data' =>$user->getPhoneOne(),
        ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}