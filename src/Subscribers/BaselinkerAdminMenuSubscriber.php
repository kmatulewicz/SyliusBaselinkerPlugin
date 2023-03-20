<?php

declare(strict_types=1);

namespace SyliusBaselinkerPlugin\Subscribers;

use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;

final class BaselinkerAdminMenuSubscriber
{
    public function addAdminMenuEntries(MenuBuilderEvent $event): void
    {
        $menu = $event->getMenu();

        $submenu = $menu->addChild('new')->setLabel('Baselinker');

        $submenu->addChild('sub-item', ['route' => 'baselinker_admin_settings_index'])->setLabel('Settings');
    }
}
