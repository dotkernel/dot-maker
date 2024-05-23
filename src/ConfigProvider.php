<?php

declare(strict_types=1);

namespace Dot\Maker;

use Dot\Maker\Command\MakeCommandCommand;
use Dot\Maker\Command\MakeEntityCommand;
use Dot\Maker\Command\MakeFactoryCommand;
use Dot\Maker\Command\MakeHandlerCommand;
use Dot\Maker\Command\MakeMiddlewareCommand;
use Dot\Maker\Command\MakeServiceCommand;
use Dot\Maker\Command\PublishStubCommand;
use Dot\Maker\Config\ComponentConfig;
use Dot\Maker\Config\ComponentConfigInterface;
use Dot\Maker\Factory\ComponentConfigFactory;
use Dot\Maker\Factory\MakeComponentCommandFactory;
use Dot\Maker\Factory\PublishStubCommandFactory;
use Dot\Maker\Factory\StubServiceFactory;
use Dot\Maker\Service\StubService;
use Dot\Maker\Service\StubServiceInterface;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencyConfig(),
        ];
    }

    public function getDependencyConfig(): array
    {
        return [
            'aliases' => [
                ComponentConfigInterface::class => ComponentConfig::class,
                StubServiceInterface::class     => StubService::class,
            ],
            'factories' => [
                ComponentConfig::class       => ComponentConfigFactory::class,
                MakeCommandCommand::class    => MakeComponentCommandFactory::class,
                MakeEntityCommand::class     => MakeComponentCommandFactory::class,
                MakeFactoryCommand::class    => MakeComponentCommandFactory::class,
                MakeHandlerCommand::class    => MakeComponentCommandFactory::class,
                MakeMiddlewareCommand::class => MakeComponentCommandFactory::class,
                MakeServiceCommand::class    => MakeComponentCommandFactory::class,
                PublishStubCommand::class    => PublishStubCommandFactory::class,
                StubService::class           => StubServiceFactory::class,
            ],
        ];
    }
}
