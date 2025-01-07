<?php

namespace App\Service;

use App\Entity\Customer;
use App\Repository\CustomerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

// Servisni trida pro business logiku zakazniku
class CustomerService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CustomerRepository $customerRepository,
        private ValidatorInterface $validator
    ) {}

    // Ziskat filtrovany seznam zakazniku
    // Predava parametry do repository
    public function getFilteredCustomers(array $filters): array
    {
        return $this->customerRepository->findByFilters(
            name: $filters['name'] ?? null,
            minTotalSpent: $filters['min_total_spent'] ?? null,
            maxTotalSpent: $filters['max_total_spent'] ?? null,
            sortBy: $filters['sort_by'] ?? 'name',
            sortOrder: $filters['sort_order'] ?? 'ASC'
        );
    }

    // Vytvorit noveho zakaznika
    // Obsahuje validaci dat
    public function createCustomer(string $name): array
    {
        $customer = new Customer();
        $customer->setName($name);

        $errors = $this->validator->validate($customer);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            throw new \InvalidArgumentException(implode(', ', $errorMessages));
        }

        $this->entityManager->persist($customer);
        $this->entityManager->flush();

        return [
            'id' => $customer->getId(),
            'name' => $customer->getName(),
            'total_spent' => 0
        ];
    }
}
