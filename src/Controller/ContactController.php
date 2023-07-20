<?php

namespace App\Controller;

use App\Form\ContactFormType;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;

class ContactController extends AbstractController
{

    private $mailer;
    #[Route(path: '/contact', name: 'app_contact')]    
    /**
     * Contient la création de formulaire de contact et l'envoi de mail
     *
     * @param  mixed $request
     * @param  mixed $mailer
     * @return Response
     */
    public function index(Request $request, MailerInterface $mailer): Response
    {

        $contactForm = $this->createForm(ContactFormType::class);
        $contactForm->handleRequest($request);
        if ($contactForm->isSubmitted() && $contactForm->isValid()) {

            $contactFormData = $contactForm->getData();
            switch ($contactFormData['subject']) {
                case 'claim':
                    $subject= 'Réclamation';
                    break;
                    case 'others':
                        $subject= 'Autres';
                        break;
                default:
                $subject= 'Demande d\'information';
            break;
            }
            // $subject = $contactFormData['subject'];
            $from = $contactFormData['email'];
            $fName = $contactFormData['fullName'];
            $phone=  $contactFormData['phone'];
            $context =  $contactFormData['message'];
            $isUrgent = $contactFormData['subject'] == "claim" ? true :false;
            $message = (new TemplatedEmail())
                ->from( new Address($from, $fName))
                ->to(new Address('contact@cleanup.ovh'))
                ->subject($subject)
                ->htmlTemplate('contact/contact_email_template.html.twig')
                
                ->context([
                    'date' => new \DateTime('now'),
                    'expiration_date' => $isUrgent? new \DateTime('+3 days') :new \DateTime('+10 days'),
                    'subject' => $subject,
                    'from'=>$from,
                    'fName'=>$fName,
                    'phone'=>$phone,
                    'message' => $context,
                    'isUrgent' =>$isUrgent
                ]);
            $mailer->send($message);




            $this->addFlash('success', 'Votre message vient d\'être envoyé');

            return $this->redirectToRoute('app_home');
        }

        return $this->render(
            'contact/index.html.twig',
            ['contactForm' => $contactForm]
        );

    }


}