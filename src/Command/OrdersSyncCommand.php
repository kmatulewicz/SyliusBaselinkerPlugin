<?php

declare(strict_types=1);

namespace SyliusBaselinkerPlugin\Command;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use SyliusBaselinkerPlugin\Entity\OrderInterface;
use SyliusBaselinkerPlugin\Entity\Settings;
use SyliusBaselinkerPlugin\Service\OrdersApiServiceInterface;
use SyliusBaselinkerPlugin\Service\OrderStatusApplierInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class OrdersSyncCommand extends Command
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
        $this->setName('baselinker:orders:sync');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $orders = $this->orderRepository->findAllExceptCarts();
        $output->writeln('Orders synchronization:');

        /** @var OrderInterface $order */
        foreach ($orders as $order) {
            if (false === $this->isOrderApplicableForSync($order)) {
                continue;
            }

            //new order
            $exception = null;
            if (0 === $order->getBaselinkerId()) {
                try {
                    $baselinkerId = $this->orderApi->addOrder($order);
                    $order->setBaselinkerId($baselinkerId);
                    $order->setBaselinkerUpdateTime(time());
                    $this->entityManager->persist($order);
                    $this->entityManager->flush();
                } catch (Exception $e) {
                    $exception = $e;
                }
                $message = (null === $exception) ?
                    'Order ' . (string) $order->getId() . ' successfully exported to Baselinker on id: ' . $order->getBaselinkerId()
                    :
                    'Order ' . (string) $order->getId() . ' ' . $exception->getMessage();
                $output->writeln($message);
            }
        }

        /** @todo update existing order on Baselinker */
        /** @todo update order on Sylius */
        /** @var Settings|null $lastJournalIdSetting */
        $lastJournalIdSetting = $this->entityManager->getRepository(Settings::class)->find('last.journal.id');
        $lastJournalId = $lastJournalIdSetting ?  (int) $lastJournalIdSetting->getValue() : 0;
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
            $this->entityManager->persist($order);
            $this->entityManager->flush();
        }

        $output->writeln('Done');

        return Command::SUCCESS;
    }

    private function isOrderApplicableForSync(OrderInterface $order): bool
    {
        $orderUpdatedAt = $order->getUpdatedAt();
        if (null === $orderUpdatedAt) {
            return false;
        }
        if ($order->getBaselinkerUpdateTime() > $orderUpdatedAt->getTimestamp()) {
            return false;
        }

        return true;
    }
}
