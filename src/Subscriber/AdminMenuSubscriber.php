<?php

declare(strict_types=1);

namespace SyliusBaselinkerPlugin\Subscriber;

use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;

final class AdminMenuSubscriber
{
    public function addAdminMenuEntries(MenuBuilderEvent $event): void
    {
        $menu = $event->getMenu();

        $submenu = $menu->addChild('baselinker')->setLabel('Baselinker');

        $submenu->addChild('settings', ['route' => 'baselinker_admin_settings_index'])->setLabel('baselinker.menu.settings');
        $submenu->addChild('statuses', ['route' => 'baselinker_admin_statuses_index'])->setLabel('baselinker.menu.statuses');
    }
}
