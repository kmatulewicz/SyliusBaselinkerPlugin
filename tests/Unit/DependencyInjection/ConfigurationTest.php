<?php

declare(strict_types=1);

namespace Tests\SyliusBaselinkerPlugin\Unit\DependencyInjection;

use Matthias\SymfonyConfigTest\PhpUnit\ConfigurationTestCaseTrait;
use PHPUnit\Framework\TestCase;
use SyliusBaselinkerPlugin\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class ConfigurationTest extends TestCase
{
    use ConfigurationTestCaseTrait;

    /** @test */
    public function it_sets_default_values(): void
    {
        $this->assertProcessedConfigurationEquals([], ['token' => '%env(string:BL_TOKEN)%'], 'token');
        $this->assertProcessedConfigurationEquals([], ['url' => 'https://api.baselinker.com/connector.php'], 'url');
        $this->assertProcessedConfigurationEquals([], ['method' => 'POST'], 'method');
    }

    /** @test */
    public function it_allows_to_set_other_values(): void
    {
        $this->assertProcessedConfigurationEquals([['token' => 'other_token']], ['token' => 'other_token'], 'token');
        $this->assertProcessedConfigurationEquals([['url' => 'https://other_url.php']], ['url' => 'https://other_url.php'], 'url');
        $this->assertProcessedConfigurationEquals([['method' => 'PUT']], ['method' => 'PUT'], 'method');
    }

    /** @test */
    public function it_does_not_allow_to_set_empty_values(): void
    {
        $this->assertConfigurationIsInvalid([['token' => '']]);
        $this->assertConfigurationIsInvalid([['url' => '']]);
        $this->assertConfigurationIsInvalid([['method' => '']]);
    }

    protected function getConfiguration(): ConfigurationInterface
    {
        return new Configuration();
    }
}
