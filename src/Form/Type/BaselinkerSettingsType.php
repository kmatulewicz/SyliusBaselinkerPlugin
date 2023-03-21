<?php

declare(strict_types=1);

namespace SyliusBaselinkerPlugin\Form\Type;

use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use SyliusBaselinkerPlugin\Entity\BaselinkerSettings;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class BaselinkerSettingsType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var BaselinkerSettings $setting */
        $setting = $options['data'];

        $builder->add('value', TextType::class, ['label' => $setting->getName()]);
    }

    public function getBlockPrefix()
    {
        return 'baselinker_setting';
    }
}
