<?php

declare(strict_types=1);

namespace Tests\SyliusBaselinkerPlugin\Unit\DependencyInjection;

use Doctrine\Bundle\MigrationsBundle\DependencyInjection\DoctrineMigrationsExtension;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use SyliusBaselinkerPlugin\DependencyInjection\SyliusBaselinkerExtension;
use SyliusLabs\DoctrineMigrationsExtraBundle\DependencyInjection\SyliusLabsDoctrineMigrationsExtraExtension;

final class SyliusBaselinkerExtensionTest extends AbstractExtensionTestCase
{
    /** @test */
    public function after_load_correct_parameters_are_set(): void
    {
        $this->load();

        $this->assertContainerBuilderHasParameter('sylius.baselinker_token', '%env(string:BL_TOKEN)%');
        $this->assertContainerBuilderHasParameter('sylius.baselinker_url', 'https://api.baselinker.com/connector.php');
        $this->assertContainerBuilderHasParameter('sylius.baselinker_method', 'POST');
        $this->assertContainerBuilderHasParameter('sylius.baselinker_on_delete', 'unsync');
        $this->assertContainerBuilderHasParameter('sylius.baselinker_days_to_sync', 14);
        $this->assertContainerBuilderHasParameter('sylius.baselinker_max_orders_add', 40);
        $this->assertContainerBuilderHasParameter('sylius.baselinker_max_orders_payments', 40);
    }

    /** @test */
    public function it_autoconfigures_prepending_doctrine_migration_with_proper_migrations_paths(): void
    {
        $this->configureContainer();

        $this->container->registerExtension(new DoctrineMigrationsExtension());
        $this->container->registerExtension(new SyliusLabsDoctrineMigrationsExtraExtension());

        $this->load();

        $doctrineMigrationsExtensionConfig = $this->container->getExtensionConfig('doctrine_migrations');

        self::assertArrayHasKey(
            'SyliusBaselinkerPlugin\Migration',
            $doctrineMigrationsExtensionConfig[0]['migrations_paths']
        );
        self::assertSame(
            '@SyliusBaselinkerPlugin/Migration',
            $doctrineMigrationsExtensionConfig[0]['migrations_paths']['SyliusBaselinkerPlugin\Migration']
        );

        $syliusLabsDoctrineMigrationsExtraExtensionConfig = $this->container
            ->getExtensionConfig('sylius_labs_doctrine_migrations_extra');

        self::assertArrayHasKey(
            'SyliusBaselinkerPlugin\Migration',
            $syliusLabsDoctrineMigrationsExtraExtensionConfig[0]['migrations']
        );
        self::assertSame(
            ['Sylius\Bundle\CoreBundle\Migrations'],
            $syliusLabsDoctrineMigrationsExtraExtensionConfig[0]['migrations']['SyliusBaselinkerPlugin\Migration']
        );
    }

    /** @test */
    public function it_does_not_autoconfigure_prepending_doctrine_migrations_if_it_is_disabled(): void
    {
        $this->configureContainer();
        $this->container->setParameter('sylius_core.prepend_doctrine_migrations', false);

        $this->load();

        $doctrineMigrationsExtensionConfig = $this->container->getExtensionConfig('doctrine_migrations');
        self::assertEmpty($doctrineMigrationsExtensionConfig);

        $syliusLabsDoctrineMigrationsExtraExtensionConfig = $this->container
            ->getExtensionConfig('sylius_labs_doctrine_migrations_extra');
        self::assertEmpty($syliusLabsDoctrineMigrationsExtraExtensionConfig);
    }

    protected function getContainerExtensions(): array
    {
        return [new SyliusBaselinkerExtension()];
    }

    private function configureContainer(): void
    {
        $this->container->setParameter('kernel.environment', 'test');
        $this->container->setParameter('kernel.debug', true);
        $this->container->setParameter('kernel.bundles', []);
        $this->container->setParameter('kernel.bundles_metadata', ['SyliusBaselinkerPlugin' => ['path' => __DIR__ . '../../']]);
    }
}
