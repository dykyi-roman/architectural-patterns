<?php

declare(strict_types=1);

namespace OrderContext\Presentation\Api\Action;

use OrderContext\Application\UseCases\CreateOrder\Command\CreateOrderCommand;
use OrderContext\DomainModel\ValueObject\CustomerId;
use OrderContext\DomainModel\ValueObject\OrderId;
use OrderContext\Presentation\Api\Request\CreateOrderRequest;
use OrderContext\Presentation\Api\Response\CreateOrderResponse;
use Shared\Application\Service\ApplicationService;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/v1', name: 'api_orders_')]
final readonly class CreateOrderAction
{
    public function __construct(
        private ApplicationService $applicationService,
    ) {
    }

    #[Route('/orders', name: 'create_order', methods: ['POST'])]
    public function __invoke(
        #[MapRequestPayload] CreateOrderRequest $request,
    ): CreateOrderResponse {
        $this->applicationService->command(
            new CreateOrderCommand(
                OrderId::generate(),
                CustomerId::fromString($request->customerId),
                ...$request->getItems(),
            ),
        );

        return new CreateOrderResponse();
    }
}
