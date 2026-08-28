<?php

namespace App\Repository;

use App\Entity\Promo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Promo>
 */
class PromoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Promo::class);
    }

    public function findValidByCode(string $code): ?Promo
    {
        $promo = $this->findOneBy(['code' => strtoupper(trim($code))]);

        return $promo && $promo->isValidNow() ? $promo : null;
    }
}
