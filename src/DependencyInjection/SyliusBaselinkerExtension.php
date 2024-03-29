<?php

declare(strict_types=1);

namespace SyliusBaselinkerPlugin\DependencyInjection;

use Exception;
use Sylius\Bundle\CoreBundle\DependencyInjection\PrependDoctrineMigrationsTrait;
use Sylius\Bundle\ResourceBundle\DependencyInjection\Extension\AbstractResourceExtension;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

final class SyliusBaselinkerExtension extends AbstractResourceExtension implements PrependExtensionInterface
{
    use PrependDoctrineMigrationsTrait;

    /**
     * @psalm-suppress UnusedVariable
     *
     * @throws Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        /** @var ConfigurationInterface $configuration */
        $configuration = $this->getConfiguration([], $container);
        $config = $this->processConfiguration($configuration, $configs);

        $token = (string) $config['token'];
        $url = (string) $config['url'];
        $method = (string) $config['method'];
        $on_delete = (string) $config['on_delete'];
        $days_to_sync = (string) $config['days_to_sync'];
        $max_orders_add = (string) $config['max_orders_add'];
        $max_orders_payments = (string) $config['max_orders_payments'];

        $container->setParameter('sylius.baselinker_token', $token);
        $container->setParameter('sylius.baselinker_url', $url);
        $container->setParameter('sylius.baselinker_method', $method);
        $container->setParameter('sylius.baselinker_on_delete', $on_delete);
        $container->setParameter('sylius.baselinker_days_to_sync', $days_to_sync);
        $container->setParameter('sylius.baselinker_max_orders_add', $max_orders_add);
        $container->setParameter('sylius.baselinker_max_orders_payments', $max_orders_payments);

        $this->processConfiguration($configuration, $configs);
    }

    public function prepend(ContainerBuilder $container): void
    {
        $config = $this->getCurrentConfiguration($container);
        $this->registerResources('baselinker_plugin', 'doctrine/orm', $config['resources'], $container);

        $this->prependDoctrineMigrations($container);
        $this->prependDoctrineMapping($container);
    }

    protected function getMigrationsNamespace(): string
    {
        return 'SyliusBaselinkerPlugin\Migration';
    }

    protected function getMigrationsDirectory(): string
    {
        return '@SyliusBaselinkerPlugin/Migration';
    }

    protected function getNamespacesOfMigrationsExecutedBefore(): array
    {
        return [
            'Sylius\Bundle\CoreBundle\Migrations',
        ];
    }

    private function prependDoctrineMapping(ContainerBuilder $container): void
    {
        $config = array_merge(...$container->getExtensionConfig('doctrine'));

        // do not register mappings if dbal not configured.
        if (!isset($config['dbal']) || !isset($config['orm'])) {
            return;
        }

        $container->prependExtensionConfig('doctrine', [
            'orm' => [
                'mappings' => [
                    'SyliusBaselinkerPlugin' => [
                        'type' => 'attribute',
                        'dir' => $this->getPath($container, '/Entity'),
                        'is_bundle' => false,
                        'prefix' => 'SyliusBaselinkerPlugin\Entity',
                        'alias' => 'SyliusBaselinkerPlugin',
                    ],
                ],
            ],
        ]);
    }

    private function getCurrentConfiguration(ContainerBuilder $container): array
    {
        /** @var ConfigurationInterface $configuration */
        $configuration = $this->getConfiguration([], $container);

        $configs = $container->getExtensionConfig($this->getAlias());

        return $this->processConfiguration($configuration, $configs);
    }

    private function getPath(ContainerBuilder $container, string $path): string
    {
        /** @var array<string, array<string, string>> $metadata */
        $metadata = $container->getParameter('kernel.bundles_metadata');

        return $metadata['SyliusBaselinkerPlugin']['path'] . $path;
    }
}
