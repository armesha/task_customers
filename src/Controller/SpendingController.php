<?php

namespace App\Controller;

use App\Service\SpendingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

// API kontroler pro spravu vydaju
#[Route('/api/users/{id}/spendings')]
class SpendingController extends AbstractController
{
    public function __construct(
        private SpendingService $spendingService
    ) {}

    // POST - pridat novy vydaj pro zakaznika
    // params: date, amount
    #[Route('', methods: ['POST'])]
    public function create(int $id, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            if (!isset($data['date']) || !isset($data['amount'])) {
                return new JsonResponse(['error' => 'Date and amount are required'], Response::HTTP_BAD_REQUEST);
            }

            try {
                $spending = $this->spendingService->createSpending(
                    customerId: $id,
                    date: $data['date'],
                    amount: (float)$data['amount']
                );
                return new JsonResponse($spending, Response::HTTP_CREATED);
            } catch (\InvalidArgumentException $e) {
                return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
            }

        } catch (\Exception $e) {
            return new JsonResponse(
                ['error' => 'An error occurred while processing your request: ' . $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
