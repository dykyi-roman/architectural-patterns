<?php

declare(strict_types=1);

namespace OrderContext\Presentation\Api\Action;

use OrderContext\Application\Service\OrderApplicationService;
use OrderContext\Application\UseCases\CreateOrder\Command\CreateOrderCommand;
use OrderContext\DomainModel\ValueObject\CustomerId;
use OrderContext\Presentation\Api\Request\CreateOrderRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

final class CreateOrderAction extends AbstractController
{
    public function __construct(
        private readonly OrderApplicationService $applicationService
    ) {
    }

    #[Route('/api/orders', methods: ['POST'])]
    public function __invoke(
        #[MapRequestPayload] CreateOrderRequest $request,
    ): JsonResponse {
        dump($request); die();  
        $this->applicationService->execute(
            new CreateOrderCommand(
                CustomerId::fromString($request->customerId),
                $request->getItems(),
            ),
        );

        return new JsonResponse(['message' => 'Заказ успешно создан'], Response::HTTP_CREATED);
    }
}
