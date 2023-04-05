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

class OrdersPaymentsCommand extends Command
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
        $this->setName('baselinker:orders:payments');
        $this->setDescription('Adds to Baselinker payments done in shop after last synchronization.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @todo: Log */
        /** @todo: --quiet */
        /** @todo: Rethink consistency: payment -> status change on Baselinker -> status change in shop */
        $orders = $this->orderRepository->findOrdersForUpdate();
        $output->writeln('Adding payments to Baselinker:');

        /** @var OrderInterface $order */
        foreach ($orders as $order) {
            $isPaid = ('paid' === $order->getPaymentState()) ? true : false;
            $payment = $order->getLastPayment();
            $paymentTime = (null === $payment) ? null : $payment->getUpdatedAt();
            $paymentTimestamp = (null === $paymentTime) ? 0 : $paymentTime->getTimestamp();
            if ($isPaid && ($paymentTimestamp > $order->getBaselinkerUpdateTime())) {
                $exception = null;

                try {
                    $this->orderApi->setOrderPayment($order);
                    $order->setBaselinkerUpdateTime(time());
                    $this->entityManager->persist($order);
                    $this->entityManager->flush();
                } catch (Exception $e) {
                    $exception = $e;
                } finally {
                    if (null === $exception) {
                        $message = 'Payment for order ' . (string) $order->getId() .
                            ' successfully exported to Baselinker';
                        $output->writeln($message);
                    } else {
                        $message = 'Payment for order ' . (string) $order->getId() . ' ' . $exception->getMessage();
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
}
