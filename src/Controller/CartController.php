<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/panier')]
class CartController extends AbstractController
{
    #[Route('', name: 'app_cart')]
    public function index(CartService $cart): Response
    {
        return $this->render('cart/index.html.twig', [
            'cart' => $cart->getDetails(),
        ]);
    }

    #[Route('/ajouter/{id}', name: 'app_cart_add', methods: ['POST'])]
    public function add(int $id, Request $request, CartService $cart, ProductRepository $products): Response
    {
        if (!$this->isCsrfTokenValid('cart_add_' . $id, (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }

        $product = $products->find($id);
        if (!$product || !$product->isActive()) {
            throw $this->createNotFoundException();
        }

        $quantity = max(1, (int) $request->request->get('quantity', 1));
        $cart->add($id, $quantity);

        $this->addFlash('success', sprintf('« %s » a été ajouté au panier.', $product->getName()));

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/modifier/{id}', name: 'app_cart_update', methods: ['POST'])]
    public function update(int $id, Request $request, CartService $cart): Response
    {
        if (!$this->isCsrfTokenValid('cart_update_' . $id, (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }

        $cart->setQuantity($id, (int) $request->request->get('quantity', 1));

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/retirer/{id}', name: 'app_cart_remove', methods: ['POST'])]
    public function remove(int $id, Request $request, CartService $cart): Response
    {
        if (!$this->isCsrfTokenValid('cart_remove_' . $id, (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }

        $cart->remove($id);
        $this->addFlash('info', 'Article retiré du panier.');

        return $this->redirectToRoute('app_cart');
    }
}
