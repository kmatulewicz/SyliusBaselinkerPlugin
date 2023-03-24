<?php

declare(strict_types=1);

namespace SyliusBaselinkerPlugin\Repository;

use Doctrine\ORM\QueryBuilder;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use SyliusBaselinkerPlugin\Factory\BaselinkerSettingsFactory;

class BaselinkerSettingsRepository extends EntityRepository
{
    public function findAllSettings(): QueryBuilder
    {
        $query = $this->createQueryBuilder('s')->select();
        /** @var array<int, string> $result */
        $result = $query->getQuery()->execute();
        if (count($result) === 0) {
            BaselinkerSettingsFactory::createSettingsOnFirstRun($this->_em);
        }

        return $query;
    }
}
