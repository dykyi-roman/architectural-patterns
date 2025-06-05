<?php

declare(strict_types=1);

namespace OrderContext\Presentation\Api\Action;

use OrderContext\Application\UseCases\GetOrder\Query\GetOrderQuery;
use OrderContext\DomainModel\ValueObject\OrderId;
use OrderContext\Presentation\Api\Response\OrderResponse;
use Shared\Application\Service\ApplicationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/v1', name: 'api_orders_')]
final class GetOrderAction extends AbstractController
{
    public function __construct(
        private readonly ApplicationService $applicationService,
    ) {
    }

    #[Route('/orders/{orderId}', name: 'get_order', methods: ['GET'])]
    public function __invoke(string $orderId): JsonResponse
    {
        $query = new GetOrderQuery(OrderId::fromString($orderId));

        $orderData = $this->applicationService->query($query);
        if (null === $orderData) {
            return new JsonResponse(['error' => 'Order not foud'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(OrderResponse::fromReadModel($orderData), Response::HTTP_OK);
    }
}
