<?php

namespace App\Entity;

use App\Repository\CustomerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CustomerRepository::class)]
class Customer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Name is required')]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: 'Name must be at least {{ limit }} characters long',
        maxMessage: 'Name cannot be longer than {{ limit }} characters'
    )]
    private ?string $name = null;

    #[ORM\OneToMany(mappedBy: 'customer', targetEntity: Spending::class, orphanRemoval: true)]
    private Collection $spendings;

    public function __construct()
    {
        $this->spendings = new ArrayCollection();
    }

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

    /**
     * @return Collection<int, Spending>
     */
    public function getSpendings(): Collection
    {
        return $this->spendings;
    }

    public function addSpending(Spending $spending): static
    {
        if (!$this->spendings->contains($spending)) {
            $this->spendings->add($spending);
            $spending->setCustomer($this);
        }

        return $this;
    }

    public function getTotalSpent(): float
    {
        return $this->spendings->reduce(function (float $total, Spending $spending) {
            return $total + $spending->getAmount();
        }, 0.0);
    }
}
