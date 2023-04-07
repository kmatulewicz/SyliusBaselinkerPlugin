<?php

declare(strict_types=1);

namespace SyliusBaselinkerPlugin\Repository;

use DateTime;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Sylius\Component\Core\OrderPaymentStates;
use SyliusBaselinkerPlugin\Entity\OrderInterface;

class OrderRepository implements OrderRepositoryInterface
{
    private EntityRepository $baseRepository;

    private DateTime $dateLimit;

    private int $maxOrdersAdd;

    private int $maxOrdersPayments;

    public function __construct(
        EntityRepository $baseRepository,
        int $daysToSync = 14,
        int $maxOrdersAdd = 40,
        int $maxOrdersPayments = 40,
    ) {
        $this->baseRepository = $baseRepository;

        $this->dateLimit = new DateTime((string) (-$daysToSync) . ' days');
        $this->maxOrdersAdd = $maxOrdersAdd;
        $this->maxOrdersPayments = $maxOrdersPayments;
    }

    public function find(int $id): ?OrderInterface
    {
        /** @var OrderInterface|null $result */
        $result = $this->baseRepository->find($id);

        return $result;
    }

    public function findNewOrdersToAdd(): array
    {
        $qb = $this->baseRepository->createQueryBuilder('o');
        $qb
            ->select('o')
            ->where('o.state != :state')
            ->andWhere('o.baselinkerId = 0')
            ->andWhere('o.checkoutCompletedAt > :date_limit')
            ->setMaxResults($this->maxOrdersAdd)
            ->setParameter('state', OrderInterface::STATE_CART)
            ->setParameter('date_limit', $this->dateLimit)
        ;
        /** @var array<int, OrderInterface> $result */
        $result = $qb->getQuery()->getResult();

        return $result;
    }

    public function findOrdersForUpdate(): array
    {
        $qb = $this->baseRepository->createQueryBuilder('o');
        $qb
            ->select('o')
            ->where('o.paymentState = :state')
            ->andWhere('o.baselinkerId > 0')
            ->andWhere('o.baselinkerUpdateTime < o.updatedAt')
            ->andWhere('o.checkoutCompletedAt > :date_limit')
            ->setMaxResults($this->maxOrdersPayments)
            ->setParameter('state', OrderPaymentStates::STATE_PAID)
            ->setParameter('date_limit', $this->dateLimit)
        ;
        /** @var array<int, OrderInterface> $result */
        $result = $qb->getQuery()->getResult();

        return $result;
    }
}
