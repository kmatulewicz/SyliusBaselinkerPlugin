<?php

declare(strict_types=1);

namespace Tests\SyliusBaselinkerPlugin\Fixtures;

use DateTime;
use Doctrine\Persistence\ObjectManager;
use Exception;
use InvalidArgumentException;
use Sylius\Bundle\FixturesBundle\Fixture\AbstractFixture;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Tests\SyliusBaselinkerPlugin\Fixtures\Factory\OrderExampleFactory;

class CustomOrderFixture extends AbstractFixture
{
    protected OrderExampleFactory $orderExampleFactory;

    protected ObjectManager $orderManager;

    public function __construct(
        ObjectManager $orderManager,
        OrderExampleFactory $orderExampleFactory,
    ) {

        $this->orderManager = $orderManager;
        $this->orderExampleFactory = $orderExampleFactory;
    }

    public function load(array $options): void
    {
        try {
            $options = array_merge($options, ['complete_date' => new DateTime($options['complete_date'])]);
        } catch (Exception) {
            throw new InvalidArgumentException('Argument complete_date must be a valid time format');
        }

        $order = $this->orderExampleFactory->create($options);

        $this->orderManager->persist($order);

        $this->orderManager->flush();
    }

    public function getName(): string
    {
        return 'custom_order';
    }

    protected function configureOptionsNode(ArrayNodeDefinition $optionsNode): void
    {
        $optionsNode
            ->children()
                ->scalarNode('channel')->cannotBeEmpty()->end()
                ->scalarNode('customer')->cannotBeEmpty()->end()
                ->scalarNode('country')->cannotBeEmpty()->end()
                ->scalarNode('complete_date')->cannotBeEmpty()->end()
                ->arrayNode('billing_address')
                    ->children()
                        ->scalarNode('first_name')->end()
                        ->scalarNode('last_name')->end()
                        ->scalarNode('phone_number')->end()
                        ->scalarNode('company')->end()
                        ->scalarNode('street')->end()
                        ->scalarNode('city')->end()
                        ->scalarNode('postcode')->end()
                        ->scalarNode('country_code')->end()
                    ->end()
                ->end()
                ->arrayNode('shipping_address')
                    ->children()
                        ->scalarNode('first_name')->end()
                        ->scalarNode('last_name')->end()
                        ->scalarNode('phone_number')->end()
                        ->scalarNode('company')->end()
                        ->scalarNode('street')->end()
                        ->scalarNode('city')->end()
                        ->scalarNode('postcode')->end()
                        ->scalarNode('country_code')->end()
                    ->end()
                ->end()
                ->booleanNode('fulfilled')->defaultValue(false)->end()
                ->scalarNode('note')->defaultValue(null)->end()
                ->arrayNode('items')
                    ->scalarPrototype()->end()
                ->end()
            ->end();
    }
}
