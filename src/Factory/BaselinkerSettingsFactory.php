<?php

declare(strict_types=1);

namespace SyliusBaselinkerPlugin\Factory;

use Doctrine\ORM\EntityManagerInterface;
use SyliusBaselinkerPlugin\Entity\BaselinkerSettings;

class BaselinkerSettingsFactory
{
    public static function createSettingsOnFirstRun(EntityManagerInterface $entityManager): void
    {
        $defaultSettings = BaselinkerSettings::getDefaultSettings();

        /** @var string $setting */
        foreach ($defaultSettings as $setting) {
            $baselinkerSetting = new BaselinkerSettings($setting);
            $entityManager->persist($baselinkerSetting);
        }

        $entityManager->flush();
    }
}
