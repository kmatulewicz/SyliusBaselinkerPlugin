<?php

declare(strict_types=1);

namespace SyliusBaselinkerPlugin\Command;

use Doctrine\ORM\EntityManagerInterface;
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

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        OrdersApiServiceInterface $orderApi,
        EntityManagerInterface $entityManager,
        OrderStatusApplierInterface $statusApplier,
    ) {
        $this->orderRepository = $orderRepository;
        $this->orderApi = $orderApi;
        $this->entityManager = $entityManager;
        $this->statusApplier = $statusApplier;

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
        /** @todo: Log */
        /** @todo: --quiet */
        /** @todo: Rethink consistency: payment -> status change on Baselinker -> status change in shop */
        $output->writeln('Synchronizing statuses:');
        /** @var Settings|null $lastJournalIdSetting */
        $lastJournalIdSetting = $this->entityManager->getRepository(Settings::class)->find('last.journal.id');
        $lastJournalId = 0;
        if (is_object($lastJournalIdSetting)) {
            $lastJournalId = (int) $lastJournalIdSetting->getValue();
        } else {
            $lastJournalIdSetting = new Settings('last.journal.id');
        }

        $journal = $this->orderApi->getJournalList($lastJournalId, [18]);

        /** @var array<string, int> $entry */
        foreach ($journal as $entry) {
            /** @var OrderInterface|null $order */
            $order = $this->orderRepository->findOneBy(['baselinkerId' => $entry['order_id']]);
            if (!is_object($order)) {
                continue;
            }
            if ($order->getBaselinkerUpdateTime() > $entry['date']) {
                continue;
            }

            $output->write('Order ' . (string) $order->getId() . ': ');

            $this->statusApplier->apply($order, $entry['log_type'], $entry['object_id']);

            $output->writeln('updated ');

            $order->setBaselinkerUpdateTime(time());

            $lastJournalIdSetting->setValue((string) $entry['log_id']);

            $this->entityManager->persist($lastJournalIdSetting);
            $this->entityManager->persist($order);
            $this->entityManager->flush();
        }

        $output->writeln('Done');

        return Command::SUCCESS;
    }
}
