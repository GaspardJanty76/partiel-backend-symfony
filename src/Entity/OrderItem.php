<?php

namespace App\Entity;

use App\Repository\OrderItemRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderItemRepository::class)]
class OrderItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $quantity = null;

    #[ORM\Column]
    private ?float $price = null;

    #[ORM\ManyToOne(inversedBy: 'orderItems')]
    private ?Order $OrderRelation = null;

    #[ORM\ManyToOne(inversedBy: 'orderItems')]
    private ?Equipment $EquipmentRelation = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getOrderRelation(): ?Order
    {
        return $this->OrderRelation;
    }

    public function setOrderRelation(?Order $OrderRelation): static
    {
        $this->OrderRelation = $OrderRelation;

        return $this;
    }

    public function getEquipmentRelation(): ?Equipment
    {
        return $this->EquipmentRelation;
    }

    public function setEquipmentRelation(?Equipment $EquipmentRelation): static
    {
        $this->EquipmentRelation = $EquipmentRelation;

        return $this;
    }
}
