<?php

declare(strict_types=1);

namespace OrderContext\Presentation\Api\Action;

use OrderContext\Application\UseCases\GetOrdersList\Query\GetOrdersListQuery;
use OrderContext\Presentation\Api\Response\OrderResponse;
use Shared\Application\Service\ApplicationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/v1', name: 'api_orders_')]
final class GetOrdersListAction extends AbstractController
{
    public function __construct(
        private readonly ApplicationService $applicationService,
    ) {
    }

    #[Route('/orders', name: 'order_list', methods: ['GET'])]
    public function __invoke(Request $request): JsonResponse
    {
        $page = (int) $request->query->get('page', 1);
        $limit = (int) $request->query->get('limit', 20);
        $status = $request->query->get('status');
        $customerId = $request->query->get('customer_id');
        $sortBy = $request->query->get('sort_by', 'created_at');
        $sortDirection = $request->query->get('sort_direction', 'desc');

        // Формируем фильтры
        $filters = [];
        if ($status) {
            $filters['status'] = $status;
        }
        if ($customerId) {
            $filters['customer_id'] = $customerId;
        }

        $result = $this->applicationService->query(
            new GetOrdersListQuery(
                $filters,
                $page,
                $limit,
                $sortBy,
                $sortDirection
            ),
        );

        $ordersResponse = array_map(
            fn (array $orderData) => OrderResponse::fromReadModel($orderData),
            $result['items']
        );

        return $this->json([
            'items' => $ordersResponse,
            'total' => $result['total'],
            'page' => $result['page'],
            'limit' => $result['limit'],
        ]);
    }
}
