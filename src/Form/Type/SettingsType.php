<?php

declare(strict_types=1);

namespace SyliusBaselinkerPlugin\Form\Type;

use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use SyliusBaselinkerPlugin\Entity\Settings;
use SyliusBaselinkerPlugin\Service\OrdersApiService;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\PositiveOrZero;
use Symfony\Component\Validator\Constraints\Type;

class SettingsType extends AbstractResourceType
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

        $optionName = $setting->getName();
        switch ($optionName) {
            case 'order.source':
                $this->order_source($builder, $optionName);

                break;
            case 'last.journal.id':
                $builder->add('value', IntegerType::class, ['label' => $optionName, 'constraints' => [
                    new Type('integer'),
                    new PositiveOrZero(),
                ]]);
                $builder->get('value')->addModelTransformer(new CallbackTransformer(
                    function (int $value): int {
                        return $value;
                    },
                    function (?int $value): mixed {
                        return $value ?? '';
                    },
                ));

                break;
            default:
                $builder->add('value', TextType::class, ['label' => $optionName]);
        }
    }

    public function getBlockPrefix()
    {
        return 'baselinker_setting';
    }

    private function order_source(FormBuilderInterface $builder, string $optionName): void
    {
        $sources = $this->baselinkerOrder->getOrderSources();

        $flippedSources = [];
        /** @var array<array-key, string> $sourceCategory */
        foreach ($sources as $key => $sourceCategory) {
            $flippedSources[$key] = array_flip($sourceCategory);
        }

        $builder->add('value', ChoiceType::class, ['label' => $optionName, 'choices' => $flippedSources]);
    }
}
