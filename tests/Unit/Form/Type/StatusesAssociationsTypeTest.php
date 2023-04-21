<?php

namespace Tests\SyliusBaselinkerPlugin\Unit\Form\Type;

use SyliusBaselinkerPlugin\Entity\StatusesAssociations;
use SyliusBaselinkerPlugin\Form\Type\StatusesAssociationsType;
use SyliusBaselinkerPlugin\Service\OrdersApiService;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Validation;

final class StatusesAssociationsTypeTest extends TypeTestCase
{
    /**
     * @covers \SyliusBaselinkerPlugin\Form\Type\StatusesAssociationsType
     * @covers \SyliusBaselinkerPlugin\Entity\StatusesAssociations
     */
    public function test_form_with_custom_setting(): void
    {
        $model = new StatusesAssociations();
        $form = $this->factory->create(StatusesAssociationsType::class, $model);
        $form->submit(['shopStatus' => 'testShop', 'baselinkerStatus' => 1051]);

        self::assertTrue($form->isSynchronized());
        self::assertEquals('testShop', $model->getShopStatus());
        self::assertEquals('1051', $model->getBaselinkerStatus());
    }

    protected function getExtensions(): array
    {
        $statuses = [
            [
                "id" => 1051,
                "name" => "New orders",
                "name_for_customer" => "Order accepted",
            ],

            [
                "id" => 1052,
                "name" => "To be paid (courier)",
                "name_for_customer" => " Awaiting payment",
            ],
            [
                "id" => 1291,
                "name" => "Ready to ship (courier)",
                "name_for_customer" => "Processing",
            ],
            [
                "id" => 1470,
                "name" => "To be paid (post mail)",
                "name_for_customer" => " Awaiting payment",
            ],
            [
                "id" => 1471,
                "name" => "Dispatched",
                "name_for_customer" => "The parcel has been shipped",
            ],
            [
                "id" => 4073,
                "name" => "Ready to ship (post mail)",
                "name_for_customer" => "Processing",
            ],
            [
                "id" => 4128,
                "name" => "Ready to ship (economy mail)",
                "name_for_customer" => "Processing",
            ],
            [
                "id" => 4129,
                "name" => "Ready to ship (priority mail)",
                "name_for_customer" => "Processing",
            ],
            [
                "id" => 4130,
                "name" => "Ready to ship (post priority)",
                "name_for_customer" => "Processing",
            ],
        ];

        $orderApi = $this->createMock(OrdersApiService::class);
        $orderApi->method('getOrderStatusList')->willReturn($statuses);

        $type = new StatusesAssociationsType(StatusesAssociations::class, $orderApi);
        return [
            new PreloadedExtension([$type], []),
            new ValidatorExtension(Validation::createValidator()),
        ];
    }
}
