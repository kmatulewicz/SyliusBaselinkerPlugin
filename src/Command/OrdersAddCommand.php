<?php

declare(strict_types=1);

namespace SyliusBaselinkerPlugin\Command;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use SyliusBaselinkerPlugin\Entity\OrderInterface;
use SyliusBaselinkerPlugin\Repository\OrderRepositoryInterface;
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
        $orders = $this->orderRepository->findNewOrdersToAdd();
        $output->writeln('Adding orders to Baselinker:');

        /** @var OrderInterface $order */
        foreach ($orders as $order) {
            $exception = null;

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
        $output->writeln('Done');

        return Command::SUCCESS;
    }
}
