<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Repository\CustomerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/users')]
class CustomerController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CustomerRepository $customerRepository,
        private ValidatorInterface $validator
    ) {}

    #[Route('', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        try {
            $qb = $this->customerRepository->createQueryBuilder('c')
                ->select('c.id, c.name')
                ->addSelect('COALESCE(SUM(s.amount), 0) as total_spent')
                ->leftJoin('c.spendings', 's')
                ->groupBy('c.id, c.name');

            // Apply name filter
            if ($name = $request->query->get('name')) {
                $qb->andWhere('c.name LIKE :name')
                   ->setParameter('name', '%' . $name . '%');
            }

            // Apply total spent filters
            if ($minTotalSpent = $request->query->get('min_total_spent')) {
                $qb->having('total_spent >= :minTotalSpent')
                   ->setParameter('minTotalSpent', (float)$minTotalSpent);
            }

            if ($maxTotalSpent = $request->query->get('max_total_spent')) {
                $qb->having('total_spent <= :maxTotalSpent')
                   ->setParameter('maxTotalSpent', (float)$maxTotalSpent);
            }

            // Apply sorting
            $sortBy = $request->query->get('sort_by', 'name');
            $sortOrder = strtoupper($request->query->get('sort_order', 'asc'));

            // Validate sort order
            if (!in_array($sortOrder, ['ASC', 'DESC'])) {
                $sortOrder = 'ASC';
            }

            // Validate sort by
            if (!in_array($sortBy, ['name', 'total_spent'])) {
                $sortBy = 'name';
            }

            // Use the alias 'total_spent' that we created in the SELECT statement
            if ($sortBy === 'total_spent') {
                $qb->orderBy('total_spent', $sortOrder);
            } else {
                $qb->orderBy('c.name', $sortOrder);
            }

            $results = $qb->getQuery()->getArrayResult();

            // Format the results
            $customers = array_map(function ($row) {
                return [
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'total_spent' => (float)$row['total_spent']
                ];
            }, $results);

            return new JsonResponse($customers);

        } catch (\Exception $e) {
            return new JsonResponse(
                ['error' => 'An error occurred while fetching customers: ' . $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (!isset($data['name'])) {
                return new JsonResponse(['error' => 'Name is required'], Response::HTTP_BAD_REQUEST);
            }

            $customer = new Customer();
            $customer->setName($data['name']);

            $errors = $this->validator->validate($customer);
            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[] = $error->getMessage();
                }
                return new JsonResponse(['error' => $errorMessages], Response::HTTP_BAD_REQUEST);
            }

            $this->entityManager->persist($customer);
            $this->entityManager->flush();

            return new JsonResponse([
                'id' => $customer->getId(),
                'name' => $customer->getName(),
                'total_spent' => 0
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            return new JsonResponse(
                ['error' => 'An error occurred while creating customer: ' . $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
