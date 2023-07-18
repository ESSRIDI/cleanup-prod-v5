<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    private $emailVerifier;
    private $mailer;

    public function __construct(EmailVerifier $emailVerifier, MailerInterface $mailer)
    {
        $this->emailVerifier = $emailVerifier;
        $this->mailer = $mailer;

    }

    #[Route('/register', name: 'app_register')]    
    /**
     * Persister les donnée d'un nouveau utilisateur et envoi de mail de verification
     *
     * @param  mixed $request
     * @param  mixed $userPasswordHasher
     * @param  mixed $entityManager
     * @return Response
     */
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        //Redirection à la page d'accueil si l'utlisateur est connecté
         if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $userData = $form->getData();
         
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            $proofPath =  $form->get('siretProof')->getData();
            if ($proofPath) {
               
                $newFileName = uniqid() . '.' . $proofPath->guessExtension();
                try {
                    $proofPath->move(
                        $this->getParameter('kernel.project_dir') . '/public/uploads',
                        $newFileName
                    );
                } catch (FileException $err) {

                    return new Response($err->getMessage());
                }
                $userData->setSiretProof('/uploads/' . $newFileName);
            $entityManager->persist($user);
            $entityManager->flush();

            // generate a signed url and email it to the user
            $this->emailVerifier->sendEmailConfirmation(
                'app_verify_email',
                $user,
                (new TemplatedEmail())
                    ->from(new Address('contact@cleanup.ovh', 'Cleanup Mail Bot'))
                    ->to($user->getEmail())
                    ->subject('Please Confirm your Email')
                    ->htmlTemplate('registration/confirmation_email.html.twig')

            );
            // do anything else you need here, like send an email



            return $this->redirectToRoute('app_email_verified');
        }
    }
        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
    
    #[Route('/verify/email', name: 'app_verify_email')]    
    /**
     * Rédirection après verification du mail
     *
     * @param  mixed $request
     * @param  mixed $translator
     * @return Response
     */
    public function verifyUserEmail(Request $request, TranslatorInterface $translator): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $this->getUser());
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('app_login');
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('success', 'Your email address has been verified.');

        // 
        return $this->redirectToRoute('app_email_verified');
    }
    #[Route('/register/conditions', name: 'app_signup_conditions')]    
    /**
     * Renvoie les conditions de création de compte
     *
     * @return Response
     */
    public function signUpConditions(): Response
    {

        return $this->render('registration/signup_conditions.html.twig');
    }

    #[Route('/email/verified', name: 'app_email_verified')]    
    /**
     * Redirection en cas de mail verifié
     *
     * @return Response
     */
    public function emailVerified(): Response
    {
        return $this->render('registration/email_verified.html.twig', [

        ]);
    }
}