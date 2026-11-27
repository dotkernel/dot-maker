<?php

declare(strict_types=1);

namespace Dot\Maker;

use Dot\Maker\IO\Output;
use Dot\Maker\Type\Form;
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

class Maker
{
    public function __construct(
        private readonly string $projectPath,
    ) {
    }

    /**
     * @param list<string> $arguments
     */
    public function __invoke(array $arguments): int
    {
        try {
            if (! $this->isCli()) {
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
                return Output::SUCCESS;
            }

            Output::info(sprintf('Detected project type: %s', $context->getProjectType()));
            Output::info(sprintf('Core architecture: %s', $context->hasCore() ? 'Yes' : 'No'));

            if ($instance instanceof Form && $context->isApi()) {
                throw new RuntimeException('Cannot create Forms in an API');
            }

            if ($instance->isModule()) {
                assert($instance instanceof ModuleInterface);
                $instance->setModule($instance);
                assert(is_callable($instance));
                $instance();

                return Output::SUCCESS;
            }

            $module = (new Module($fileSystem, $context, $config))->initExisting();
            $instance->setModule($module);
            assert(is_callable($instance));
            $instance();
            $module->renderMessages();

            return Output::SUCCESS;
        } catch (Throwable $exception) {
            Output::error($exception->getMessage());
            return Output::FAILURE;
        }
    }

    public function isCli(): bool
    {
        return PHP_SAPI === 'cli';
    }
}
