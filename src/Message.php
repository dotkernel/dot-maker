<?php

declare(strict_types=1);

namespace Dot\Maker;

use function sprintf;

use const PHP_EOL;

class Message
{
    public const ADD_CONFIG_PROVIDER_TO_CONFIG      = 1;
    public const ADD_CORE_CONFIG_PROVIDER_TO_CONFIG = 2;
    public const ADD_MODULE_TO_COMPOSER             = 3;
    public const ADD_CORE_MODULE_TO_COMPOSER        = 4;
    public const ADD_COMMAND_TO_CONFIG              = 5;
    public const ADD_MIDDLEWARE_TO_PIPELINE         = 6;
    public const DUMP_COMPOSER_AUTOLOADER           = 7;
    public const GENERATE_MIGRATION                 = 8;

    public function __construct(
        private string $message = '',
        private int $priority = 10,
    ) {
    }

    public function __toString(): string
    {
        return $this->message;
    }

    public function append(string $message): self
    {
        $this->message .= $message;

        return $this;
    }

    public function appendLine(string $message): self
    {
        $this->message .= PHP_EOL . $message;

        return $this;
    }

    public static function addCommandToConfig(string $fqcn): self
    {
        return (new self('add to ', self::ADD_COMMAND_TO_CONFIG))
            ->append(
                ColorEnum::colorize('config/autoload/cli.global.php', ColorEnum::ForegroundBrightWhite)
            )
            ->append(' under ')
            ->append(
                ColorEnum::colorize('dot_cli', ColorEnum::ForegroundBrightWhite)
            )
            ->append('.')
            ->append(
                ColorEnum::colorize('commands', ColorEnum::ForegroundBrightWhite)
            )
            ->append(':')
            ->appendLine(
                ColorEnum::colorize(
                    sprintf('  %s::getDefaultName() => %s::class,', $fqcn, $fqcn),
                    ColorEnum::ForegroundBrightYellow
                )
            );
    }

    public static function addConfigProviderToConfig(string $fqcn): self
    {
        return (new self('add to ', self::ADD_CONFIG_PROVIDER_TO_CONFIG))
            ->append(
                ColorEnum::colorize('config/config.php', ColorEnum::ForegroundBrightWhite)
            )
            ->append(':')
            ->appendLine(
                ColorEnum::colorize(sprintf('  %s,', $fqcn), ColorEnum::ForegroundBrightYellow)
            );
    }

    public static function addCoreConfigProviderToConfig(string $fqcn): self
    {
        return self::addConfigProviderToConfig($fqcn)->setPriority(self::ADD_CORE_CONFIG_PROVIDER_TO_CONFIG);
    }

    public static function addModuleToComposer(string $rootNamespace, string $moduleName): self
    {
        return (new self('add to ', self::ADD_MODULE_TO_COMPOSER))
            ->append(
                ColorEnum::colorize('composer.json', ColorEnum::ForegroundBrightWhite)
            )
            ->append(' under ')
            ->append(
                ColorEnum::colorize('autoload', ColorEnum::ForegroundBrightWhite)
            )
            ->append('.')
            ->append(
                ColorEnum::colorize('psr-4', ColorEnum::ForegroundBrightWhite)
            )
            ->append(':')
            ->appendLine(
                ColorEnum::colorize(
                    sprintf('  "%s\\\\%s\\\\": "src/%s/src/"', $rootNamespace, $moduleName, $moduleName),
                    ColorEnum::ForegroundBrightYellow
                )
            );
    }

    public static function addCoreModuleToComposer(string $moduleName): self
    {
        return (new self('add to ', self::ADD_CORE_MODULE_TO_COMPOSER))
            ->append(
                ColorEnum::colorize('composer.json', ColorEnum::ForegroundBrightWhite)
            )
            ->append(' under ')
            ->append(
                ColorEnum::colorize('autoload', ColorEnum::ForegroundBrightWhite)
            )
            ->append('.')
            ->append(
                ColorEnum::colorize('psr-4', ColorEnum::ForegroundBrightWhite)
            )
            ->append(':')
            ->appendLine(
                ColorEnum::colorize(
                    sprintf(
                        '  "%s\\\\%s\\\\": "src/%s/src/%s/src/"',
                        ContextInterface::NAMESPACE_CORE,
                        $moduleName,
                        ContextInterface::NAMESPACE_CORE,
                        $moduleName
                    ),
                    ColorEnum::ForegroundBrightYellow
                )
            );
    }

    public static function addMiddlewareToPipeline(string $fqcn): self
    {
        return (new self('add to ', self::ADD_MIDDLEWARE_TO_PIPELINE))
            ->append(
                ColorEnum::colorize('config/pipeline.php', ColorEnum::ForegroundBrightWhite)
            )
            ->append(':')
            ->appendLine(
                ColorEnum::colorize(sprintf('  $app->pipe(%s);', $fqcn), ColorEnum::ForegroundBrightYellow)
            );
    }

    public static function dumpComposerAutoloader(): self
    {
        return (new self('dump Composer autoloader by executing this command:'))
            ->setPriority(self::DUMP_COMPOSER_AUTOLOADER)
            ->appendLine(ColorEnum::colorize('  composer dump', ColorEnum::ForegroundBrightYellow));
    }

    public static function generateMigration(): self
    {
        return (new self('generate Doctrine migration:'))
            ->setPriority(self::GENERATE_MIGRATION)
            ->appendLine(
                ColorEnum::colorize('  php ./vendor/bin/doctrine-migrations diff', ColorEnum::ForegroundBrightYellow)
            );
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): self
    {
        $this->priority = $priority;

        return $this;
    }
}
