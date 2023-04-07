<?php

declare(strict_types=1);

namespace SyliusBaselinkerPlugin\DependencyInjection;

use SyliusBaselinkerPlugin\Entity\Settings;
use SyliusBaselinkerPlugin\Entity\StatusesAssociations;
use SyliusBaselinkerPlugin\Form\Type\SettingsType;
use SyliusBaselinkerPlugin\Form\Type\StatusesAssociationsType;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
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

        $rootNode
            ->children()
                ->scalarNode('token')
                    ->defaultValue('%env(string:BL_TOKEN)%')
                    ->cannotBeEmpty()->info('Baselinker API token (Baselinker -> Settings -> API)')
                ->end()
                ->scalarNode('url')
                    ->defaultValue('https://api.baselinker.com/connector.php')
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('method')
                    ->defaultValue('POST')
                    ->cannotBeEmpty()
                ->end()
                ->enumNode('on_delete')
                    ->values(['unsync', 'cancel'])
                    ->defaultValue('unsync')
                ->end()
            ->end()
        ;

        $this->addResourcesSection($rootNode);

        return $treeBuilder;
    }

    private function addResourcesSection(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
                ->arrayNode('resources')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('baselinker_settings')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('options')->end()
                                ->arrayNode('classes')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('model')->defaultValue(Settings::class)->cannotBeEmpty()->end()
                                        ->scalarNode('form')->defaultValue(SettingsType::class)->cannotBeEmpty()->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('baselinker_statuses')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('options')->end()
                                ->arrayNode('classes')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('model')->defaultValue(StatusesAssociations::class)->cannotBeEmpty()->end()
                                        ->scalarNode('form')->defaultValue(StatusesAssociationsType::class)->cannotBeEmpty()->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
}
