<?php

declare(strict_types=1);

namespace OrderContext\Presentation\Api\Action;

use OrderContext\Application\Service\OrderApplicationService;
use OrderContext\Application\UseCases\ChangeOrderStatus\Command\ChangeOrderStatusCommand;
use OrderContext\DomainModel\ValueObject\OrderId;
use OrderContext\DomainModel\ValueObject\OrderStatus;
use OrderContext\Presentation\Api\Request\ChangeOrderStatusRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

final class ChangeOrderStatusAction extends AbstractController
{
    public function __construct(
        private readonly OrderApplicationService $applicationService,
    ) {
    }

    #[Route('/api/orders/{orderId}/status', methods: ['PATCH'])]
    public function __invoke(
        string $orderId,
        #[MapRequestPayload] ChangeOrderStatusRequest $request
    ): JsonResponse {
        $this->applicationService->execute(
            new ChangeOrderStatusCommand(
                OrderId::fromString($orderId),
                OrderStatus::from($request->status)
            ),
        );

        return new JsonResponse(['message' => 'Статус заказа успешно обновлен'], Response::HTTP_OK);
    }
}
