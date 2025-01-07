<?php

namespace App\Controller;

use App\Service\CustomerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

// API kontroler pro zakazniky
#[Route('/api/users')]
class CustomerController extends AbstractController
{
    public function __construct(
        private CustomerService $customerService
    ) {}

    // GET - ziskat seznam zakazniku
    // params: name, min_total_spent, max_total_spent, sort_by, sort_order
    #[Route('', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        try {
            $filters = [
                'name' => $request->query->get('name'),
                'min_total_spent' => $request->query->get('min_total_spent'),
                'max_total_spent' => $request->query->get('max_total_spent'),
                'sort_by' => $request->query->get('sort_by', 'name'),
                'sort_order' => $request->query->get('sort_order', 'asc')
            ];

            $customers = $this->customerService->getFilteredCustomers($filters);
            return new JsonResponse($customers);

        } catch (\Exception $e) {
            return new JsonResponse(
                ['error' => 'An error occurred while fetching customers: ' . $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    // POST - vytvorit noveho zakaznika
    // povinny param: name
    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (!isset($data['name'])) {
                return new JsonResponse(['error' => 'Name is required'], Response::HTTP_BAD_REQUEST);
            }

            try {
                $customer = $this->customerService->createCustomer($data['name']);
                return new JsonResponse($customer, Response::HTTP_CREATED);
            } catch (\InvalidArgumentException $e) {
                return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
            }

        } catch (\Exception $e) {
            return new JsonResponse(
                ['error' => 'An error occurred while creating customer: ' . $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
