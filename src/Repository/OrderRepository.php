<?php

declare(strict_types=1);

namespace SyliusBaselinkerPlugin\Repository;

use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use SyliusBaselinkerPlugin\Entity\OrderInterface;

class OrderRepository implements OrderRepositoryInterface
{
    private EntityRepository $baseRepository;

    public function __construct(EntityRepository $baseRepository)
    {
        $this->baseRepository = $baseRepository;
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
            ->andWhere('o.baselinkerId != 0')
            ->setParameter('state', OrderInterface::STATE_CART)
        ;

        return $qb->getQuery()->getArrayResult();
    }
}
