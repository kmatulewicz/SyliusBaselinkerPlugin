<?php

namespace Tests\SyliusBaselinkerPlugin\Unit\Form\Type;

use SyliusBaselinkerPlugin\Entity\Settings;
use SyliusBaselinkerPlugin\Form\Type\SettingsType;
use SyliusBaselinkerPlugin\Service\OrdersApiService;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Validation;

final class SettingsTypeTest extends TypeTestCase
{
    /**
     * @covers \SyliusBaselinkerPlugin\Form\Type\SettingsType
     * @covers \SyliusBaselinkerPlugin\Entity\Settings
     */
    public function test_form_with_custom_setting(): void
    {
        $model = new Settings('test');
        $form = $this->factory->create(SettingsType::class, $model);
        $form->submit(['name' => 'testName', 'value' => 'testValue']);

        self::assertTrue($form->isSynchronized());
        self::assertEquals('test', $model->getName());
        self::assertEquals('testValue', $model->getValue());
    }

    /**
     * @covers \SyliusBaselinkerPlugin\Form\Type\SettingsType
     * @covers \SyliusBaselinkerPlugin\Entity\Settings
     */
    public function test_form_with_last_journal_id(): void
    {
        $model = new Settings('last.journal.id', '0');
        $form = $this->factory->create(SettingsType::class, $model);
        $form->submit(['name' => 'last.journal.id', 'value' => '155']);

        self::assertTrue($form->isSynchronized());
        self::assertEquals('155', $model->getValue());
    }

    /**
     * @covers \SyliusBaselinkerPlugin\Form\Type\SettingsType
     * @covers \SyliusBaselinkerPlugin\Entity\Settings
     */
    public function test_form_with_order_source(): void
    {
        $model = new Settings('order.source');
        $form = $this->factory->create(SettingsType::class, $model);
        $form->submit(['name' => 'order.source', 'value' => '1621']);

        self::assertTrue($form->isSynchronized());
        self::assertEquals('1621', $model->getValue());

        $model = new Settings('order.source');
        $form = $this->factory->create(SettingsType::class, $model);
        $form->submit(['name' => 'order.source', 'value' => '162']);

        self::assertTrue($form->isSynchronized());
        self::assertEquals('', $model->getValue());
    }

    protected function getExtensions(): array
    {
        $sources = [
            'personal' => [
                0 => 'In person / by phone',
                1621 => 'stationary',
            ],
            'shop' => [
                8235 => 'Shop 1',
                4626 => 'Shop 2',
            ],
        ];

        $orderApi = $this->createMock(OrdersApiService::class);
        $orderApi->method('getOrderSources')->willReturn($sources);

        $type = new SettingsType(Settings::class, $orderApi);
        return [
            new PreloadedExtension([$type], []),
            new ValidatorExtension(Validation::createValidator()),
        ];
    }
}
