<?php

namespace App\Service;

use App\Entity\Promo;
use App\Repository\ProductRepository;
use App\Repository\PromoRepository;
use App\Repository\SettingRepository;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Gère le panier de l'utilisateur, stocké en session.
 * Le panier est un tableau [ productId => quantité ] ; le code promo est stocké à part.
 */
class CartService
{
    private const SESSION_KEY = 'cart';
    private const SESSION_PROMO = 'cart_promo';

    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly ProductRepository $productRepository,
        private readonly PromoRepository $promoRepository,
        private readonly SettingRepository $settingRepository,
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
        $session = $this->requestStack->getSession();
        $session->remove(self::SESSION_KEY);
        $session->remove(self::SESSION_PROMO);
    }

    /** Nombre total d'articles (somme des quantités) — utilisé dans l'en-tête. */
    public function getTotalQuantity(): int
    {
        return array_sum($this->getRaw());
    }

    // --- Codes promo -----------------------------------------------------

    /** Tente d'appliquer un code promo. Renvoie true si le code est valide. */
    public function applyPromo(string $code): bool
    {
        $promo = $this->promoRepository->findValidByCode($code);
        if (!$promo) {
            return false;
        }
        $this->requestStack->getSession()->set(self::SESSION_PROMO, $promo->getCode());

        return true;
    }

    public function removePromo(): void
    {
        $this->requestStack->getSession()->remove(self::SESSION_PROMO);
    }

    public function getAppliedPromo(): ?Promo
    {
        $code = $this->requestStack->getSession()->get(self::SESSION_PROMO);

        return $code ? $this->promoRepository->findValidByCode($code) : null;
    }

    // --- Détail complet --------------------------------------------------

    /**
     * @return array{
     *   items: array<int, array{product: \App\Entity\Product, quantity: int, subtotalCents: int}>,
     *   subtotalCents: int, discountCents: int, shippingCents: int, totalCents: int,
     *   promo: ?Promo, freeShippingFromCents: int
     * }
     */
    public function getDetails(): array
    {
        $items = [];
        $subtotalCents = 0;

        foreach ($this->getRaw() as $productId => $quantity) {
            $product = $this->productRepository->find($productId);
            if (!$product || !$product->isActive()) {
                continue;
            }
            $lineCents = $product->getPriceCents() * $quantity;
            $subtotalCents += $lineCents;
            $items[] = [
                'product' => $product,
                'quantity' => $quantity,
                'subtotalCents' => $lineCents,
            ];
        }

        $settings = $this->settingRepository->getSettings();
        $promo = $this->getAppliedPromo();

        $discountCents = $promo ? $promo->computeDiscount($subtotalCents) : 0;
        $shippingCents = $subtotalCents > 0 ? $settings->shippingFor($subtotalCents) : 0;
        $totalCents = max(0, $subtotalCents - $discountCents) + $shippingCents;

        return [
            'items' => $items,
            'subtotalCents' => $subtotalCents,
            'discountCents' => $discountCents,
            'shippingCents' => $shippingCents,
            'totalCents' => $totalCents,
            'promo' => $promo,
            'freeShippingFromCents' => $settings->getShippingFreeFromCents(),
        ];
    }
}
