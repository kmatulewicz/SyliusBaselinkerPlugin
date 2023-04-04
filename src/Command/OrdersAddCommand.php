<?php

declare(strict_types=1);

namespace SyliusBaselinkerPlugin\Command;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use SyliusBaselinkerPlugin\Entity\OrderInterface;
use SyliusBaselinkerPlugin\Service\OrdersApiServiceInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class OrdersAddCommand extends Command
{
    private OrderRepositoryInterface $orderRepository;

    private OrdersApiServiceInterface $orderApi;

    private EntityManagerInterface $entityManager;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        OrdersApiServiceInterface $orderApi,
        EntityManagerInterface $entityManager,
    ) {
        $this->orderRepository = $orderRepository;
        $this->orderApi = $orderApi;
        $this->entityManager = $entityManager;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('baselinker:orders:add');
        $this->setDescription('Adds not synchronized shop orders to Baselinker.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @todo: Log */
        /** @todo: --quiet */
        /** @todo: Custom query */
        $orders = $this->orderRepository->findAllExceptCarts();
        $output->writeln('Adding orders to Baselinker:');

        /** @var OrderInterface $order */
        foreach ($orders as $order) {
            if (false === $this->isOrderApplicableForSync($order)) {
                continue;
            }

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
                } finally {
                    if (null === $exception) {
                        $message = 'Order ' . (string) $order->getId() .
                            ' successfully exported to Baselinker on id: ' .
                            $order->getBaselinkerId();
                        $output->writeln($message);
                    } else {
                        $message = 'Order ' . (string) $order->getId() . ' ' . $exception->getMessage();
                        $output->writeln($message);
                        $output->writeln('Aborting');

                        return Command::FAILURE;
                    }
                }
            }
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
