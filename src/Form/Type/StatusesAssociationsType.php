<?php

declare(strict_types=1);

namespace SyliusBaselinkerPlugin\Form\Type;

use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use SyliusBaselinkerPlugin\Entity\Settings;
use SyliusBaselinkerPlugin\Service\OrdersApiService;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

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

        $disabled = ($setting->getId() === '') ? false : true;

        $builder->add('shopStatus', TextType::class, [
            'label' => 'baselinker.ui.shop_status',
            'required' => true,
            'disabled' => $disabled,
            'constraints' => [
                new NotBlank(),
                new NotNull(),
            ],
        ]);
        $builder->get('shopStatus')->addModelTransformer(new CallbackTransformer(
            function (string $value): string {
                return $value;
            },
            function (?string $value): string {
                return $value ?? '';
            },
        ), true);

        $builder->add('baselinkerStatus', ChoiceType::class, [
            'label' => 'baselinker.ui.baselinker_status',
            'choices' => $choices,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'baselinker_statuses_associations';
    }
}
