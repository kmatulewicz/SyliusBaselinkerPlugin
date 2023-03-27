<?php

declare(strict_types=1);

namespace SyliusBaselinkerPlugin\Factory;

use Doctrine\ORM\EntityManagerInterface;
use SyliusBaselinkerPlugin\Entity\BaselinkerSettings;
use SyliusBaselinkerPlugin\Entity\BaselinkerStatusesAssociations;

class BaselinkerFirstRunFactory
{
    public static function createOnFirstRun(EntityManagerInterface $entityManager): void
    {
        $defaultSettings = BaselinkerSettings::getDefaultSettings();

        /** @var string $setting */
        foreach ($defaultSettings as $setting) {
            $baselinkerSetting = new BaselinkerSettings($setting);
            $entityManager->persist($baselinkerSetting);
        }

        $defaultStatuses = BaselinkerStatusesAssociations::getDefaultStatuses();
        /** @var string $status */
        foreach ($defaultStatuses as $status) {
            $existingStatus = $entityManager->find(BaselinkerStatusesAssociations::class, $status);
            if (null === $existingStatus) {
                $baselinkerStatus = new BaselinkerStatusesAssociations($status);
                $entityManager->persist($baselinkerStatus);
            }
        }

        $entityManager->flush();
    }
}
