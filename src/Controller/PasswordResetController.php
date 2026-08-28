<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RequestPasswordResetType;
use App\Form\ResetPasswordType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PasswordResetController extends AbstractController
{
    private const TOKEN_TTL = '+1 hour';

    /** @param array<string,mixed> $shop */
    public function __construct(private readonly array $shop)
    {
    }

    /**
     * Étape 1 : le visiteur saisit son e-mail, on lui envoie un lien.
     */
    #[Route('/mot-de-passe-oublie', name: 'app_forgot_password')]
    public function request(
        Request $request,
        UserRepository $users,
        EntityManagerInterface $em,
        MailerInterface $mailer,
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_account');
        }

        $form = $this->createForm(RequestPasswordResetType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $user = $users->findOneBy(['email' => $email]);

            if ($user) {
                // Jeton en clair envoyé par mail, hash stocké en base.
                $token = bin2hex(random_bytes(32));
                $user->setResetTokenHash(hash('sha256', $token));
                $user->setResetTokenExpiresAt(new \DateTimeImmutable(self::TOKEN_TTL));
                $em->flush();

                $resetUrl = $this->generateUrl('app_reset_password', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);
                $this->sendResetEmail($mailer, $user, $resetUrl);
            }

            // Même message dans tous les cas : on ne révèle pas si l'e-mail existe.
            $this->addFlash('info', 'Si un compte existe avec cette adresse, un e-mail de réinitialisation vient d\'être envoyé.');

            return $this->redirectToRoute('app_forgot_password');
        }

        return $this->render('security/forgot_password.html.twig', [
            'requestForm' => $form,
        ]);
    }

    /**
     * Étape 2 : le visiteur arrive via le lien du mail et choisit un nouveau mot de passe.
     */
    #[Route('/reinitialiser-mot-de-passe/{token}', name: 'app_reset_password')]
    public function reset(
        string $token,
        Request $request,
        UserRepository $users,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher,
    ): Response {
        $user = $users->findOneBy(['resetTokenHash' => hash('sha256', $token)]);

        if (!$user || !$user->isResetTokenValid()) {
            $this->addFlash('error', 'Ce lien de réinitialisation est invalide ou a expiré. Merci de refaire une demande.');

            return $this->redirectToRoute('app_forgot_password');
        }

        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($hasher->hashPassword($user, $form->get('plainPassword')->getData()));
            $user->clearResetToken();
            $em->flush();

            $this->addFlash('success', 'Votre mot de passe a été réinitialisé. Vous pouvez vous connecter.');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/reset_password.html.twig', [
            'resetForm' => $form,
        ]);
    }

    private function sendResetEmail(MailerInterface $mailer, User $user, string $resetUrl): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address($this->shop['email'], $this->shop['name']))
            ->to(new Address($user->getEmail(), $user->getFullName()))
            ->subject('Réinitialisation de votre mot de passe')
            ->htmlTemplate('emails/password_reset.html.twig')
            ->context([
                'resetUrl' => $resetUrl,
                'user' => $user,
            ]);

        $mailer->send($email);
    }
}
