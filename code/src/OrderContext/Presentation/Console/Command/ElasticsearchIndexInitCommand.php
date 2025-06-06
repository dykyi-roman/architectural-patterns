<?php

declare(strict_types=1);

namespace OrderContext\Presentation\Console\Command;

use OrderContext\DomainModel\Repository\OrderReadModelRepositoryInterface;
use OrderContext\Infrastructure\Persistence\Doctrine\Repository\ElasticsearchOrderReadModelRepository;
use Psr\Log\LoggerInterface;
use Shared\Presentation\Console\Command\AbstractConsoleCommand;
use Shared\Presentation\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:elasticsearch:init-indexes',
    description: 'Initialize Elasticsearch indexes if they do not exist'
)]
final class ElasticsearchIndexInitCommand extends AbstractConsoleCommand
{
    public function __construct(
        private readonly OrderReadModelRepositoryInterface $orderRepository,
        private readonly LoggerInterface $logger,
    ) {
        parent::__construct();
    }

    protected function executeCommand(InputInterface $input, OutputInterface $output): ConsoleOutput
    {
        try {
            $this->orderRepository->createIndex();
            $messages = [
                ConsoleOutput::formatMessage('Elasticsearch indexes have been successfully initialized', 'success'),
            ];

            return ConsoleOutput::success(
                $messages,
                'Elasticsearch Index Initialization',
                [],
                'Indexes initialized successfully'
            );
        } catch (\Throwable $e) {
            $this->logger->error(
                'Error initializing Elasticsearch indexes',
                ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]
            );

            $messages = [
                ConsoleOutput::formatMessage('Failed to initialize Elasticsearch indexes', 'error'),
                ConsoleOutput::formatMessage($e->getMessage(), 'error'),
            ];

            return ConsoleOutput::failure(
                $messages,
                'Elasticsearch Index Initialization',
                'Error initializing indexes'
            );
        }
    }
}
