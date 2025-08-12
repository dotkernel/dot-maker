<?php

declare(strict_types=1);

namespace Component;

use Dot\Maker\Component\Import;
use Dot\Maker\Config;
use Dot\Maker\Context;
use Dot\Maker\FileSystem;
use Dot\Maker\IO\Input;
use Dot\Maker\IO\Output;
use Dot\Maker\Type\Input as InputType;
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

class InputTest extends TestCase
{
    private Config $config;
    private Context $context;
    private FileSystem $fileSystem;
    private Import $import;
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
        $this->import     = new Import($this->context);
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
        $file = $this->fileSystem->input($this->resourceName);
        $this->assertFileDoesNotExist($file->getPath());
        $this->assertFalse($file->exists());

        $this->expectExceptionMessage('Invalid Input name: "."');
        $input = new InputType($this->fileSystem, $this->context, $this->config, $this->module);
        $input->create('.');

        rewind($this->errorStream);
        $this->assertEmpty(stream_get_contents($this->errorStream));
        $this->assertFalse($file->exists());
        $this->assertFileDoesNotExist($file->getPath());
    }

    public function testCallToCreateWillFailWhenAlreadyExists(): void
    {
        $file = $this->fileSystem->input($this->resourceName);
        $this->assertFileDoesNotExist($file->getPath());
        $this->assertFalse($file->exists());
        $file->create('...');
        $this->assertFileExists($file->getPath());
        $this->assertTrue($file->exists());

        $this->expectExceptionMessage(
            sprintf('Class "BookStoreInput" already exists at %s', $file->getPath())
        );
        $input = new InputType($this->fileSystem, $this->context, $this->config, $this->module);
        $input->create('BookStoreInput');

        rewind($this->errorStream);
        $this->assertEmpty(stream_get_contents($this->errorStream));
    }

    public function testCallToInvokeWillNotCreateFileOnEmptyInput(): void
    {
        $file = $this->fileSystem->input($this->resourceName);
        $this->assertFileDoesNotExist($file->getPath());
        $this->assertFalse($file->exists());

        fwrite($this->inputStream, PHP_EOL);
        rewind($this->inputStream);

        $input = new InputType($this->fileSystem, $this->context, $this->config, $this->module);
        $input();

        rewind($this->errorStream);
        $this->assertEmpty(stream_get_contents($this->errorStream));
        $this->assertFalse($file->exists());
        $this->assertFileDoesNotExist($file->getPath());
    }

    public function testCallToInvokeWillOutputErrorAndWillNotCreateFileWhenNameIsInvalid(): void
    {
        $file = $this->fileSystem->input($this->resourceName);
        $this->assertFalse($file->exists());
        $this->assertFileDoesNotExist($file->getPath());

        fwrite($this->inputStream, '.' . PHP_EOL);
        rewind($this->inputStream);

        $input = new InputType($this->fileSystem, $this->context, $this->config, $this->module);
        $input();

        rewind($this->errorStream);
        $this->assertStringContainsString('Invalid Input name: "."', stream_get_contents($this->errorStream));
        $this->assertFalse($file->exists());
        $this->assertFileDoesNotExist($file->getPath());
    }

    public function testCallToInvokeWillSucceedWhenNameIsValid(): void
    {
        $file = $this->fileSystem->input($this->resourceName);
        $this->assertFileDoesNotExist($file->getPath());
        $this->assertFalse($file->exists());

        fwrite($this->inputStream, $this->resourceName . PHP_EOL);
        rewind($this->inputStream);

        $input = new InputType($this->fileSystem, $this->context, $this->config, $this->module);
        $input();

        $this->assertFileExists($file->getPath());
        $this->assertTrue($file->exists());

        $this->assertSame($this->dataProvider(), $file->read());
    }

    public function testCallToCreateWillSucceedWhenNameIsValid(): void
    {
        $file = $this->fileSystem->input($this->resourceName);
        $this->assertFileDoesNotExist($file->getPath());
        $this->assertFalse($file->exists());

        $input = new InputType($this->fileSystem, $this->context, $this->config, $this->module);
        $type  = $input->create($this->resourceName);

        $this->assertFileExists($file->getPath());
        $this->assertTrue($file->exists());
        $this->assertSame($type->getPath(), $file->getPath());

        rewind($this->errorStream);
        $this->assertEmpty(stream_get_contents($this->errorStream));

        rewind($this->outputStream);
        $this->assertStringContainsString(
            sprintf('Created Input: %s', $type->getPath()),
            stream_get_contents($this->outputStream)
        );

        $this->assertSame($this->dataProvider(), $type->read());
    }

    private function dataProvider(): string
    {
        $uses = [
            sprintf('use %s;', $this->import->getAppMessageFqcn()),
            sprintf('use %s;', Import::LAMINAS_FILTER_STRINGTRIM),
            sprintf('use %s;', Import::LAMINAS_FILTER_STRIPTAGS),
            sprintf('use %s;', Import::LAMINAS_INPUTFILTER_INPUT),
            sprintf('use %s;', Import::LAMINAS_VALIDATOR_NOTEMPTY),
        ];
        $uses = implode(PHP_EOL, $uses);

        return <<<BODY
<?php

declare(strict_types=1);

namespace Api\ModuleName\InputFilter\Input;

{$uses}

class BookStoreInput extends Input
{
    public function __construct(
        ?string \$name = null,
        bool \$isRequired = true,
    ) {
        parent::__construct(\$name);

        \$this->setRequired(\$isRequired);
        \$this->getFilterChain()
            ->attachByName(StringTrim::class)
            ->attachByName(StripTags::class);

        // chain more validators below

        \$this->getValidatorChain()
            ->attachByName(NotEmpty::class, [
                'message' => Message::VALIDATOR_REQUIRED_FIELD,
            ], true);
    }
}

BODY;
    }
}
