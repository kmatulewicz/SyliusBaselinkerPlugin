<?php

namespace Tests\SyliusBaselinkerPlugin\Unit\Entity;

use PHPUnit\Framework\TestCase;
use SyliusBaselinkerPlugin\Entity\Settings;

class SettingsTest extends TestCase
{
    /**
     * @covers \SyliusBaselinkerPlugin\Entity\Settings
     */
    public function test_Value(): void
    {
        $setting = new Settings('someName');

        $setting->setValue('someValue');
        self::assertEquals('someValue', $setting->getValue());
    }

    /**
     * @covers \SyliusBaselinkerPlugin\Entity\Settings
     */
    public function test___construct(): void
    {
        $setting = new Settings('someName', 'someValue');
        self::assertEquals('someName', $setting->getName());
        self::assertEquals('someName', $setting->getId());
        self::assertEquals('someValue', $setting->getValue());

        $setting = new Settings('someName2');
        self::assertEquals('someName2', $setting->getName());
        self::assertEquals('someName2', $setting->getId());
        self::assertEquals('', $setting->getValue());

        $this->expectException(\ArgumentCountError::class);
        $setting = new Settings();
    }

}
