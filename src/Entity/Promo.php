<?php

namespace App\Entity;

use App\Entity\Traits\BlameableTrait;
use App\Entity\Traits\TimestampableTrait;
use App\Repository\PromoRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PromoRepository::class)]
#[ORM\Table(name: 'promos')]
#[UniqueEntity(fields: ['code'], message: 'Ce code promo existe déjà.')]
class Promo
{
    use TimestampableTrait;
    use BlameableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 40, unique: true)]
    #[Assert\NotBlank]
    private ?string $code = null;

    /** Réduction en pourcentage (ex: 10 pour -10 %). Utilisé si renseigné. */
    #[ORM\Column(nullable: true)]
    #[Assert\Range(min: 1, max: 100)]
    private ?int $percent = null;

    /** Réduction en montant fixe (en centimes). Utilisé si "percent" est vide. */
    #[ORM\Column(nullable: true)]
    #[Assert\PositiveOrZero]
    private ?int $amountCents = null;

    /** Montant minimum de commande (en centimes) pour que le code s'applique. */
    #[ORM\Column]
    private int $minCents = 0;

    #[ORM\Column]
    private bool $active = true;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $expiresAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = strtoupper(trim($code));

        return $this;
    }

    public function getPercent(): ?int
    {
        return $this->percent;
    }

    public function setPercent(?int $percent): static
    {
        $this->percent = $percent;

        return $this;
    }

    public function getAmountCents(): ?int
    {
        return $this->amountCents;
    }

    public function setAmountCents(?int $amountCents): static
    {
        $this->amountCents = $amountCents;

        return $this;
    }

    public function getMinCents(): int
    {
        return $this->minCents;
    }

    public function setMinCents(int $minCents): static
    {
        $this->minCents = $minCents;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }

    public function getExpiresAt(): ?\DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(?\DateTimeImmutable $expiresAt): static
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }

    public function isValidNow(): bool
    {
        return $this->active
            && ($this->expiresAt === null || $this->expiresAt > new \DateTimeImmutable());
    }

    /** Réduction (en centimes) applicable à un sous-total donné (0 si non éligible). */
    public function computeDiscount(int $subtotalCents): int
    {
        if (!$this->isValidNow() || $subtotalCents < $this->minCents) {
            return 0;
        }

        if ($this->percent) {
            $discount = (int) floor($subtotalCents * $this->percent / 100);
        } elseif ($this->amountCents) {
            $discount = $this->amountCents;
        } else {
            return 0;
        }

        return min($discount, $subtotalCents);
    }

    public function __toString(): string
    {
        return (string) $this->code;
    }
}
