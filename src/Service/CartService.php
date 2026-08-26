<?php

namespace App\Service;

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Gère le panier de l'utilisateur, stocké en session.
 * Le panier est un simple tableau [ productId => quantité ].
 */
class CartService
{
    private const SESSION_KEY = 'cart';

    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly ProductRepository $productRepository,
    ) {
    }

    /** @return array<int,int> [productId => quantité] */
    private function getRaw(): array
    {
        return $this->requestStack->getSession()->get(self::SESSION_KEY, []);
    }

    /** @param array<int,int> $cart */
    private function save(array $cart): void
    {
        $this->requestStack->getSession()->set(self::SESSION_KEY, $cart);
    }

    public function add(int $productId, int $quantity = 1): void
    {
        $cart = $this->getRaw();
        $cart[$productId] = ($cart[$productId] ?? 0) + $quantity;
        $this->save($cart);
    }

    public function setQuantity(int $productId, int $quantity): void
    {
        $cart = $this->getRaw();
        if ($quantity <= 0) {
            unset($cart[$productId]);
        } else {
            $cart[$productId] = $quantity;
        }
        $this->save($cart);
    }

    public function remove(int $productId): void
    {
        $cart = $this->getRaw();
        unset($cart[$productId]);
        $this->save($cart);
    }

    public function clear(): void
    {
        $this->requestStack->getSession()->remove(self::SESSION_KEY);
    }

    /** Nombre total d'articles (somme des quantités) — utilisé dans l'en-tête. */
    public function getTotalQuantity(): int
    {
        return array_sum($this->getRaw());
    }

    /**
     * Détail complet du panier avec les produits chargés depuis la BDD.
     *
     * @return array{items: array<int, array{product: \App\Entity\Product, quantity: int, subtotalCents: int}>, totalCents: int}
     */
    public function getDetails(): array
    {
        $items = [];
        $totalCents = 0;

        foreach ($this->getRaw() as $productId => $quantity) {
            $product = $this->productRepository->find($productId);
            // Produit supprimé ou désactivé : on l'ignore.
            if (!$product || !$product->isActive()) {
                continue;
            }
            $subtotalCents = $product->getPriceCents() * $quantity;
            $totalCents += $subtotalCents;
            $items[] = [
                'product' => $product,
                'quantity' => $quantity,
                'subtotalCents' => $subtotalCents,
            ];
        }

        return ['items' => $items, 'totalCents' => $totalCents];
    }
}
