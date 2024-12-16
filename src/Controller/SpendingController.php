<?php

namespace App\Controller;

use App\Entity\Spending;
use App\Repository\CustomerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/users')]
class SpendingController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CustomerRepository $customerRepository,
        private ValidatorInterface $validator
    ) {}

    #[Route('/{id}/spending', methods: ['POST'])]
    public function create(int $id, Request $request): JsonResponse
    {
        try {
            $customer = $this->customerRepository->find($id);
            if (!$customer) {
                return new JsonResponse(['error' => 'Customer not found'], Response::HTTP_NOT_FOUND);
            }

            $data = json_decode($request->getContent(), true);
            
            if (!isset($data['date']) || !isset($data['amount'])) {
                return new JsonResponse(['error' => 'Date and amount are required'], Response::HTTP_BAD_REQUEST);
            }

            try {
                $date = new \DateTime($data['date']);
            } catch (\Exception $e) {
                return new JsonResponse(['error' => 'Invalid date format'], Response::HTTP_BAD_REQUEST);
            }

            $amount = filter_var($data['amount'], FILTER_VALIDATE_FLOAT);
            if ($amount === false) {
                return new JsonResponse(['error' => 'Invalid amount format'], Response::HTTP_BAD_REQUEST);
            }

            $spending = new Spending();
            $spending->setCustomer($customer)
                    ->setDate($date)
                    ->setAmount($amount);

            $errors = $this->validator->validate($spending);
            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[] = $error->getMessage();
                }
                return new JsonResponse(['error' => $errorMessages], Response::HTTP_BAD_REQUEST);
            }

            $this->entityManager->persist($spending);
            $this->entityManager->flush();

            return new JsonResponse([
                'id' => $spending->getId(),
                'customer_id' => $customer->getId(),
                'date' => $spending->getDate()->format('Y-m-d'),
                'amount' => $spending->getAmount()
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            return new JsonResponse(
                ['error' => 'An error occurred while processing your request: ' . $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
