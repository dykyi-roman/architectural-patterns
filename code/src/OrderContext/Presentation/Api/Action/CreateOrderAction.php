<?php

declare(strict_types=1);

namespace OrderContext\Presentation\Api\Action;

use OrderContext\Application\Service\OrderApplicationService;
use OrderContext\Application\UseCases\CreateOrder\Command\CreateOrderCommand;
use OrderContext\DomainModel\ValueObject\CustomerId;
use OrderContext\DomainModel\ValueObject\OrderId;
use OrderContext\Presentation\Api\Request\CreateOrderRequest;
use OrderContext\Presentation\Api\Response\CreateOrderResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

final readonly class CreateOrderAction
{
    public function __construct(
        private OrderApplicationService $applicationService,
    ) {
    }

    #[Route('/api/orders', methods: ['POST'])]
    public function __invoke(
        #[MapRequestPayload] CreateOrderRequest $request,
    ): CreateOrderResponse {
        $this->applicationService->execute(
            new CreateOrderCommand(
                OrderId::generate(),
                CustomerId::fromString($request->customerId),
                $request->getItems(),
            ),
        );

        return new CreateOrderResponse();
    }
}
