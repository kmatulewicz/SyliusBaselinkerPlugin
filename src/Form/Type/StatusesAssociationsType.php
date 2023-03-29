<?php

declare(strict_types=1);

namespace SyliusBaselinkerPlugin\Form\Type;

use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use SyliusBaselinkerPlugin\Entity\Settings;
use SyliusBaselinkerPlugin\Service\OrdersApiService;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class StatusesAssociationsType extends AbstractResourceType
{
    private OrdersApiService $baselinkerOrder;

    /** @param  array<array-key, string> $validationGroups*/
    public function __construct(string $dataClass, OrdersApiService $baselinkerOrder, array $validationGroups = [])
    {
        $this->baselinkerOrder = $baselinkerOrder;

        parent::__construct($dataClass, $validationGroups);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Settings $setting */
        $setting = $options['data'];

        $statuses = $this->baselinkerOrder->getOrderStatusList();

        $choices = [];
        /** @var mixed $status */
        foreach ($statuses as $status) {
            if (is_array($status) && array_key_exists('name', $status) && array_key_exists('id', $status)) {
                $choices[(string) $status['name']] = (string) $status['id'];
            }
        }

        $builder->add('shopStatus', TextType::class);
        $builder->add('baselinkerStatus', ChoiceType::class, ['choices' => $choices]);
    }

    public function getBlockPrefix()
    {
        return 'baselinker_statuses_associations';
    }
}
