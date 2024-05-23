<?php

declare(strict_types=1);

namespace Dot\Maker\Factory;

use Dot\Maker\Config\ComponentConfig;
use Dot\Maker\Config\ComponentConfigInterface;
use Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Webmozart\Assert\Assert;

class ComponentConfigFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Exception
     */
    public function __invoke(ContainerInterface $container): ComponentConfigInterface
    {
        $path = dirname(__DIR__, 2) . '/config/dot-maker.php';
        Assert::fileExists($path);

        return new ComponentConfig(require $path);
    }
}
