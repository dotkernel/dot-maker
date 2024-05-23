<?php

declare(strict_types=1);

namespace DotTest\Maker;

use Dot\Maker\Command\MakeCommandCommand;
use Dot\Maker\Command\MakeEntityCommand;
use Dot\Maker\Command\MakeFactoryCommand;
use Dot\Maker\Command\MakeHandlerCommand;
use Dot\Maker\Command\MakeMiddlewareCommand;
use Dot\Maker\Command\MakeServiceCommand;
use Dot\Maker\Command\PublishStubCommand;
use Dot\Maker\Config\ComponentConfig;
use Dot\Maker\Config\ComponentConfigInterface;
use Dot\Maker\ConfigProvider;
use Dot\Maker\Service\StubService;
use Dot\Maker\Service\StubServiceInterface;
use PHPUnit\Framework\TestCase;

class ConfigProviderTest extends TestCase
{
    protected array $config;

    protected function setup(): void
    {
        $this->config = (new ConfigProvider())();
    }

    public function testHasDependencies(): void
    {
        $this->assertArrayHasKey('dependencies', $this->config);
    }

    public function testDependenciesHasFactories(): void
    {
        $this->assertArrayHasKey('factories', $this->config['dependencies']);
        $this->assertArrayHasKey(ComponentConfig::class, $this->config['dependencies']['factories']);
        $this->assertArrayHasKey(MakeCommandCommand::class, $this->config['dependencies']['factories']);
        $this->assertArrayHasKey(MakeEntityCommand::class, $this->config['dependencies']['factories']);
        $this->assertArrayHasKey(MakeFactoryCommand::class, $this->config['dependencies']['factories']);
        $this->assertArrayHasKey(MakeHandlerCommand::class, $this->config['dependencies']['factories']);
        $this->assertArrayHasKey(MakeMiddlewareCommand::class, $this->config['dependencies']['factories']);
        $this->assertArrayHasKey(MakeServiceCommand::class, $this->config['dependencies']['factories']);
        $this->assertArrayHasKey(PublishStubCommand::class, $this->config['dependencies']['factories']);
        $this->assertArrayHasKey(StubService::class, $this->config['dependencies']['factories']);
    }

    public function testDependenciesHasAliases(): void
    {
        $this->assertArrayHasKey('aliases', $this->config['dependencies']);
        $this->assertArrayHasKey(ComponentConfigInterface::class, $this->config['dependencies']['aliases']);
        $this->assertArrayHasKey(StubServiceInterface::class, $this->config['dependencies']['aliases']);
    }
}
