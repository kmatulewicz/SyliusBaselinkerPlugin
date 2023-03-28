<?php

declare(strict_types=1);

namespace SyliusBaselinkerPlugin\DependencyInjection;

use SyliusBaselinkerPlugin\Entity\BaselinkerSettings;
use SyliusBaselinkerPlugin\Entity\BaselinkerStatusesAssociations;
use SyliusBaselinkerPlugin\Form\Type\BaselinkerSettingsType;
use SyliusBaselinkerPlugin\Form\Type\BaselinkerStatusesAssociationsType;
use SyliusBaselinkerPlugin\Repository\BaselinkerSettingsRepository;
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
                                        ->scalarNode('model')->defaultValue(BaselinkerSettings::class)->cannotBeEmpty()->end()
                                        ->scalarNode('repository')->defaultValue(BaselinkerSettingsRepository::class)->cannotBeEmpty()->end()
                                        ->scalarNode('form')->defaultValue(BaselinkerSettingsType::class)->cannotBeEmpty()->end()
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
                                        ->scalarNode('model')->defaultValue(BaselinkerStatusesAssociations::class)->cannotBeEmpty()->end()
                                        ->scalarNode('form')->defaultValue(BaselinkerStatusesAssociationsType::class)->cannotBeEmpty()->end()
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
