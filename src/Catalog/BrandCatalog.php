<?php

namespace App\Catalog;

/**
 * Liste de référence des marques + normalisation des libellés.
 * Contrairement aux couleurs, on préserve la casse d'origine des marques inconnues
 * (ex: "EcoWear"), on ne fait que corriger les variantes connues.
 */
final class BrandCatalog
{
    /** Marques de référence (créées au seed). */
    public const BRANDS = [
        'Adidas',
        'Converse',
        'Jordan',
        'Nike',
        'Reebok',
        'Yeezy',
    ];

    /** Variantes (minuscules) -> nom canonique. */
    private const ALIASES = [
        'adidas' => 'Adidas',
        'converse' => 'Converse',
        'jordan' => 'Jordan',
        'air jordan' => 'Jordan',
        'nike' => 'Nike',
        'reebok' => 'Reebok',
        'yeezy' => 'Yeezy',
    ];

    /**
     * Renvoie le nom canonique d'une marque. Si inconnue, renvoie le libellé d'origine (casse préservée).
     */
    public static function normalize(?string $raw): ?string
    {
        $raw = trim((string) $raw);
        if ($raw === '') {
            return null;
        }

        return self::ALIASES[mb_strtolower($raw)] ?? $raw;
    }
}
