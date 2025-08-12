<?php

declare(strict_types=1);

namespace Component;

use Dot\Maker\Context;
use Dot\Maker\FileSystem;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

use function sprintf;

class CommandTest extends TestCase
{
    private Context $context;
    private string $moduleName   = 'ModuleName';
    private string $resourceName = 'BookStore';

    protected function setUp(): void
    {
        $fileSystem = vfsStream::setup('root', 0644, [
            'composer.json' => '{
                "autoload": {
                    "psr-4": {
                        "Api\\\\App\\\\": "src/App/src/"
                    }
                }
            }',
        ]);

        $this->context = new Context($fileSystem->url());
    }

    public function testWillSetupType(): void
    {
        $fileSystem = new FileSystem($this->context);
        $fileSystem->setModuleName($this->moduleName);
        $type = $fileSystem->command($this->resourceName);

        $this->assertSame('BookStoreCommand.php', $type->getName());
        $this->assertSame(
            sprintf('%s/src/ModuleName/src/Command/BookStoreCommand.php', $this->context->getProjectPath()),
            $type->getPath()
        );
    }

    public function testWillSetupParentDirectory(): void
    {
        $fileSystem = new FileSystem($this->context);
        $fileSystem->setModuleName($this->moduleName);
        $type = $fileSystem->command($this->resourceName);

        $this->assertSame(
            sprintf('%s/src/ModuleName/src', $this->context->getProjectPath()),
            $type->getParentDirectory()->getParentDirectory()
        );
        $this->assertSame('Command', $type->getParentDirectory()->getName());
        $this->assertSame(
            sprintf('%s/src/ModuleName/src/Command', $this->context->getProjectPath()),
            $type->getParentDirectory()->getPath()
        );
    }

    public function testWillSetupComponent(): void
    {
        $fileSystem = new FileSystem($this->context);
        $fileSystem->setModuleName($this->moduleName);
        $type = $fileSystem->command($this->resourceName);

        $this->assertSame('BookStoreCommand', $type->getComponent()->getClassName());
        $this->assertSame('BookStoreCommand::class', $type->getComponent()->getClassString());
        $this->assertSame(
            sprintf('Api\\%s\\Command\\BookStoreCommand', $this->moduleName),
            $type->getComponent()->getFqcn()
        );
        $this->assertSame(sprintf('Api\\%s\\Command', $this->moduleName), $type->getComponent()->getNamespace());
        $this->assertSame('$bookStoreCommand', $type->getComponent()->getVariable());
        $this->assertSame('$bookStoreCommand', $type->getComponent()->getVariable(false));
        $this->assertSame('BookStores', $type->getComponent()::pluralize($this->resourceName));
        $this->assertSame('bookStoreCommand', $type->getComponent()->toCamelCase());
        $this->assertSame('book-store-command', $type->getComponent()->toKebabCase());
        $this->assertSame('book_store_command', $type->getComponent()->toSnakeCase());
        $this->assertSame('BOOK_STORE_COMMAND', $type->getComponent()->toUpperCase());
    }

    public function testWillCreateFileWithoutService(): void
    {
        $fileSystem = new FileSystem($this->context);
        $fileSystem->setModuleName($this->moduleName);
        $type = $fileSystem->command($this->resourceName);

        $class = <<<BODY
<?php

declare(strict_types=1);

namespace Api\ModuleName\Command;

use {Import::SYMFONY_COMPONENT_CONSOLE_ATTRIBUTE_ASCOMMAND};
use {Import::SYMFONY_COMPONENT_CONSOLE_COMMAND_COMMAND};
use {Import::SYMFONY_COMPONENT_CONSOLE_INPUT_INPUTINTERFACE};
use {Import::SYMFONY_COMPONENT_CONSOLE_OUTPUT_OUTPUTINTERFACE};
use {Import::SYMFONY_COMPONENT_CONSOLE_STYLE_SYMFONYSTYLE};

#[AsCommand(name: 'book-store:command', description: 'Command description.')]
class BookStoreCommand extends Command
{
    /** @var string \$defaultName */
    protected static \$defaultName = 'book-store:command';

    public function __construct()
    {
        parent::__construct(self::\$defaultName);
    }

    protected function configure(): void
    {
        \$this
            ->setName(self::\$defaultName)
            ->setDescription('Command description.');
    }

    protected function execute(
        InputInterface \$input,
        OutputInterface \$output,
    ): int {
        \$io = new SymfonyStyle(\$input, \$output);
        \$io->info('BookStoreCommand default output');

        return Command::SUCCESS;
    }
}

BODY;

        $this->assertFileDoesNotExist($type->getPath());
        $type->create($class);
        $this->assertFileExists($type->getPath());

        $content = $type->read();
        $this->assertSame($class, $content);
        $this->assertStringContainsString('namespace Api\\ModuleName\\Command;', $content);
        $this->assertStringContainsString('class BookStoreCommand extends Command', $content);
    }

    public function testWillCreateFileWithService(): void
    {
        $fileSystem = new FileSystem($this->context);
        $fileSystem->setModuleName($this->moduleName);
        $type = $fileSystem->command($this->resourceName);

        $class = <<<BODY
<?php

declare(strict_types=1);

namespace Api\ModuleName\Command;

use Api\Book\Service\BookServiceInterface;
use {Import::DOT_DEPENDENCYINJECTION_ATTRIBUTE_INJECT};
use {Import::SYMFONY_COMPONENT_CONSOLE_ATTRIBUTE_ASCOMMAND};
use {Import::SYMFONY_COMPONENT_CONSOLE_COMMAND_COMMAND};
use {Import::SYMFONY_COMPONENT_CONSOLE_INPUT_INPUTINTERFACE};
use {Import::SYMFONY_COMPONENT_CONSOLE_OUTPUT_OUTPUTINTERFACE};
use {Import::SYMFONY_COMPONENT_CONSOLE_STYLE_SYMFONYSTYLE};

#[AsCommand(name: 'book-store:command', description: 'Command description.')]
class BookStoreCommand extends Command
{
    /** @var string \$defaultName */
    protected static \$defaultName = 'book-store:command';

    #[Inject(
        BookServiceInterface::class,
    )]
    public function __construct(
        protected BookServiceInterface \$bookService,
    ) {
        parent::__construct(self::\$defaultName);
    }

    protected function configure(): void
    {
        \$this
            ->setName(self::\$defaultName)
            ->setDescription('Command description.');
    }

    protected function execute(
        InputInterface \$input,
        OutputInterface \$output,
    ): int {
        \$io = new SymfonyStyle(\$input, \$output);
        \$io->info('BookStoreCommand default output');

        return Command::SUCCESS;
    }
}

BODY;

        $this->assertFileDoesNotExist($type->getPath());
        $type->create($class);
        $this->assertFileExists($type->getPath());

        $content = $type->read();
        $this->assertSame($class, $content);
        $this->assertStringContainsString('namespace Api\\ModuleName\\Command;', $content);
        $this->assertStringContainsString('class BookStoreCommand extends Command', $content);
    }
}
