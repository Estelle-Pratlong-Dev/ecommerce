<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    #[Route('/inscription', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em,
        Security $security,
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_account');
        }

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $passwordHasher->hashPassword($user, $form->get('plainPassword')->getData())
            );

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Votre compte a été créé, bienvenue !');

            // Connexion automatique après inscription
            $security->login($user, 'form_login', 'main');

            return $this->redirectToRoute('app_account');
        }

        return $this->render('security/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}
