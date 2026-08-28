<?php

namespace App\Service;

use App\Entity\Order;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

/**
 * Envoie les e-mails liés à une commande (confirmation client + notification admin).
 * L'expéditeur et l'adresse admin viennent de config/shop.yaml.
 */
class OrderMailer
{
    /** @param array<string,mixed> $shop */
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly array $shop,
    ) {
    }

    public function sendOrderPlaced(Order $order): void
    {
        $from = new Address($this->shop['email'], $this->shop['name']);

        // --- Confirmation au client ---
        $customer = $order->getCustomer();
        if ($customer && $customer->getEmail()) {
            $toClient = (new TemplatedEmail())
                ->from($from)
                ->to(new Address($customer->getEmail(), $customer->getFullName()))
                ->subject(sprintf('Confirmation de votre commande %s', $order->getReference()))
                ->htmlTemplate('emails/order_confirmation.html.twig')
                ->context(['order' => $order]);

            $this->mailer->send($toClient);
        }

        // --- Notification à l'administrateur (adresse de la boutique) ---
        $toAdmin = (new TemplatedEmail())
            ->from($from)
            ->to($this->shop['email'])
            ->subject(sprintf('Nouvelle commande %s', $order->getReference()))
            ->htmlTemplate('emails/order_admin_notification.html.twig')
            ->context(['order' => $order]);

        $this->mailer->send($toAdmin);
    }

    public function sendOrderShipped(Order $order): void
    {
        $customer = $order->getCustomer();
        if (!$customer || !$customer->getEmail()) {
            return;
        }

        $email = (new TemplatedEmail())
            ->from(new Address($this->shop['email'], $this->shop['name']))
            ->to(new Address($customer->getEmail(), $customer->getFullName()))
            ->subject(sprintf('Votre commande %s a été expédiée', $order->getReference()))
            ->htmlTemplate('emails/order_shipped.html.twig')
            ->context(['order' => $order]);

        $this->mailer->send($email);
    }
}
