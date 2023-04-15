<?php

namespace Tests\SyliusBaselinkerPlugin\Unit\Entity;

use PHPUnit\Framework\TestCase;
use SyliusBaselinkerPlugin\Entity\OrderTrait;

class OrderTraitTest extends TestCase
{

    /**
     * @covers \SyliusBaselinkerPlugin\Entity\OrderTrait
     */
    public function test_BaselinkerId(): void
    {
        $mock = $this->getMockForTrait(OrderTrait::class);

        $mock->setBaselinkerId(15);
        self::assertEquals(15, $mock->getBaselinkerId());
    }

    /**
     * @covers \SyliusBaselinkerPlugin\Entity\OrderTrait
     */
    public function test_BaselinkerUpdateTime(): void
    {
        $mock = $this->getMockForTrait(OrderTrait::class);

        $mock->setBaselinkerUpdateTime(16);
        self::assertEquals(16, $mock->getBaselinkerUpdateTime());
    }
}
