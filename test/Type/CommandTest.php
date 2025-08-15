<?php

declare(strict_types=1);

namespace DotTest\Maker\Type;

use Dot\Maker\Component\Import;
use Dot\Maker\Config;
use Dot\Maker\Context;
use Dot\Maker\FileSystem;
use Dot\Maker\FileSystem\File;
use Dot\Maker\IO\Input;
use Dot\Maker\IO\Output;
use Dot\Maker\Type\Command;
use Dot\Maker\Type\Module;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

use function fclose;
use function fopen;
use function fwrite;
use function implode;
use function rewind;
use function sprintf;
use function stream_get_contents;

use const PHP_EOL;

class CommandTest extends TestCase
{
    private Config $config;
    private Context $context;
    private FileSystem $fileSystem;
    private Module $module;
    private string $moduleName   = 'ModuleName';
    private string $resourceName = 'BookStore';

    /** @var resource $outputStream */
    private $outputStream;
    /** @var resource $inputStream */
    private $inputStream;
    /** @var resource $errorStream */
    private $errorStream;

    protected function setUp(): void
    {
        $root = vfsStream::setup('root', 0644, [
            'composer.json' => '{
                "autoload": {
                    "psr-4": {
                        "Api\\\\App\\\\": "src/App/src/"
                    }
                }
            }',
        ]);

        $this->config     = new Config($root->url());
        $this->context    = new Context($root->url());
        $this->fileSystem = (new FileSystem($this->context))->setModuleName($this->moduleName);
        $this->module     = new Module($this->fileSystem, $this->context, $this->config);

        $this->outputStream = fopen('php://memory', 'w+');
        Output::setOutputStream($this->outputStream);

        $this->errorStream = fopen('php://memory', 'w+');
        Output::setErrorStream($this->errorStream);

