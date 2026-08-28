<?php

namespace App\Entity;

use App\Entity\Traits\TimestampableTrait;
use App\Repository\SettingRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Paramètres de la boutique éditables depuis l'admin (une seule ligne).
 * Pour l'instant : les frais de port.
 */
#[ORM\Entity(repositoryClass: SettingRepository::class)]
#[ORM\Table(name: 'settings')]
class Setting
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /** Forfait de livraison, en centimes. */
    #[ORM\Column]
    private int $shippingFlatCents = 490;

    /** Livraison offerte à partir de ce montant d'achat (en centimes). 0 = jamais. */
    #[ORM\Column]
    private int $shippingFreeFromCents = 6000;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getShippingFlatCents(): int
    {
        return $this->shippingFlatCents;
    }

    public function setShippingFlatCents(int $shippingFlatCents): static
    {
        $this->shippingFlatCents = $shippingFlatCents;

        return $this;
    }

    public function getShippingFreeFromCents(): int
    {
        return $this->shippingFreeFromCents;
    }

    public function setShippingFreeFromCents(int $shippingFreeFromCents): static
    {
        $this->shippingFreeFromCents = $shippingFreeFromCents;

        return $this;
    }

    /** Frais de port applicables à un sous-total donné. */
    public function shippingFor(int $subtotalCents): int
    {
        if ($subtotalCents <= 0) {
            return 0;
        }
        if ($this->shippingFreeFromCents > 0 && $subtotalCents >= $this->shippingFreeFromCents) {
            return 0;
        }

        return $this->shippingFlatCents;
    }
}
