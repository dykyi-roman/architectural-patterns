<?php

declare(strict_types=1);

namespace OrderContext\Presentation\Api\Action;

use OrderContext\Application\UseCases\ChangeOrderStatus\Command\ChangeOrderStatusCommand;
use OrderContext\DomainModel\ValueObject\OrderId;
use OrderContext\DomainModel\ValueObject\OrderStatus;
use OrderContext\Presentation\Api\Request\ChangeOrderStatusRequest;
use Shared\Application\Service\ApplicationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/v1', name: 'api_orders_')]
final class ChangeOrderStatusAction extends AbstractController
{
    public function __construct(
        private readonly ApplicationService $applicationService,
    ) {
    }

    #[Route('/orders/{orderId}/status', name: 'change_status', methods: ['PATCH'])]
    public function __invoke(
        string $orderId,
        #[MapRequestPayload] ChangeOrderStatusRequest $request,
    ): JsonResponse {
        $this->applicationService->command(
            new ChangeOrderStatusCommand(
                OrderId::fromString($orderId),
                OrderStatus::from($request->status)
            ),
        );

        return new JsonResponse(['message' => 'Order status updated successfully'], Response::HTTP_OK);
    }
}
