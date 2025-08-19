<?php

declare(strict_types=1);

namespace DotTest\Maker;

use Dot\Maker\Message;
use PHPUnit\Framework\TestCase;

use const PHP_EOL;

class MessageTest extends TestCase
{
    public function testWillInstantiate(): void
    {
        $message = new Message('test');
        $this->assertContainsOnlyInstancesOf(Message::class, [$message]);
    }

    public function testCanUsePriorityAccessors(): void
    {
        $message = new Message('test');
        $this->assertSame(0, $message->getPriority());

        $message->setPriority(1);
        $this->assertSame(1, $message->getPriority());

        $message = new Message('test', 1);
        $this->assertSame(1, $message->getPriority());
    }

    public function testWillAppend(): void
    {
        $message = new Message('test');
        $this->assertSame('test', (string) $message);

        $message->append('-message');
        $this->assertSame('test-message', (string) $message);

        $message->appendLine('new line');
        $this->assertSame('test-message' . PHP_EOL . 'new line', (string) $message);
    }

    public function testWillParseAddCommandToConfigMessage(): void
    {
        $message = Message::addCommandToConfig('App\\Module\\Command\\TestCommand');

        $expected = <<<EXP
add to \033[97mconfig/autoload/cli.global.php\033[0m under \033[97mdot_cli\033[0m.\033[97mcommands\033[0m:
\033[93m  App\\Module\\Command\\TestCommand::getDefaultName() => App\\Module\\Command\\TestCommand::class,\033[0m
EXP;

        $this->assertSame($expected, (string) $message);
        $this->assertSame(Message::ADD_COMMAND_TO_CONFIG, $message->getPriority());
    }

    public function testWillParseAddConfigProviderToConfigMessage(): void
    {
        $message = Message::addConfigProviderToConfig('App\\Module\\ConfigProvider');

        $expected = <<<EXP
add to \033[97mconfig/config.php\033[0m:
\033[93m  App\\Module\\ConfigProvider,\033[0m
EXP;

        $this->assertSame($expected, (string) $message);
        $this->assertSame(Message::ADD_CONFIG_PROVIDER_TO_CONFIG, $message->getPriority());
    }

    public function testWillParseAddCoreConfigProviderToConfigMessage(): void
    {
        $message = Message::addCoreConfigProviderToConfig('Core\\Module\\ConfigProvider');

        $expected = <<<EXP
add to \033[97mconfig/config.php\033[0m:
\033[93m  Core\\Module\\ConfigProvider,\033[0m
EXP;

        $this->assertSame($expected, (string) $message);
        $this->assertSame(Message::ADD_CORE_CONFIG_PROVIDER_TO_CONFIG, $message->getPriority());
    }

    public function testWillParseAddModuleToComposerMessage(): void
    {
        $message = Message::addModuleToComposer('Api', 'Module');

        $expected = <<<EXP
add to \033[97mcomposer.json\033[0m under \033[97mautoload\033[0m.\033[97mpsr-4\033[0m:
\033[93m  "Api\\\\Module\\\\": "src/Module/src/"\033[0m
EXP;

        $this->assertSame($expected, (string) $message);
        $this->assertSame(Message::ADD_MODULE_TO_COMPOSER, $message->getPriority());
    }

    public function testWillParseAddCoreModuleToComposerMessage(): void
    {
        $message = Message::addCoreModuleToComposer('Module');

        $expected = <<<EXP
add to \033[97mcomposer.json\033[0m under \033[97mautoload\033[0m.\033[97mpsr-4\033[0m:
\033[93m  "Core\\\\Module\\\\": "src/Core/src/Module/src/"\033[0m
EXP;

        $this->assertSame($expected, (string) $message);
        $this->assertSame(Message::ADD_CORE_MODULE_TO_COMPOSER, $message->getPriority());
    }

    public function testWillParseAddMiddlewareToPipelineMessage(): void
    {
        $message = Message::addMiddlewareToPipeline('App\\Module\\Middleware\\TestMiddleware');

        $expected = <<<EXP
add to \033[97mconfig/pipeline.php\033[0m:
\033[93m  \$app->pipe(App\\Module\\Middleware\\TestMiddleware);\033[0m
EXP;

        $this->assertSame($expected, (string) $message);
        $this->assertSame(Message::ADD_MIDDLEWARE_TO_PIPELINE, $message->getPriority());
    }

    public function testWillParseAddRoutesToAuthConfigMessage(): void
    {
        $message = Message::addRoutesToAuthConfig(
            'config/autoload/authorization.php',
            'App\\Module\\RoutesDelegator.php'
        );

        $expected = <<<EXP
add to \033[97mconfig/autoload/authorization.php\033[0m
  the routes registered in \033[97mApp\\Module\\RoutesDelegator.php\033[0m
EXP;

        $this->assertSame($expected, (string) $message);
        $this->assertSame(Message::ADD_ROUTES_TO_AUTH_CONFIG, $message->getPriority());
    }

    public function testWillParseCheckFilesMessage(): void
    {
        $message = Message::checkFiles();

        $expected = <<<EXP
\033[91mRun through each new file, verify their content and start adding logic to them.\033[0m
EXP;

        $this->assertSame($expected, (string) $message);
        $this->assertSame(Message::CHECK_FILES, $message->getPriority());
    }

    public function testWillParseDumpComposerAutoloaderMessage(): void
    {
        $message = Message::dumpComposerAutoloader();

        $expected = <<<EXP
dump Composer autoloader by executing this command:
\033[93m  composer dump\033[0m
EXP;

        $this->assertSame($expected, (string) $message);
        $this->assertSame(Message::DUMP_COMPOSER_AUTOLOADER, $message->getPriority());
    }

    public function testWillParseGenerateMigrationMessage(): void
    {
        $message = Message::generateMigration();

        $expected = <<<EXP
generate Doctrine migration:
\033[93m  php ./vendor/bin/doctrine-migrations diff\033[0m
EXP;

        $this->assertSame($expected, (string) $message);
        $this->assertSame(Message::GENERATE_MIGRATION, $message->getPriority());
    }
}
