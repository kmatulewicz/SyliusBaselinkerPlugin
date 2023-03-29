<?php

declare(strict_types=1);

namespace SyliusBaselinkerPlugin\Resolver;

use Doctrine\ORM\EntityManagerInterface;
use SyliusBaselinkerPlugin\Entity\BaselinkerStatusesAssociations;

class BaselinkerStatusResolver implements BaselinkerStatusResolverInterface
{
    private EntityManagerInterface $entityManager;

    /** @var array<string, string> */
    private array $associations;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $as = $this->getAllAssociations();
        $this->associations = $this->associationsToArray($as);
    }

    public function resolveBaselinkerStatus(string $orderState): ?int
    {
        if (array_key_exists($orderState, $this->associations)) {
            return (int) $this->associations[$orderState];
        }

        return null;
    }

    public function resolveShopStatus(int $baselinkerStatus): ?string
    {
        $key = array_search((string) $baselinkerStatus, $this->associations, true);

        return (false === $key) ? null : $key;
    }

    /** @return array<int, BaselinkerStatusesAssociations> */
    private function getAllAssociations(): array
    {
        $repository = $this->entityManager->getRepository(BaselinkerStatusesAssociations::class);

        return $repository->findAll();
    }

    /**
     * @param array<int, BaselinkerStatusesAssociations> $associations
     *
     * @return array<string, string>
     */
    private function associationsToArray(array $associations): array
    {
        $associativeArray = [];
        foreach ($associations as $association) {
            $associativeArray[$association->getShopStatus()] = $association->getBaselinkerStatus();
        }

        return $associativeArray;
    }
}
