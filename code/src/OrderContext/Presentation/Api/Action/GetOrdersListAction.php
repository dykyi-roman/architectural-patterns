<?php

declare(strict_types=1);

namespace OrderContext\Presentation\Api\Action;

use OrderContext\Application\Service\OrderApplicationService;
use OrderContext\Application\UseCases\GetOrdersList\Query\GetOrdersListQuery;
use OrderContext\Presentation\Api\Response\OrderResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GetOrdersListAction extends AbstractController
{
    public function __construct(
        private readonly OrderApplicationService $applicationService
    ) {
    }

    #[Route('/api/orders', methods: ['GET'])]
    public function __invoke(Request $request): JsonResponse
    {
        // Get query parameters
        $filters = [];

        // Apply customer filter if provided
        if ($customerId = $request->query->get('customer_id')) {
            $filters['customer_id'] = $customerId;
        }

        // Apply status filter if provided
        if ($status = $request->query->get('status')) {
            $filters['status'] = $status;
        }

        // Set up pagination
        $page = max(1, (int)$request->query->get('page', 1));
        $limit = max(1, min(100, (int)$request->query->get('limit', 20)));

        // Apply sorting if provided
        $sortBy = $request->query->get('sort_by');
        $sortDirection = strtolower($request->query->get('sort_direction', 'desc'));
        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'desc';
        }

        // Create query for retrieving orders list
        $query = new GetOrdersListQuery(
            $filters,
            $page,
            $limit,
            $sortBy,
            $sortDirection
        );

        // Execute query via application service
        $result = $this->applicationService->query($query);

        // Transform read model data to response DTOs
        $ordersResponse = array_map(
            fn(array $orderData) => OrderResponse::fromReadModel($orderData),
            $result['items']
        );

        // Return orders list with pagination
        return new JsonResponse([
            'orders' => $ordersResponse,
            'pagination' => [
                'total' => $result['total'],
                'page' => $result['page'],
                'limit' => $result['limit'],
                'pages' => ceil($result['total'] / $result['limit']),
            ],
        ], Response::HTTP_OK);
    }
}
