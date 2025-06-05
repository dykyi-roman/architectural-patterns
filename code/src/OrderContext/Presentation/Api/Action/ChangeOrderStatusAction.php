<?php

declare(strict_types=1);

namespace OrderContext\Presentation\Api\Action;

use OrderContext\Application\UseCases\ChangeOrderStatus\Command\ChangeOrderStatusCommand;
use OrderContext\DomainModel\Enum\OrderStatus;
use OrderContext\DomainModel\ValueObject\OrderId;
use OrderContext\Presentation\Api\Request\ChangeOrderStatusRequest;
use OrderContext\Presentation\Api\Response\ChangeOrderStatusResponse;
use Shared\Application\Service\ApplicationService;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/v1', name: 'api_orders_')]
final readonly class ChangeOrderStatusAction
{
    public function __construct(
        private ApplicationService $applicationService,
    ) {
    }

    #[Route('/orders/{orderId}/status', name: 'change_status', methods: ['PATCH'])]
    public function __invoke(
        string $orderId,
        #[MapRequestPayload] ChangeOrderStatusRequest $request,
    ): ChangeOrderStatusResponse {
        $this->applicationService->command(
            new ChangeOrderStatusCommand(
                OrderId::fromString($orderId),
                OrderStatus::from($request->status)
            ),
        );

        return new ChangeOrderStatusResponse($orderId, $request->status);
    }
}
