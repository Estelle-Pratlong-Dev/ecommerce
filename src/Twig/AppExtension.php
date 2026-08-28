<?php

namespace App\Twig;

use App\Repository\CategoryRepository;
use App\Service\CartService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * Fonctions/filtres Twig pratiques pour la boutique.
 */
class AppExtension extends AbstractExtension
{
    private string $currencySymbol;

    /** @param array<string,mixed> $shop Configuration centrale (config/shop.yaml) */
    public function __construct(
        private readonly CartService $cartService,
        private readonly CategoryRepository $categoryRepository,
        array $shop,
    ) {
        $this->currencySymbol = $shop['currency_symbol'] ?? '€';
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('cart_count', [$this, 'cartCount']),
            new TwigFunction('shop_categories', [$this, 'shopCategories']),
        ];
    }

    public function getFilters(): array
    {
        return [
            // Formate un montant EN CENTIMES vers "19,90 €"
            new TwigFilter('price', [$this, 'formatPriceFromCents']),
            // Formate un montant EN EUROS (float) vers "19,90 €"
            new TwigFilter('money', [$this, 'formatMoney']),
        ];
    }

    public function cartCount(): int
    {
        return $this->cartService->getTotalQuantity();
    }

    /** @return \App\Entity\Category[] Toutes les catégories (pour la barre de navigation). */
    public function shopCategories(): array
    {
        return $this->categoryRepository->findBy([], ['name' => 'ASC']);
    }

    public function formatPriceFromCents(int $cents): string
    {
        return $this->formatMoney($cents / 100);
    }

    public function formatMoney(float $amount): string
    {
        return number_format($amount, 2, ',', ' ') . ' ' . $this->currencySymbol;
    }
}
