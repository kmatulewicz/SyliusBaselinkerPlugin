<?php

declare(strict_types=1);

namespace Tests\SyliusBaselinkerPlugin\Fixtures;

use Sylius\Bundle\CoreBundle\Fixture\ProductFixture;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

class CustomProductFixture extends ProductFixture
{
    public function getName(): string
    {
        return 'custom_product';
    }

    protected function configureResourceNode(ArrayNodeDefinition $resourceNode): void
    {
        parent::configureResourceNode($resourceNode);

        $resourceNode->children()->scalarNode('price')->end();
    }
}
