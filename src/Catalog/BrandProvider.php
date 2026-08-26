<?php

namespace App\Catalog;

use App\Entity\Brand;
use App\Repository\BrandRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * Récupère (ou crée) l'entité Brand correspondant à un libellé, après normalisation.
 * Mutualisé entre fixtures et import.
 */
class BrandProvider
{
    /** @var array<string, Brand> */
    private array $cache = [];

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly BrandRepository $brandRepository,
        private readonly SluggerInterface $slugger,
    ) {
    }

    public function resolve(?string $raw): ?Brand
    {
        $name = BrandCatalog::normalize($raw);
        if ($name === null) {
            return null;
        }

        if (isset($this->cache[$name])) {
            return $this->cache[$name];
        }

        $brand = $this->brandRepository->findOneBy(['name' => $name]);
        if (!$brand) {
            $brand = new Brand();
            $brand->setName($name);
            $brand->setSlug(strtolower((string) $this->slugger->slug($name)));
            $this->em->persist($brand);
        }

        return $this->cache[$name] = $brand;
    }

    /** Crée les marques de référence si elles n'existent pas encore. */
    public function seedCanonical(): void
    {
        foreach (BrandCatalog::BRANDS as $name) {
            $this->resolve($name);
        }
    }
}
