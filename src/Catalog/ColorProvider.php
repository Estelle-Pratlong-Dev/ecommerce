<?php

namespace App\Catalog;

use App\Entity\Color;
use App\Repository\ColorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * Récupère (ou crée) l'entité Color correspondant à un libellé saisi librement,
 * après normalisation via ColorCatalog. Mutualisé entre fixtures et import.
 */
class ColorProvider
{
    /** @var array<string, Color> cache par nom canonique, le temps d'un run */
    private array $cache = [];

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ColorRepository $colorRepository,
        private readonly SluggerInterface $slugger,
    ) {
    }

    public function resolve(?string $raw): ?Color
    {
        $name = ColorCatalog::normalize($raw);
        if ($name === null) {
            return null;
        }

        if (isset($this->cache[$name])) {
            return $this->cache[$name];
        }

        $color = $this->colorRepository->findOneBy(['name' => $name]);
        if (!$color) {
            $color = new Color();
            $color->setName($name);
            $color->setSlug(strtolower((string) $this->slugger->slug($name)));
            $color->setHex(ColorCatalog::COLORS[$name] ?? null);
            $this->em->persist($color);
        }

        return $this->cache[$name] = $color;
    }

    /** Crée toutes les couleurs de référence si elles n'existent pas encore. */
    public function seedCanonical(): void
    {
        foreach (array_keys(ColorCatalog::COLORS) as $name) {
            $this->resolve($name);
        }
    }
}
