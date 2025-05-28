<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Transaction;

use Doctrine\DBAL\Connection;
use Shared\DomainModel\Service\TransactionServiceInterface;

final readonly class DoctrineTransactionService implements TransactionServiceInterface
{
    public function __construct(
        private Connection $connection,
    ) {
    }

    /**
     * @throws \Throwable
     */
    public function execute(callable $callback): mixed
    {
        // Checking if we are already in a transaction
        $isAlreadyInTransaction = $this->connection->isTransactionActive();
        if (!$isAlreadyInTransaction) {
            $this->connection->beginTransaction();
        }
        
        try {
            $result = $callback();

            if (!$isAlreadyInTransaction) {
                $this->connection->commit();
            }
            
            return $result;
        } catch (\Throwable $exception) {
            // If we are not in a transaction, rollback
            if (!$isAlreadyInTransaction && $this->connection->isTransactionActive()) {
                $this->connection->rollBack();
            }
            
            throw $exception;
        }
    }
}
