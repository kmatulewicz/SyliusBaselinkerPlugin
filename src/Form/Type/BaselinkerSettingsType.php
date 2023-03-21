<?php

declare(strict_types=1);

namespace SyliusBaselinkerPlugin\Form\Type;

use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use SyliusBaselinkerPlugin\Entity\BaselinkerSettings;
use SyliusBaselinkerPlugin\Services\BaselinkerOrdersApiService;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class BaselinkerSettingsType extends AbstractResourceType
{
    private BaselinkerOrdersApiService $baselinkerOrder;

    /** @param  array<array-key, string> $validationGroups*/
    public function __construct(string $dataClass, BaselinkerOrdersApiService $baselinkerOrder, array $validationGroups = [])
    {
        $this->baselinkerOrder = $baselinkerOrder;

        parent::__construct($dataClass, $validationGroups);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var BaselinkerSettings $setting */
        $setting = $options['data'];

        $sources = $this->baselinkerOrder->getOrderSources();

        $flippedSources = [];
        /** @var array<array-key, string> $sourceCategory */
        foreach ($sources as $key => $sourceCategory) {
            $flippedSources[$key] = array_flip($sourceCategory);
        }

        $builder->add('value', ChoiceType::class, ['label' => $setting->getName(), 'choices' => $flippedSources]);
    }

    public function getBlockPrefix()
    {
        return 'baselinker_setting';
    }
}
