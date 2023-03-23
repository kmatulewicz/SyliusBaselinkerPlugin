<?php

declare(strict_types=1);

namespace SyliusBaselinkerPlugin\Subscribers;

use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;

final class BaselinkerAdminMenuSubscriber
{
    public function addAdminMenuEntries(MenuBuilderEvent $event): void
    {
        $menu = $event->getMenu();

        $submenu = $menu->addChild('baselinker')->setLabel('Baselinker');

        $submenu->addChild('settings', ['route' => 'baselinker_admin_settings_index'])->setLabel('Settings');
        $submenu->addChild('statuses', ['route' => 'baselinker_admin_statuses_index'])->setLabel('Statuses');
    }
}
