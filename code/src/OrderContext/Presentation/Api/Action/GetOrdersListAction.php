<?php

declare(strict_types=1);

namespace OrderContext\Presentation\Api\Action;

use OrderContext\Application\UseCases\GetOrdersList\Query\GetOrdersListQuery;
use OrderContext\Presentation\Api\Response\GetOrdersListResponse;
use Shared\Application\Service\ApplicationService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/v1', name: 'api_orders_')]
final readonly class GetOrdersListAction
{
    public function __construct(
        private ApplicationService $applicationService,
    ) {
    }

    #[Route('/orders', name: 'order_list', methods: ['GET'])]
    public function __invoke(Request $request): GetOrdersListResponse
    {
        $page = (int) $request->query->get('page', 1);
        $limit = (int) $request->query->get('limit', 20);
        $status = $request->query->get('status');
        $customerId = $request->query->get('customer_id');
        $sortBy = $request->query->get('sort_by', 'created_at');
        $sortDirection = $request->query->get('sort_direction', 'desc');

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
                $sortDirection,
            ),
        );

        return new GetOrdersListResponse(
            $result['items'],
            $result['total'],
            $result['page'],
            $result['limit'],
        );
    }
}
