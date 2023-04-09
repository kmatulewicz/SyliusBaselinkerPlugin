<?php

declare(strict_types=1);

namespace Tests\SyliusBaselinkerPlugin\Fixtures;

use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Tests\SyliusBaselinkerPlugin\Fixtures\Factory\OrderExampleFactory;
use Sylius\Bundle\FixturesBundle\Fixture\AbstractFixture;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

class CustomOrderFixture extends AbstractFixture
{
    /** @var OrderExampleFactory */
    protected $orderExampleFactory;

    /** @var ObjectManager */
    protected $orderManager;

    private Generator $faker;

    public function __construct(
        ObjectManager $orderManager,
        OrderExampleFactory $orderExampleFactory,
    ) {

        $this->orderManager = $orderManager;
        $this->orderExampleFactory = $orderExampleFactory;

        $this->faker = Factory::create();
    }

    public function load(array $options): void
    {
        $generateDates = $this->generateDates($options['amount']);

        for ($i = 0; $i < $options['amount']; ++$i) {
            $options = array_merge($options, ['complete_date' => array_shift($generateDates)]);

            $order = $this->orderExampleFactory->create($options);

            $this->orderManager->persist($order);

            if (0 === ($i % 50)) {
                $this->orderManager->flush();
            }
        }

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
                ->integerNode('amount')->isRequired()->min(0)->end()
                ->scalarNode('channel')->cannotBeEmpty()->end()
                ->scalarNode('customer')->cannotBeEmpty()->end()
                ->scalarNode('country')->cannotBeEmpty()->end()
                ->booleanNode('fulfilled')->defaultValue(false)->end()
                ->arrayNode('items')
                    ->scalarPrototype()->end()
                ->end()
            ->end()
        ;
    }

    private function generateDates(int $amount): array
    {
        /** @var \DateTimeInterface[] $dates */
        $dates = [];

        for ($i = 0; $i < $amount; ++$i) {
            $dates[] = $this->faker->dateTimeBetween('-10 days', 'now');
        }

        sort($dates);

        return $dates;
    }
}
