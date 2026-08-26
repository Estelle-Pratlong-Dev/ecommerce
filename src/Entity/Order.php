<?php

namespace App\Entity;

use App\Entity\Traits\BlameableTrait;
use App\Entity\Traits\TimestampableTrait;
use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: 'orders')]
class Order
{
    use TimestampableTrait;
    use BlameableTrait;

    // États possibles d'une commande
    public const STATUS_CART      = 'cart';      // panier en cours (avant paiement)
    public const STATUS_PENDING   = 'pending';   // en attente de paiement
    public const STATUS_PAID      = 'paid';      // payée
    public const STATUS_SHIPPED   = 'shipped';   // expédiée
    public const STATUS_CANCELLED = 'cancelled'; // annulée

    public const STATUS_LABELS = [
        self::STATUS_CART      => 'Panier',
        self::STATUS_PENDING   => 'En attente',
        self::STATUS_PAID      => 'Payée',
        self::STATUS_SHIPPED   => 'Expédiée',
        self::STATUS_CANCELLED => 'Annulée',
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20)]
    private string $status = self::STATUS_PENDING;

    #[ORM\Column(length: 30, unique: true)]
    private ?string $reference = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $shippingAddress = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $stripeSessionId = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $customer = null;

    /** @var Collection<int, OrderItem> */
    #[ORM\OneToMany(mappedBy: 'orderRef', targetEntity: OrderItem::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $items;

    public function __construct()
    {
        $this->items = new ArrayCollection();
        $this->reference = 'CMD-' . strtoupper(bin2hex(random_bytes(4)));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getStatusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): static
    {
        $this->reference = $reference;

        return $this;
    }

    public function getShippingAddress(): ?string
    {
        return $this->shippingAddress;
    }

    public function setShippingAddress(?string $shippingAddress): static
    {
        $this->shippingAddress = $shippingAddress;

        return $this;
    }

    public function getStripeSessionId(): ?string
    {
        return $this->stripeSessionId;
    }

    public function setStripeSessionId(?string $stripeSessionId): static
    {
        $this->stripeSessionId = $stripeSessionId;

        return $this;
    }

    public function getCustomer(): ?User
    {
        return $this->customer;
    }

    public function setCustomer(?User $customer): static
    {
        $this->customer = $customer;

        return $this;
    }

    /** @return Collection<int, OrderItem> */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(OrderItem $item): static
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setOrderRef($this);
        }

        return $this;
    }

    public function removeItem(OrderItem $item): static
    {
        if ($this->items->removeElement($item)) {
            if ($item->getOrderRef() === $this) {
                $item->setOrderRef(null);
            }
        }

        return $this;
    }

    /** Total de la commande en centimes. */
    public function getTotalCents(): int
    {
        $total = 0;
        foreach ($this->items as $item) {
            $total += $item->getSubtotalCents();
        }

        return $total;
    }

    /** Total en euros (float) pour l'affichage. */
    public function getTotal(): float
    {
        return $this->getTotalCents() / 100;
    }

    public function getTotalQuantity(): int
    {
        $qty = 0;
        foreach ($this->items as $item) {
            $qty += $item->getQuantity();
        }

        return $qty;
    }
}
