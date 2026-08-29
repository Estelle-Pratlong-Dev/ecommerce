<?php

namespace App\Controller;

use App\Repository\OrderRepository;
use App\Service\OrderFulfiller;
use Psr\Log\LoggerInterface;
use Stripe\Webhook;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Reçoit les notifications de paiement de Stripe (webhook).
 * C'est la source de vérité : Stripe appelle cette URL quand le paiement est confirmé,
 * même si le client a fermé son navigateur. La signature est vérifiée à chaque appel.
 */
class StripeWebhookController extends AbstractController
{
    public function __construct(private readonly string $stripeWebhookSecret)
    {
    }

    #[Route('/stripe/webhook', name: 'app_stripe_webhook', methods: ['POST'])]
    public function webhook(
        Request $request,
        OrderRepository $orders,
        OrderFulfiller $fulfiller,
        LoggerInterface $logger,
    ): Response {
        if (!$this->stripeWebhookSecret) {
            return new Response('Webhook non configuré.', Response::HTTP_BAD_REQUEST);
        }

        try {
            $event = Webhook::constructEvent(
                $request->getContent(),
                (string) $request->headers->get('stripe-signature'),
                $this->stripeWebhookSecret,
            );
        } catch (\Throwable $e) {
            // Signature invalide ou payload corrompu : on refuse.
            return new Response('Signature invalide.', Response::HTTP_BAD_REQUEST);
        }

        // Paiement confirmé (immédiat ou différé)
        if (in_array($event->type, ['checkout.session.completed', 'checkout.session.async_payment_succeeded'], true)) {
            $session = $event->data->object;
            $orderId = $session->client_reference_id ?? null;
            $order = $orderId ? $orders->find($orderId) : null;

            if ($order && ($session->payment_status ?? null) === 'paid') {
                if ($fulfiller->fulfill($order)) {
                    $logger->info('Commande {ref} validée via webhook Stripe.', ['ref' => $order->getReference()]);
                }
            }
        }

        return new Response('OK', Response::HTTP_OK);
    }
}
