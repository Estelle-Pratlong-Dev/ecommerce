<?php

namespace App\Service;

use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Valide une commande payée : passage au statut « payée », décrément du stock,
 * envoi des e-mails. Idempotent : ne fait rien si la commande n'est pas « en attente »,
 * donc le webhook Stripe et le retour navigateur ne peuvent pas la traiter deux fois.
 */
class OrderFulfiller
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly OrderMailer $orderMailer,
    ) {
    }

    /** Valide la commande. Renvoie true si c'est cet appel qui l'a validée. */
    public function fulfill(Order $order): bool
    {
        if ($order->getStatus() !== Order::STATUS_PENDING) {
            return false;
        }

        $order->setStatus(Order::STATUS_PAID);

        foreach ($order->getItems() as $item) {
            $product = $item->getProduct();
            if ($product) {
                $product->setStock(max(0, $product->getStock() - $item->getQuantity()));
            }
        }

        $this->em->flush();

        $this->orderMailer->sendOrderPlaced($order);

        return true;
    }
}
