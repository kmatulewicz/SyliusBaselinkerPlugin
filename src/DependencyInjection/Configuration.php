<?php

declare(strict_types=1);

namespace Kmatulewicz\SyliusBaselinkerPlugin\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    /**
     * @psalm-suppress UnusedVariable
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('kmatulewicz_sylius_baselinker_plugin');
        $rootNode = $treeBuilder->getRootNode();

        return $treeBuilder;
    }
}
