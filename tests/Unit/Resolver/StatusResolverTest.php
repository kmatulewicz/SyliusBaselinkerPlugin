<?php

declare(strict_types=1);

namespace Tests\SyliusBaselinkerPlugin\Unit\Resolver;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use SyliusBaselinkerPlugin\Entity\StatusesAssociations;
use SyliusBaselinkerPlugin\Resolver\StatusResolver;
use SyliusBaselinkerPlugin\Resolver\StatusResolverInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class StatusResolverTest extends KernelTestCase
{
    private array $associations = [
        ['status1', '1'],
        ['status2', '2'],
        ['status3', '3'],
        ['status4', '4'],
        ['status5', '5'],
    ];

    public function setUp(): void
    {
        self::bootKernel();

        /** @var MockObject $repository */
        $repository = $this->createMock(RepositoryInterface::class);
        $repository->expects(self::once())->method('findAll')->willReturn($this->prepare_associations());

        /** @var MockObject $entityManager */
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('getRepository')->willReturn($repository);

        StatusResolverTest::getContainer()->set(EntityManagerInterface::class, $entityManager);
    }

    private function prepare_associations(): array
    {
        $associations = [];
        $source = $this->associations;
        foreach ($source as $value) {
            $as = $this->createMock(StatusesAssociations::class);
            $as->method('getBaselinkerStatus')->willReturn($value[1]);
            $as->method('getShopStatus')->willReturn($value[0]);
            $associations[] = $as;
        }

        return $associations;
    }

    /**
     * @test
     * @covers \SyliusBaselinkerPlugin\Resolver\StatusResolver
     */
    public function it_can_be_initialized(): void
    {
        /** @var StatusResolver $statusResolver */
        $statusResolver = StatusResolverTest::getContainer()->get(StatusResolver::class);

        $this->assertInstanceOf(StatusResolverInterface::class, $statusResolver);
    }

    /**
     * @test
     * @dataProvider provide
     * @covers       \SyliusBaselinkerPlugin\Resolver\StatusResolver
     */
    public function it_can_resolve_baselinker_status($shopStatus, $baselinkerStatus): void
    {
        /** @var StatusResolver $statusResolver */
        $statusResolver = StatusResolverTest::getContainer()->get(StatusResolver::class);

        $result = $statusResolver->resolveBaselinkerStatus($shopStatus);
        $this->assertEquals($baselinkerStatus, $result);
    }

    /**
     * @test
     * @dataProvider provide
     * @covers       \SyliusBaselinkerPlugin\Resolver\StatusResolver
     */
    public function it_can_resolve_shop_status($shopStatus, $baselinkerStatus): void
    {
        /** @var StatusResolver $statusResolver */
        $statusResolver = StatusResolverTest::getContainer()->get(StatusResolver::class);

        $result = $statusResolver->resolveShopStatus((int) $baselinkerStatus);
        $this->assertEquals($shopStatus, $result);
    }

    /**
     * @test
     * @covers \SyliusBaselinkerPlugin\Resolver\StatusResolver
     */
    public function it_return_null_on_not_assigned_baselinker_status(): void
    {
        /** @var StatusResolver $statusResolver */
        $statusResolver = StatusResolverTest::getContainer()->get(StatusResolver::class);

        $result = $statusResolver->resolveShopStatus(100);
        $this->assertNull($result);
    }

    /**
     * @test
     * @covers \SyliusBaselinkerPlugin\Resolver\StatusResolver
     */
    public function it_return_null_on_not_existing_shop_status(): void
    {
        /** @var StatusResolver $statusResolver */
        $statusResolver = StatusResolverTest::getContainer()->get(StatusResolver::class);

        $result = $statusResolver->resolveBaselinkerStatus('not_existing_shop_status');
        $this->assertNull($result);
    }

    public function provide(): array
    {
        return $this->associations;
    }
}
