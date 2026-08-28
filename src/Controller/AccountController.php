<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\User;
use App\Form\ChangePasswordType;
use App\Form\ProfileType;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/mon-compte')]
#[IsGranted('ROLE_USER')]
class AccountController extends AbstractController
{
    #[Route('', name: 'app_account')]
    public function index(OrderRepository $orders): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->render('account/index.html.twig', [
            'orders' => $orders->findByCustomer($user),
        ]);
    }

    #[Route('/informations', name: 'app_account_profile')]
    public function profile(Request $request, EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Vos informations ont été mises à jour.');

            return $this->redirectToRoute('app_account_profile');
        }

        return $this->render('account/profile.html.twig', [
            'profileForm' => $form,
        ]);
    }

    #[Route('/mot-de-passe', name: 'app_account_password')]
    public function changePassword(Request $request, UserPasswordHasherInterface $hasher, EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $form = $this->createForm(ChangePasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérifie le mot de passe actuel
            if (!$hasher->isPasswordValid($user, $form->get('currentPassword')->getData())) {
                $form->get('currentPassword')->addError(new FormError('Mot de passe actuel incorrect.'));
            } else {
                $user->setPassword($hasher->hashPassword($user, $form->get('plainPassword')->getData()));
                $em->flush();
                $this->addFlash('success', 'Votre mot de passe a été mis à jour.');

                return $this->redirectToRoute('app_account_password');
            }
        }

        return $this->render('account/password.html.twig', [
            'passwordForm' => $form,
        ]);
    }

    #[Route('/commande/{id}', name: 'app_account_order')]
    public function order(Order $order): Response
    {
        // On vérifie que la commande appartient bien à l'utilisateur connecté.
        if ($order->getCustomer() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('account/order.html.twig', [
            'order' => $order,
        ]);
    }
}
