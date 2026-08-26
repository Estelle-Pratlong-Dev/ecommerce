<?php

namespace App\Repository;

use App\Entity\Order;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Order>
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    /** @return Order[] Commandes d'un client (hors paniers), les plus récentes d'abord. */
    public function findByCustomer(User $customer): array
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.customer = :customer')
            ->andWhere('o.status != :cart')
            ->setParameter('customer', $customer)
            ->setParameter('cart', Order::STATUS_CART)
            ->orderBy('o.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