        $this->inputStream = fopen('php://memory', 'w+');
        Input::setStream($this->inputStream);
    }

    protected function tearDown(): void
    {
        fclose($this->inputStream);
        fclose($this->errorStream);
        fclose($this->outputStream);
    }

    public function testCallToCreateWillFailWhenNameIsInvalid(): void
    {
        $file = $this->fileSystem->collection($this->resourceName);
        $this->assertFileDoesNotExist($file->getPath());
        $this->assertFalse($file->exists());

        $this->expectExceptionMessage('Invalid Command name: "."');
        $command = new Command($this->fileSystem, $this->context, $this->config, $this->module);
        $command->create('.');

        rewind($this->errorStream);
        $this->assertEmpty(stream_get_contents($this->errorStream));
        $this->assertFalse($file->exists());
        $this->assertFileDoesNotExist($file->getPath());
    }

    public function testCallToCreateWillFailWhenAlreadyExists(): void
    {
        $file = $this->fileSystem->command($this->resourceName);
        $this->assertFileDoesNotExist($file->getPath());
        $this->assertFalse($file->exists());
        $file->create('...');
        $this->assertFileExists($file->getPath());
        $this->assertTrue($file->exists());

        $this->expectExceptionMessage(
            sprintf('Class "BookStoreCommand" already exists at %s', $file->getPath())
        );
        $command = new Command($this->fileSystem, $this->context, $this->config, $this->module);
        $command->create('BookStoreCommand');

        rewind($this->errorStream);
        $this->assertEmpty(stream_get_contents($this->errorStream));
    }

    public function testCallToInvokeWillNotCreateFileOnEmptyInput(): void
    {
        $file = $this->fileSystem->command($this->resourceName);
        $this->assertFileDoesNotExist($file->getPath());
        $this->assertFalse($file->exists());

        fwrite($this->inputStream, PHP_EOL);
        rewind($this->inputStream);

        $command = new Command($this->fileSystem, $this->context, $this->config, $this->module);
        $command();

        rewind($this->errorStream);
        $this->assertEmpty(stream_get_contents($this->errorStream));
        $this->assertFalse($file->exists());
        $this->assertFileDoesNotExist($file->getPath());
    }

    public function testCallToInvokeWillOutputErrorAndWillNotCreateFileWhenNameIsInvalid(): void
    {
        $file = $this->fileSystem->command($this->resourceName);
        $this->assertFalse($file->exists());
        $this->assertFileDoesNotExist($file->getPath());

        fwrite($this->inputStream, '.' . PHP_EOL);
        rewind($this->inputStream);

        $command = new Command($this->fileSystem, $this->context, $this->config, $this->module);
        $command();

        rewind($this->errorStream);
        $this->assertStringContainsString('Invalid Command name: "."', stream_get_contents($this->errorStream));
        $this->assertFalse($file->exists());
        $this->assertFileDoesNotExist($file->getPath());
    }

    /**
     * @dataProvider dataProviderWithoutService
     */
    public function testCallToInvokeWithoutServiceWillSucceedWhenNameIsValid(string $expected): void
    {
        $file = $this->fileSystem->command($this->resourceName);
        $this->assertFileDoesNotExist($file->getPath());
        $this->assertFalse($file->exists());

        fwrite($this->inputStream, $this->resourceName . PHP_EOL);
        rewind($this->inputStream);

        $command = new Command($this->fileSystem, $this->context, $this->config, $this->module);
        $command();

        $this->assertFileExists($file->getPath());
        $this->assertTrue($file->exists());

        $this->assertSame($expected, $file->read());
    }

    public function testCallToInvokeWithServiceWillSucceedWhenNameIsValid(): void
    {
        $file = $this->fileSystem->command($this->resourceName);
        $this->assertFileDoesNotExist($file->getPath());
        $this->assertFalse($file->exists());

        $serviceInterface = $this->fileSystem->serviceInterface($this->moduleName);
        $serviceInterface->create('...');
        $this->assertFileExists($serviceInterface->getPath());
        $this->assertTrue($serviceInterface->exists());

        fwrite($this->inputStream, $this->resourceName . PHP_EOL);
        rewind($this->inputStream);

        $command = new Command($this->fileSystem, $this->context, $this->config, $this->module);
        $command();

        $this->assertFileExists($file->getPath());
        $this->assertTrue($file->exists());

        $this->assertSame($this->dataProviderWithService($serviceInterface), $file->read());
    }

    /**
     * @dataProvider dataProviderWithoutService
     */
    public function testCallToCreateWithoutServiceWillSucceedWhenNameIsValid(string $expected): void
    {
        $file = $this->fileSystem->command($this->resourceName);
        $this->assertFileDoesNotExist($file->getPath());
        $this->assertFalse($file->exists());

        fwrite($this->inputStream, $this->resourceName . PHP_EOL);
        rewind($this->inputStream);

        $command = new Command($this->fileSystem, $this->context, $this->config, $this->module);
        $command->create($this->resourceName);

        $this->assertFileExists($file->getPath());
        $this->assertTrue($file->exists());

        $this->assertSame($expected, $file->read());
    }

    public function testCallToCreateWithServiceWillSucceedWhenNameIsValid(): void
    {
        $file = $this->fileSystem->command($this->resourceName);
        $this->assertFileDoesNotExist($file->getPath());
        $this->assertFalse($file->exists());

        $serviceInterface = $this->fileSystem->serviceInterface($this->moduleName);
        $serviceInterface->create('...');
        $this->assertFileExists($serviceInterface->getPath());
        $this->assertTrue($serviceInterface->exists());

        fwrite($this->inputStream, $this->resourceName . PHP_EOL);
        rewind($this->inputStream);

        $command = new Command($this->fileSystem, $this->context, $this->config, $this->module);
        $command->create($this->resourceName);

        $this->assertFileExists($file->getPath());
        $this->assertTrue($file->exists());

        $this->assertSame($this->dataProviderWithService($serviceInterface), $file->read());
    }

    public static function dataProviderWithoutService(): array
    {
        $uses = [
            sprintf('use %s;', Import::SYMFONY_COMPONENT_CONSOLE_ATTRIBUTE_ASCOMMAND),
            sprintf('use %s;', Import::SYMFONY_COMPONENT_CONSOLE_COMMAND_COMMAND),
            sprintf('use %s;', Import::SYMFONY_COMPONENT_CONSOLE_INPUT_INPUTINTERFACE),
            sprintf('use %s;', Import::SYMFONY_COMPONENT_CONSOLE_OUTPUT_OUTPUTINTERFACE),
            sprintf('use %s;', Import::SYMFONY_COMPONENT_CONSOLE_STYLE_SYMFONYSTYLE),
        ];
        $uses = implode(PHP_EOL, $uses);

        $expected = <<<BODY
<?php

declare(strict_types=1);

namespace Api\ModuleName\Command;

{$uses}

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

        return [
            [$expected],
        ];
    }

    private function dataProviderWithService(File $serviceInterface): string
    {
        $uses = [
            sprintf('use %s;', $serviceInterface->getComponent()->getFqcn()),
            sprintf('use %s;', Import::DOT_DEPENDENCYINJECTION_ATTRIBUTE_INJECT),
            sprintf('use %s;', Import::SYMFONY_COMPONENT_CONSOLE_ATTRIBUTE_ASCOMMAND),
            sprintf('use %s;', Import::SYMFONY_COMPONENT_CONSOLE_COMMAND_COMMAND),
            sprintf('use %s;', Import::SYMFONY_COMPONENT_CONSOLE_INPUT_INPUTINTERFACE),
            sprintf('use %s;', Import::SYMFONY_COMPONENT_CONSOLE_OUTPUT_OUTPUTINTERFACE),
            sprintf('use %s;', Import::SYMFONY_COMPONENT_CONSOLE_STYLE_SYMFONYSTYLE),
        ];
        $uses = implode(PHP_EOL, $uses);

        return <<<BODY
<?php

declare(strict_types=1);

namespace Api\ModuleName\Command;

{$uses}

#[AsCommand(name: 'book-store:command', description: 'Command description.')]
class BookStoreCommand extends Command
{
    /** @var string \$defaultName */
    protected static \$defaultName = 'book-store:command';

    #[Inject(
        ModuleNameServiceInterface::class,
    )]
    public function __construct(
        protected ModuleNameServiceInterface \$moduleNameService,
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
    }
}
