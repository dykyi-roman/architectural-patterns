<?php

declare(strict_types=1);

namespace OrderContext\Presentation\Api\Action;

use OrderContext\Application\UseCases\GetOrderHistory\Dto\OrderHistoryDto;
use OrderContext\Application\UseCases\GetOrderHistory\Exception\HistoryNotFoundException;
use OrderContext\Application\UseCases\GetOrderHistory\Query\GetOrderHistoryQuery;
use OrderContext\DomainModel\ValueObject\OrderId;
use OrderContext\Presentation\Api\Response\GetOrderHistoryResponse;
use Shared\Application\Service\ApplicationService;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/v1', name: 'api_orders_')]
final readonly class GetOrderHistoryAction
{
    public function __construct(
        private ApplicationService $applicationService,
    ) {
    }

    #[Route('/orders/{orderId}/history', name: 'order_history', methods: ['GET'])]
    public function __invoke(string $orderId): GetOrderHistoryResponse
    {
        try {
            /** @var OrderHistoryDto $response */
            $response = $this->applicationService->query(
                new GetOrderHistoryQuery(OrderId::fromString($orderId)),
            );
        } catch (HistoryNotFoundException) {
            return new GetOrderHistoryResponse([]);
        }

        return new GetOrderHistoryResponse($response->jsonSerialize());
    }
}
