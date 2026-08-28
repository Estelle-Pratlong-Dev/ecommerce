<?php

namespace App\Controller;

use App\Form\ContactType;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    /** @param array<string,mixed> $shop */
    public function __construct(private readonly array $shop)
    {
    }

    #[Route('/', name: 'app_home')]
    public function index(ProductRepository $products, CategoryRepository $categories): Response
    {
        return $this->render('home/index.html.twig', [
            'featured' => $products->findFeatured(8),
            'categories' => $categories->findAll(),
        ]);
    }

    #[Route('/contact', name: 'app_contact')]
    public function contact(Request $request, MailerInterface $mailer): Response
    {
        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $email = (new TemplatedEmail())
                ->from(new Address($this->shop['email'], $this->shop['name']))
                ->to($this->shop['email'])
                ->replyTo(new Address($data['email'], $data['name']))
                ->subject('[Contact] ' . $data['subject'])
                ->htmlTemplate('emails/contact.html.twig')
                ->context(['data' => $data]);

            $mailer->send($email);

            $this->addFlash('success', 'Votre message a bien été envoyé. Nous vous répondrons rapidement.');

            return $this->redirectToRoute('app_contact');
        }

        return $this->render('home/contact.html.twig', [
            'contactForm' => $form,
        ]);
    }
}
