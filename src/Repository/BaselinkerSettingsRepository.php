<?php

declare(strict_types=1);

namespace SyliusBaselinkerPlugin\Repository;

use Doctrine\ORM\QueryBuilder;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

class BaselinkerSettingsRepository extends EntityRepository
{
    public function findAllSettings(): QueryBuilder
    {
        $query = $this->createQueryBuilder('s')->select();

        return $query;
    }
}
