<?php

declare(strict_types=1);

namespace Dot\Maker;

use Dot\Maker\IO\Output;
use Dot\Maker\Type\Help;
use Dot\Maker\Type\Module;
use Dot\Maker\Type\ModuleInterface;
use Dot\Maker\Type\TypeEnum;
use Dot\Maker\Type\TypeInterface;
use RuntimeException;
use Throwable;

use function array_shift;
use function assert;
use function is_callable;
use function sprintf;
use function strtolower;
use function trim;

use const PHP_SAPI;

final class Maker
{
    public function __construct(
        private readonly string $projectPath,
    ) {
    }

    public function __invoke(array $arguments): int
    {
        try {
            if (PHP_SAPI !== 'cli') {
                throw new RuntimeException('dot-maker must be run in CLI only');
            }

            array_shift($arguments);

            $argument  = trim(strtolower((string) array_shift($arguments)));
            $component = TypeEnum::getClass($argument);
            if ($component === null) {
                throw new RuntimeException(sprintf('Unknown component: "%s"', $argument));
            }

            $config     = new Config($this->projectPath);
            $context    = new Context($this->projectPath);
            $fileSystem = new FileSystem($context);
            $instance   = new ($component)($fileSystem, $context, $config);
            assert($instance instanceof TypeInterface);

            if ($instance instanceof Help) {
                $instance();
                exit;
            }

            Output::info(sprintf('Detected project type: %s', $context->getProjectType()));
            Output::info(sprintf('Core architecture: %s', $context->hasCore() ? 'Yes' : 'No'));

            if (! $instance->isModule()) {
                $instance->setModule((new Module($fileSystem, $context, $config))->initExisting());
            } else {
                assert($instance instanceof ModuleInterface);
                $instance->setModule($instance);
            }
            assert(is_callable($instance));

            $instance();

            return Output::SUCCESS;
        } catch (Throwable $exception) {
            Output::error($exception->getMessage());
            return Output::FAILURE;
        }
    }
}
