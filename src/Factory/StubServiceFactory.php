<?php

declare(strict_types=1);

namespace Dot\Maker\Factory;

use Dot\Maker\Config\ComponentConfig;
use Dot\Maker\Service\StubService;
use Dot\Maker\Service\StubServiceInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class StubServiceFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): StubServiceInterface
    {
        return new StubService(
            $container->get(ComponentConfig::class),
        );
    }
}
