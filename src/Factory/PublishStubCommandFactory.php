<?php

declare(strict_types=1);

namespace Dot\Maker\Factory;

use Dot\Maker\Command\PublishStubCommand;
use Dot\Maker\Service\StubServiceInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class PublishStubCommandFactory
{
    /**
     * @param ContainerInterface $container
     * @return PublishStubCommand
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): PublishStubCommand
    {
        return new PublishStubCommand(
            $container->get(StubServiceInterface::class)
        );
    }
}
