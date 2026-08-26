<?php

namespace App\Entity;

use App\Entity\Traits\BlameableTrait;
use App\Entity\Traits\TimestampableTrait;
use App\Repository\ColorRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ColorRepository::class)]
#[ORM\Table(name: 'colors')]
class Color
{
    use TimestampableTrait;
    use BlameableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 60, unique: true)]
    #[Assert\NotBlank]
    private ?string $name = null;

    #[ORM\Column(length: 70, unique: true)]
    private ?string $slug = null;

    /** Code hexadécimal pour la pastille de couleur (ex: #ffffff). */
    #[ORM\Column(length: 7, nullable: true)]
    private ?string $hex = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getHex(): ?string
    {
        return $this->hex;
    }

    public function setHex(?string $hex): static
    {
        $this->hex = $hex;

        return $this;
    }

    public function __toString(): string
    {
        return (string) $this->name;
    }
}
