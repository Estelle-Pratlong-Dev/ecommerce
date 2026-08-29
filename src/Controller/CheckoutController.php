<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\User;
use App\Repository\OrderRepository;
use App\Service\CartService;
use App\Service\OrderFulfiller;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/commande')]
#[IsGranted('ROLE_USER')]
class CheckoutController extends AbstractController
{
    /** @param array<string,mixed> $shop */
    public function __construct(
        private readonly array $shop,
        private readonly string $stripeSecretKey,
    ) {
    }

    /**
     * Crée la commande à partir du panier puis redirige vers le paiement Stripe.
     */
    #[Route('', name: 'app_checkout')]
    public function checkout(CartService $cart, EntityManagerInterface $em): Response
    {
        $details = $cart->getDetails();

        if (empty($details['items'])) {
            $this->addFlash('error', 'Votre panier est vide.');
            return $this->redirectToRoute('app_cart');
        }

        /** @var User $user */
        $user = $this->getUser();

        // --- Vérifie que les infos de livraison sont complètes ---
        if (!$user->hasCompleteShippingInfo()) {
            $this->addFlash('error', 'Merci de compléter votre adresse et votre téléphone avant de commander.');
            return $this->redirectToRoute('app_account_profile');
        }

        // --- Création de la commande (statut : en attente de paiement) ---
        $order = new Order();
        $order->setCustomer($user);
        $order->setStatus(Order::STATUS_PENDING);
        $order->setShippingAddress($user->getFullAddress());

        foreach ($details['items'] as $line) {
            $product = $line['product'];
            $item = new OrderItem();
            $item->setProduct($product);
            $item->setProductName($product->getName());
            $item->setUnitPriceCents($product->getPriceCents());
            $item->setQuantity($line['quantity']);
            $order->addItem($item);
        }

        // Fige la livraison et la remise sur la commande.
        $order->setShippingCents($details['shippingCents']);
        $order->setDiscountCents($details['discountCents']);
        $order->setPromoCode($details['promo'] ? $details['promo']->getCode() : null);

        $em->persist($order);
        $em->flush();

        // --- Si Stripe n'est pas configuré : on simule un paiement réussi (mode dev) ---
        if (!$this->stripeSecretKey) {
            return $this->redirectToRoute('app_checkout_success', ['order' => $order->getId(), 'dev' => 1]);
        }

        // --- Création de la session de paiement Stripe ---
        Stripe::setApiKey($this->stripeSecretKey);
        $currency = strtolower($this->shop['currency'] ?? 'eur');

        $lineItems = [];
        foreach ($order->getItems() as $item) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => $currency,
                    'product_data' => ['name' => $item->getProductName()],
                    'unit_amount' => $item->getUnitPriceCents(),
                ],
                'quantity' => $item->getQuantity(),
            ];
        }

        // Frais de port en ligne dédiée
        if ($order->getShippingCents() > 0) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => $currency,
                    'product_data' => ['name' => 'Frais de livraison'],
                    'unit_amount' => $order->getShippingCents(),
                ],
                'quantity' => 1,
            ];
        }

        $sessionParams = [
            'mode' => 'payment',
            'line_items' => $lineItems,
            'customer_email' => $user->getEmail(),
            'client_reference_id' => (string) $order->getId(),
            'success_url' => $this->generateUrl('app_checkout_success', ['order' => $order->getId()], 0)
                . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $this->generateUrl('app_checkout_cancel', ['order' => $order->getId()], 0),
        ];

        // Remise : coupon Stripe créé à la volée
        if ($order->getDiscountCents() > 0) {
            $coupon = \Stripe\Coupon::create([
                'amount_off' => $order->getDiscountCents(),
                'currency' => $currency,
                'duration' => 'once',
                'name' => 'Code promo ' . $order->getPromoCode(),
            ]);
            $sessionParams['discounts'] = [['coupon' => $coupon->id]];
        }

        $session = StripeSession::create($sessionParams);

        $order->setStripeSessionId($session->id);
        $em->flush();

        return $this->redirect($session->url, 303);
    }

    #[Route('/succes/{order}', name: 'app_checkout_success')]
    public function success(Order $order, CartService $cart, OrderFulfiller $fulfiller): Response
    {
        if ($order->getCustomer() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        // La validation officielle passe par le webhook Stripe. Ici on ne fait qu'un
        // filet de sécurité si la commande est encore « en attente » au retour du client.
        if ($order->getStatus() === Order::STATUS_PENDING) {
            if (!$this->stripeSecretKey) {
                // Mode démo (pas de clé) : on simule le paiement.
                $fulfiller->fulfill($order);
            } else {
                // Mode réel : on vérifie auprès de Stripe (au cas où le webhook n'est pas encore arrivé).
                try {
                    Stripe::setApiKey($this->stripeSecretKey);
                    $session = StripeSession::retrieve($order->getStripeSessionId());
                    if (($session->payment_status ?? null) === 'paid') {
                        $fulfiller->fulfill($order);
                    }
                } catch (\Throwable) {
                    // On n'échoue pas la page : le webhook validera la commande.
                }
            }
        }

        if ($order->getStatus() === Order::STATUS_PAID) {
            $cart->clear();
        }

        return $this->render('checkout/success.html.twig', [
            'order' => $order,
            'paid' => $order->getStatus() === Order::STATUS_PAID,
        ]);
    }

    #[Route('/annulee/{order}', name: 'app_checkout_cancel')]
    public function cancel(Order $order, EntityManagerInterface $em): Response
    {
        if ($order->getCustomer() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        if ($order->getStatus() === Order::STATUS_PENDING) {
            $order->setStatus(Order::STATUS_CANCELLED);
            $em->flush();
        }

        $this->addFlash('info', 'Paiement annulé. Votre panier est conservé.');

        return $this->redirectToRoute('app_cart');
    }
}
