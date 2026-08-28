<?php

namespace App\Repository;

use App\Entity\Brand;
use App\Entity\Color;
use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    /** @return Product[] Produits actifs mis en avant. */
    public function findFeatured(int $limit = 8): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.active = true')
            ->andWhere('p.featured = true')
            ->orderBy('p.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Catalogue filtré. $filters peut contenir :
     *   search      => string
     *   categories  => string[] (slugs)
     *   brands      => string[]
     *   colors      => string[]
     *   types       => string[]
     *   sizes       => string[]
     *   featured    => bool
     *   priceMin    => float (euros)
     *   priceMax    => float (euros)
     *
     * @param array<string,mixed> $filters
     * @return Paginator<Product>
     */
    public function findByFilters(array $filters = [], string $sort = 'recent', int $page = 1, int $perPage = 12): Paginator
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.category', 'c')
            ->andWhere('p.active = true');

        if (!empty($filters['search'])) {
            $qb->andWhere('p.name LIKE :q OR p.description LIKE :q')
               ->setParameter('q', '%' . $filters['search'] . '%');
        }

        if (!empty($filters['categories'])) {
            $qb->andWhere('c.slug IN (:cats)')->setParameter('cats', $filters['categories']);
        }

        // Filtres simples sur une colonne texte avec une liste de valeurs (IN)
        $inFilters = [
            'types' => 'p.type',
            'sizes' => 'p.size',
        ];
        foreach ($inFilters as $key => $field) {
            if (!empty($filters[$key])) {
                $param = 'f_' . $key;
                $qb->andWhere(sprintf('%s IN (:%s)', $field, $param))
                   ->setParameter($param, (array) $filters[$key]);
            }
        }

        // Marque et couleur : relations -> on filtre sur le slug
        if (!empty($filters['brands'])) {
            $qb->join('p.brand', 'br')
               ->andWhere('br.slug IN (:brands)')
               ->setParameter('brands', (array) $filters['brands']);
        }
        if (!empty($filters['colors'])) {
            $qb->join('p.color', 'col')
               ->andWhere('col.slug IN (:colors)')
               ->setParameter('colors', (array) $filters['colors']);
        }

        if (!empty($filters['featured'])) {
            $qb->andWhere('p.featured = true');
        }

        if (isset($filters['priceMin']) && $filters['priceMin'] !== null && $filters['priceMin'] !== '') {
            $qb->andWhere('p.priceCents >= :pmin')
               ->setParameter('pmin', (int) round((float) $filters['priceMin'] * 100));
        }
        if (isset($filters['priceMax']) && $filters['priceMax'] !== null && $filters['priceMax'] !== '') {
            $qb->andWhere('p.priceCents <= :pmax')
               ->setParameter('pmax', (int) round((float) $filters['priceMax'] * 100));
        }

        // Tri
        match ($sort) {
            'price_asc'  => $qb->orderBy('p.priceCents', 'ASC'),
            'price_desc' => $qb->orderBy('p.priceCents', 'DESC'),
            'name'       => $qb->orderBy('p.name', 'ASC'),
            default      => $qb->orderBy('p.createdAt', 'DESC'), // 'recent'
        };

        // Pagination
        $page = max(1, $page);
        $qb->setFirstResult(($page - 1) * $perPage)
           ->setMaxResults($perPage);

        return new Paginator($qb->getQuery(), fetchJoinCollection: false);
    }

    /**
     * Valeurs distinctes (non vides) d'un attribut, parmi les produits actifs.
     * Sert à alimenter les boutons du panneau de filtres.
     *
     * @return string[]
     */
    public function findDistinctValues(string $field): array
    {
        $allowed = ['type', 'size'];
        if (!in_array($field, $allowed, true)) {
            throw new \InvalidArgumentException(sprintf('Attribut de filtre non autorisé : "%s".', $field));
        }

        $rows = $this->createQueryBuilder('p')
            ->select(sprintf('DISTINCT p.%s AS value', $field))
            ->andWhere('p.active = true')
            ->andWhere(sprintf('p.%s IS NOT NULL', $field))
            ->andWhere(sprintf("p.%s != ''", $field))
            ->orderBy('value', 'ASC')
            ->getQuery()
            ->getScalarResult();

        return array_map(static fn (array $r) => $r['value'], $rows);
    }

    /**
     * Couleurs réellement utilisées par des produits actifs (pour le panneau de filtres).
     *
     * @return \App\Entity\Color[]
     */
    public function findUsedColors(): array
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('col')
            ->distinct()
            ->from(Color::class, 'col')
            ->innerJoin(Product::class, 'p', 'WITH', 'p.color = col')
            ->andWhere('p.active = true')
            ->orderBy('col.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Marques réellement utilisées par des produits actifs (pour le panneau de filtres).
     *
     * @return \App\Entity\Brand[]
     */
    public function findUsedBrands(): array
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('br')
            ->distinct()
            ->from(Brand::class, 'br')
            ->innerJoin(Product::class, 'p', 'WITH', 'p.brand = br')
            ->andWhere('p.active = true')
            ->orderBy('br.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
