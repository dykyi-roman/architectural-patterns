<?php

declare(strict_types=1);

namespace OrderContext\Presentation\Console;

use OrderContext\Infrastructure\Outbox\OutboxEventProcessor;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'order:process-outbox',
    description: 'Process outbox events and publish them to message broker'
)]
final class ProcessOutboxEventsCommand extends Command
{
    /**
     * @param OutboxEventProcessor $outboxProcessor
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly OutboxEventProcessor $outboxProcessor,
        private readonly LoggerInterface $logger,
    ) {
        parent::__construct();
    }

    /**
     * Конфигурирует опции команды
     *
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->addOption(
                'batch-size',
                'b',
                InputOption::VALUE_OPTIONAL,
                'Number of events to process in one batch',
                100
            )
            ->addOption(
                'iterations',
                'i',
                InputOption::VALUE_OPTIONAL,
                'Number of iterations to run (0 for infinite)',
                1
            )
            ->addOption(
                'delay',
                'd',
                InputOption::VALUE_OPTIONAL,
                'Delay in seconds between iterations',
                5
            );
    }

    /**
     * Выполняет команду обработки событий Outbox
     *
     * @param InputInterface $input Интерфейс ввода
     * @param OutputInterface $output Интерфейс вывода
     * @return int Код завершения
     * 
     * @throws \Exception При возникновении ошибки в процессе обработки
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $batchSize = (int)$input->getOption('batch-size');
        $iterations = (int)$input->getOption('iterations');
        $delay = (int)$input->getOption('delay');

        $output->writeln('<info>Starting outbox processing...</info>');
        $this->logger->info('Starting outbox event processing', [
            'batch_size' => $batchSize,
            'iterations' => $iterations,
            'delay' => $delay,
        ]);

        $totalProcessed = 0;
        $iteration = 0;

        try {
            do {
                $iteration++;
                $output->writeln(sprintf('<comment>Processing batch %d...</comment>', $iteration));

                $processed = $this->outboxProcessor->processOutboxEvents($batchSize);
                $totalProcessed += $processed;

                $output->writeln(sprintf('<info>Processed %d events in batch %d</info>', $processed, $iteration));
                $this->logger->info('Processed events batch', [
                    'batch' => $iteration,
                    'processed' => $processed,
                    'total_processed' => $totalProcessed,
                ]);

                // Если нет событий и команда запущена в бесконечном режиме, делаем паузу
                if ($processed === 0 && ($iterations === 0 || $iteration < $iterations)) {
                    $output->writeln(sprintf('<comment>No events to process, waiting for %d seconds...</comment>', $delay));
                    sleep($delay);
                }
            } while ($iterations === 0 || $iteration < $iterations);

            $output->writeln(sprintf('<info>Outbox processing completed. Total processed: %d events</info>', $totalProcessed));
            $this->logger->info('Outbox event processing completed', ['total_processed' => $totalProcessed]);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>Error processing outbox events: %s</error>', $e->getMessage()));
            $this->logger->error('Error processing outbox events', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return Command::FAILURE;
        }
    }
}
