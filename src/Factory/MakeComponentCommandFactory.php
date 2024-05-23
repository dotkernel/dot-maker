<?php

declare(strict_types=1);

namespace Dot\Maker\Factory;

use Dot\Maker\Command\MakeComponentCommandInterface;
use Dot\Maker\Config\ComponentConfigInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class MakeComponentCommandFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, string $serviceName): MakeComponentCommandInterface
    {
        return new $serviceName(
            $container->get(ComponentConfigInterface::class)
        );
    }
}
