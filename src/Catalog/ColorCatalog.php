<?php

namespace App\Catalog;

/**
 * Liste de référence des couleurs + normalisation des libellés.
 * On uniformise les couleurs (noms de couleur, pas adjectifs) :
 * "blanche"/"blanc" -> "Blanc", "noire"/"noir" -> "Noir", etc.
 */
final class ColorCatalog
{
    /** Couleurs canoniques : nom => code hexadécimal (pour la pastille). */
    public const COLORS = [
        'Blanc'  => '#ffffff',
        'Noir'   => '#111827',
        'Gris'   => '#9ca3af',
        'Beige'  => '#e7dcc3',
        'Écru'   => '#f3ecd8',
        'Marron' => '#795548',
        'Rouge'  => '#dc2626',
        'Orange' => '#f97316',
        'Jaune'  => '#facc15',
        'Vert'   => '#16a34a',
        'Bleu'   => '#2563eb',
        'Violet' => '#7c3aed',
        'Ambre'  => '#f59e0b',
    ];

    /** Variantes (minuscules) -> nom canonique. */
    private const ALIASES = [
        'blanc' => 'Blanc', 'blanche' => 'Blanc',
        'noir' => 'Noir', 'noire' => 'Noir',
        'gris' => 'Gris', 'grise' => 'Gris',
        'beige' => 'Beige',
        'écru' => 'Écru', 'ecru' => 'Écru',
        'marron' => 'Marron',
        'rouge' => 'Rouge',
        'orange' => 'Orange',
        'jaune' => 'Jaune',
        'vert' => 'Vert', 'verte' => 'Vert',
        'bleu' => 'Bleu', 'bleue' => 'Bleu',
        'violet' => 'Violet', 'violette' => 'Violet',
        'ambre' => 'Ambre',
    ];

    /**
     * Renvoie le nom canonique d'une couleur saisie librement.
     * Si la couleur est inconnue, renvoie une version "Titre" (jamais null pour une chaîne non vide).
     */
    public static function normalize(?string $raw): ?string
    {
        $raw = trim((string) $raw);
        if ($raw === '') {
            return null;
        }

        $key = mb_strtolower($raw);

        return self::ALIASES[$key] ?? mb_convert_case($key, MB_CASE_TITLE);
    }
}
