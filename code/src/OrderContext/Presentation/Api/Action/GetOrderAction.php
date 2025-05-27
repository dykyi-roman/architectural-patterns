<?php

declare(strict_types=1);

namespace OrderContext\Presentation\Api\Action;

use OrderContext\Application\Service\OrderApplicationService;
use OrderContext\Application\UseCases\GetOrder\Query\GetOrderQuery;
use OrderContext\DomainModel\ValueObject\OrderId;
use OrderContext\Presentation\Api\Response\OrderResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GetOrderAction extends AbstractController
{
    public function __construct(
        private readonly OrderApplicationService $applicationService
    ) {
    }

    #[Route('/api/orders/{orderId}', methods: ['GET'])]
    public function __invoke(string $orderId): JsonResponse
    {
        try {
            // Create query for retrieving order data
            $query = new GetOrderQuery(new OrderId($orderId));
            
            // Execute query via application service
            $orderData = $this->applicationService->query($query);
            
            if ($orderData === null) {
                return new JsonResponse(['error' => 'Заказ не найден'], Response::HTTP_NOT_FOUND);
            }
            
            // Transform read model data to response DTO
            $orderResponse = OrderResponse::fromReadModel($orderData);
            
            // Return order data
            return new JsonResponse($orderResponse, Response::HTTP_OK);
        } catch (\InvalidArgumentException $e) {
            // Validation errors
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            // Internal server errors
            return new JsonResponse(
                ['error' => 'Произошла внутренняя ошибка сервера'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
