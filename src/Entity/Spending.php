<?php

namespace App\Entity;

use App\Repository\SpendingRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SpendingRepository::class)]
class Spending
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'spendings')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'Customer is required')]
    private ?Customer $customer = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotNull(message: 'Date is required')]
    #[Assert\Type("\DateTimeInterface")]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column]
    #[Assert\NotNull(message: 'Amount is required')]
    #[Assert\Type('float', message: 'Amount must be a number')]
    #[Assert\GreaterThan(0, message: 'Amount must be greater than 0')]
    private ?float $amount = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(?Customer $customer): static
    {
        $this->customer = $customer;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): static
    {
        $this->amount = $amount;

        return $this;
    }
}
