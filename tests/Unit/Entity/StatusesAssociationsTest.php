<?php

namespace Tests\SyliusBaselinkerPlugin\Unit\Entity;

use PHPUnit\Framework\TestCase;
use SyliusBaselinkerPlugin\Entity\StatusesAssociations;
use function PHPUnit\Framework\assertEquals;

class StatusesAssociationsTest extends TestCase
{
    /**
     * @covers \SyliusBaselinkerPlugin\Entity\StatusesAssociations
     */
    public function test_BaselinkerStatus(): void
    {
        $assoc = new StatusesAssociations();
        $assoc->setBaselinkerStatus('baselinkerStatus');
        assertEquals('baselinkerStatus', $assoc->getBaselinkerStatus());
    }

    /**
     * @covers \SyliusBaselinkerPlugin\Entity\StatusesAssociations
     */
    public function test_ShopStatus(): void
    {
        $assoc = new StatusesAssociations();
        $assoc->setShopStatus('shopStatus');
        assertEquals('shopStatus', $assoc->getShopStatus());
        assertEquals('shopStatus', $assoc->getId());
    }

    /**
     * @covers \SyliusBaselinkerPlugin\Entity\StatusesAssociations
     */
    public function test___construct(): void
    {
        $assoc = new StatusesAssociations('shopStatus', 'baselinkerStatus');
        self::assertEquals('shopStatus', $assoc->getId());
        self::assertEquals('shopStatus', $assoc->getShopStatus());
        self::assertEquals('baselinkerStatus', $assoc->getBaselinkerStatus());

        $assoc = new StatusesAssociations('shopStatus2');
        self::assertEquals('shopStatus2', $assoc->getId());
        self::assertEquals('shopStatus2', $assoc->getShopStatus());
        self::assertEquals('', $assoc->getBaselinkerStatus());

        $assoc = new StatusesAssociations();
        self::assertEquals('', $assoc->getId());
        self::assertEquals('', $assoc->getShopStatus());
        self::assertEquals('', $assoc->getBaselinkerStatus());
    }
}
