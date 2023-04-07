<?php

declare(strict_types=1);

namespace SyliusBaselinkerPlugin\Command;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use SyliusBaselinkerPlugin\Entity\OrderInterface;
use SyliusBaselinkerPlugin\Entity\Settings;
use SyliusBaselinkerPlugin\Service\OrdersApiServiceInterface;
use SyliusBaselinkerPlugin\Service\OrderStatusApplierInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class OrdersStatusCommand extends Command
{
    private OrderRepositoryInterface $orderRepository;

    private OrdersApiServiceInterface $orderApi;

    private EntityManagerInterface $entityManager;

    private OrderStatusApplierInterface $statusApplier;

    private LoggerInterface $logger;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        OrdersApiServiceInterface $orderApi,
        EntityManagerInterface $entityManager,
        OrderStatusApplierInterface $statusApplier,
        LoggerInterface $logger,
    ) {
        $this->orderRepository = $orderRepository;
        $this->orderApi = $orderApi;
        $this->entityManager = $entityManager;
        $this->statusApplier = $statusApplier;
        $this->logger = $logger;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('baselinker:orders:statuses');
        $this->setDescription('Checks for status changes of synchronized orders. ' .
            'Applies shop order status change to corresponding Baselinker status.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @todo: --quiet */
        $this->logger->debug('Command baselinker:orders:statuses executed.');
        $output->writeln('Synchronizing statuses:');
        /** @var Settings|null $lastJournalIdSetting */
        $lastJournalIdSetting = $this->entityManager->getRepository(Settings::class)->find('last.journal.id');
        $lastJournalId = 0;
        if (is_object($lastJournalIdSetting)) {
            $lastJournalId = (int) $lastJournalIdSetting->getValue() + 1;
        } else {
            $lastJournalIdSetting = new Settings('last.journal.id');
        }

        $this->logger->debug(sprintf('Get Baselinker journal from %d.', $lastJournalId));

        try {
            $journal = $this->orderApi->getJournalList($lastJournalId, [4, 18]);
        } catch (Exception $exception) {
            $message = 'Cannot get Baselinker journal: ' . $exception->getMessage();
            $this->logger->error($message);
            $output->writeln($message);
            $output->writeln('Aborting');

            return Command::FAILURE;
        }
        $this->logger->debug(sprintf('Journal has %d positions.', count($journal)));

        /** @var array<string, int> $entry */
        foreach ($journal as $entry) {
            /** @var OrderInterface|null $order */
            $order = $this->orderRepository->findOneBy(['baselinkerId' => $entry['order_id']]);
            if (!is_object($order)) {
                $this->logger->debug(sprintf('Baselinker order %d not found. Omitting.', $entry['order_id']));
                $lastJournalIdSetting->setValue((string) $entry['log_id']);

                continue;
            }

            $output->write('Order ' . (string) $order->getId() . ': ');
            $result = $this->statusApplier->apply($order, $entry['log_type'], $entry['object_id']);
            if (true === $result) {
                $order->setBaselinkerUpdateTime(time());
                $this->entityManager->persist($order);
                $this->logger->debug(sprintf('Status of order %d updated.', (int) $order->getId()));
                $output->writeln('updated ');
            } else {
                $this->logger->notice(
                    sprintf('Status of order %d cannot be changed. Omitting.', (int) $order->getId()),
                    [
                        'Shop order state' => $order->getState(),
                        'Baselinker status id' => $entry['object_id'],
                    ],
                );
                $output->writeln('omitted ');
            }
            $lastJournalIdSetting->setValue((string) $entry['log_id']);
        }

        $this->entityManager->persist($lastJournalIdSetting);
        $this->entityManager->flush();
        $this->logger->debug('Last journal id set to: ' . $lastJournalIdSetting->getValue());

        $output->writeln('Done');

        return Command::SUCCESS;
    }
}
