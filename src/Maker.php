<?php

declare(strict_types=1);

namespace Dot\Maker;

use Dot\Maker\IO\Output;
use Dot\Maker\Type\Module;
use Dot\Maker\Type\ModuleInterface;
use Dot\Maker\Type\TypeEnum;
use Dot\Maker\Type\TypeInterface;

use function array_shift;
use function assert;
use function count;
use function is_callable;
use function sprintf;
use function strtolower;
use function trim;

use const PHP_SAPI;

final readonly class Maker
{
    public function __construct(
        private string $composerPath,
        private string $configPath,
    ) {
    }

    public function __invoke(array $arguments): int
    {
        if (PHP_SAPI !== 'cli') {
            Output::error('dot-maker must be run in CLI only', true);
        }

        array_shift($arguments);
        if (count($arguments) === 0) {
            Output::error('dot-maker must be called with at least one parameter', true);
        }

        $argument  = trim(strtolower(array_shift($arguments)));
        $component = TypeEnum::getClass($argument);
        if ($component === null) {
            Output::error(sprintf('unknown component: "%s"', $argument), true);
        }

        $config     = new Config($this->configPath);
        $context    = new Context($this->composerPath);
        $fileSystem = new FileSystem($context);
        $instance   = new ($component)($fileSystem, $context, $config);
        assert($instance instanceof TypeInterface);

        Output::info(sprintf('Detected project type: %s', $context->getProjectType()));

        if (! $instance->isModule()) {
            $instance->setModule((new Module($fileSystem, $context, $config))->initExisting());
        } else {
            assert($instance instanceof ModuleInterface);
            $instance->setModule($instance);
        }
        assert(is_callable($instance));

        $instance();

        return Output::SUCCESS;
    }
}
