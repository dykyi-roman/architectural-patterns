<?php

declare(strict_types=1);

namespace OrderContext\Presentation\Api\Action;

use OrderContext\Application\UseCases\GetOrder\Query\GetOrderQuery;
use OrderContext\DomainModel\ValueObject\OrderId;
use OrderContext\Presentation\Api\Response\GetOrderResponse;
use OrderContext\Presentation\Api\Response\OrderResponse;
use Shared\Application\Service\ApplicationService;
use Shared\Presentation\Responder\NotFoundResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/v1', name: 'api_orders_')]
final readonly class GetOrderAction
{
    public function __construct(
        private ApplicationService $applicationService,
    ) {
    }

    #[Route('/orders/{orderId}', name: 'get_order', methods: ['GET'])]
    public function __invoke(string $orderId): GetOrderResponse|NotFoundResponse
    {
        $query = new GetOrderQuery(OrderId::fromString($orderId));

        $orderData = $this->applicationService->query($query);
        if (null === $orderData) {
            return new NotFoundResponse(['error' => 'Order not found']);
        }

        return new GetOrderResponse(OrderResponse::fromReadModel($orderData));
    }
}
