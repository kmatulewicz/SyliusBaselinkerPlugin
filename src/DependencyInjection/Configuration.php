<?php

declare(strict_types=1);

namespace SyliusBaselinkerPlugin\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    /**
     * @psalm-suppress MixedMethodCall, PossiblyUndefinedMethod, UnusedVariable
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('sylius_baselinker');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode->
        children()->
            scalarNode('token')->
                defaultValue('%env(string:BL_TOKEN)%')->
                cannotBeEmpty()->info('Baselinker API token (Baselinker -> Settings -> API)')->
            end()->
            scalarNode('url')->
                defaultValue('https://api.baselinker.com/connector.php')->
                cannotBeEmpty()->
            end()->
            scalarNode('method')->
                defaultValue('POST')->
                cannotBeEmpty()->
            end()->
        end();

        return $treeBuilder;
    }
}
