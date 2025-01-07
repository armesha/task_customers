<?php

namespace App\Service;

use App\Entity\Spending;
use App\Repository\CustomerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

// Servisni trida pro spravu vydaju zakazniku
class SpendingService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CustomerRepository $customerRepository,
        private ValidatorInterface $validator
    ) {}

    // Vytvorit novy vydaj pro zakaznika
    // Kontroluje existenci zakaznika a validuje castku
    public function createSpending(int $customerId, string $date, float $amount): array
    {
        $customer = $this->customerRepository->find($customerId);
        if (!$customer) {
            throw new \InvalidArgumentException('Customer not found');
        }

        try {
            $dateObj = new \DateTime($date);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException('Invalid date format');
        }

        $spending = new Spending();
        $spending->setCustomer($customer)
                ->setDate($dateObj)
                ->setAmount($amount);

        $errors = $this->validator->validate($spending);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            throw new \InvalidArgumentException(implode(', ', $errorMessages));
        }

        $this->entityManager->persist($spending);
        $this->entityManager->flush();

        return [
            'id' => $spending->getId(),
            'customer_id' => $customer->getId(),
            'date' => $spending->getDate()->format('Y-m-d'),
            'amount' => $spending->getAmount()
        ];
    }
}