<?php

declare(strict_types=1);

namespace Tests\SyliusBaselinkerPlugin\Unit\Resolver;

use Doctrine\ORM\EntityManagerInterface;
use SyliusBaselinkerPlugin\Resolver\StatusResolverInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use SyliusBaselinkerPlugin\Entity\StatusesAssociations;
use SyliusBaselinkerPlugin\Resolver\StatusResolver;

final class StatusResolverTest extends KernelTestCase
{
    private $associations = [
        ['status1', '1'],
        ['status2', '2'],
        ['status3', '3'],
        ['status4', '4'],
        ['status5', '5'],
    ];

    /** @test */
    public function it_can_be_initialized()
    {
        $this->prepare();

        /** @var StatusResolver $statusResolver */
        $statusResolver = static::getContainer()->get(StatusResolver::class);

        $this->assertInstanceOf(StatusResolverInterface::class, $statusResolver);
    }

    /**
     * @test
     * @dataProvider provide
     */
    public function it_can_resolve_baselinker_status($shopStatus, $baselinkerStatus)
    {
        $this->prepare();

        /** @var StatusResolver $statusResolver */
        $statusResolver = static::getContainer()->get(StatusResolver::class);

        $result = $statusResolver->resolveBaselinkerStatus($shopStatus);
        $this->assertEquals($baselinkerStatus, $result);
    }

    /**
     * @test
     * @dataProvider provide
     */
    public function it_can_resolve_shop_status($shopStatus, $baselinkerStatus)
    {
        $this->prepare();

        /** @var StatusResolver $statusResolver */
        $statusResolver = static::getContainer()->get(StatusResolver::class);

        $result = $statusResolver->resolveShopStatus((int) $baselinkerStatus);
        $this->assertEquals($shopStatus, $result);
    }

    /** @test */
    public function it_return_null_on_not_assigned_baselinker_status()
    {
        $this->prepare();

        /** @var StatusResolver $statusResolver */
        $statusResolver = static::getContainer()->get(StatusResolver::class);

        $result = $statusResolver->resolveShopStatus(100);
        $this->assertNull($result);
    }

    /** @test */
    public function it_return_null_on_not_existing_shop_status()
    {
        $this->prepare();

        /** @var StatusResolver $statusResolver */
        $statusResolver = static::getContainer()->get(StatusResolver::class);

        $result = $statusResolver->resolveBaselinkerStatus('not_existing_shop_status');
        $this->assertNull($result);
    }

    private function prepare(): void
    {
        self::bootKernel();

        /** @var MockObject $repository */
        $repository = $this->createMock(RepositoryInterface::class);
        $repository->expects(self::once())->method('findAll')->willReturn($this->prepare_associations());

        /** @var MockObject $entityManager */
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('getRepository')->willReturn($repository);

        static::getContainer()->set(EntityManagerInterface::class, $entityManager);
    }

    private function prepare_associations(): array
    {
        $associations = [];
        $source = $this->associations;
        foreach ($source as $value) {
            $associations[] = new StatusesAssociations($value[0], $value[1]);
        }

        return $associations;
    }

    public function provide(): array
    {
        return $this->associations;
    }
}
