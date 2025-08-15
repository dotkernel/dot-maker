<?php

declare(strict_types=1);

namespace DotTest\Maker\Type;

use Dot\Maker\Config;
use Dot\Maker\Context;
use Dot\Maker\FileSystem;
use Dot\Maker\IO\Input;
use Dot\Maker\IO\Output;
use Dot\Maker\Type\Entity;
use Dot\Maker\Type\Module;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

use function fclose;
use function fopen;
use function fwrite;
use function rewind;
use function sprintf;
use function stream_get_contents;

use const PHP_EOL;

class EntityTest extends TestCase
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
                        "Api\\\\App\\\\": "src/App/src/",
                        "Core\\\\App\\\\": "src/Core/src/App/src/"
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
        $file = $this->fileSystem->entity($this->resourceName);
        $this->assertFileDoesNotExist($file->getPath());
        $this->assertFalse($file->exists());

        $this->expectExceptionMessage('Invalid Entity name: "."');
        $entity = new Entity($this->fileSystem, $this->context, $this->config, $this->module);
        $entity->create('.');

        rewind($this->errorStream);
        $this->assertEmpty(stream_get_contents($this->errorStream));
        $this->assertFalse($file->exists());
        $this->assertFileDoesNotExist($file->getPath());
    }

    public function testCallToCreateWillFailWhenAlreadyExists(): void
    {
        $file = $this->fileSystem->entity($this->resourceName);
        $this->assertFileDoesNotExist($file->getPath());
        $this->assertFalse($file->exists());
        $file->create('...');
        $this->assertFileExists($file->getPath());
        $this->assertTrue($file->exists());

        $this->expectExceptionMessage(
            sprintf('Class "BookStore" already exists at %s', $file->getPath())
        );
        $entity = new Entity($this->fileSystem, $this->context, $this->config, $this->module);
        $entity->create($this->resourceName);

        rewind($this->errorStream);
        $this->assertEmpty(stream_get_contents($this->errorStream));
    }

    public function testCallToInvokeWillNotCreateFileOnEmptyInput(): void
    {
        $file = $this->fileSystem->entity($this->resourceName);
        $this->assertFileDoesNotExist($file->getPath());
        $this->assertFalse($file->exists());

        fwrite($this->inputStream, PHP_EOL);
        rewind($this->inputStream);

        $entity = new Entity($this->fileSystem, $this->context, $this->config, $this->module);
        $entity();

        rewind($this->errorStream);
        $this->assertEmpty(stream_get_contents($this->errorStream));
        $this->assertFalse($file->exists());
        $this->assertFileDoesNotExist($file->getPath());
    }

    public function testCallToInvokeWillOutputErrorAndWillNotCreateFileWhenNameIsInvalid(): void
    {
        $file = $this->fileSystem->entity($this->resourceName);
        $this->assertFalse($file->exists());
        $this->assertFileDoesNotExist($file->getPath());

        fwrite($this->inputStream, '.' . PHP_EOL);
        rewind($this->inputStream);

        $entity = new Entity($this->fileSystem, $this->context, $this->config, $this->module);
        $entity();

        rewind($this->errorStream);
        $this->assertStringContainsString('Invalid Entity name: "."', stream_get_contents($this->errorStream));
        $this->assertFalse($file->exists());
        $this->assertFileDoesNotExist($file->getPath());
    }

    /**
     * @dataProvider dataProvider
     */
    public function testCallToInvokeWillSucceedWhenNameIsValid(string $expected): void
    {
        $file = $this->fileSystem->entity($this->resourceName);
        $this->assertFileDoesNotExist($file->getPath());
        $this->assertFalse($file->exists());

        fwrite($this->inputStream, $this->resourceName . PHP_EOL);
        rewind($this->inputStream);

        $entity = new Entity($this->fileSystem, $this->context, $this->config, $this->module);
        $entity();

        $this->assertFileExists($file->getPath());
        $this->assertTrue($file->exists());

        $this->assertSame($expected, $file->read());
    }

    /**
     * @dataProvider dataProvider
     */
    public function testCallToCreateWillSucceedWhenNameIsValid(string $expected): void
    {
        $file = $this->fileSystem->entity($this->resourceName);
        $this->assertFileDoesNotExist($file->getPath());
        $this->assertFalse($file->exists());

        $entity = new Entity($this->fileSystem, $this->context, $this->config, $this->module);
        $type   = $entity->create($this->resourceName);

        $this->assertFileExists($file->getPath());
        $this->assertTrue($file->exists());
        $this->assertSame($type->getPath(), $file->getPath());

        rewind($this->errorStream);
        $this->assertEmpty(stream_get_contents($this->errorStream));

        rewind($this->outputStream);
        $this->assertStringContainsString(
            sprintf('Created Entity: %s', $type->getPath()),
            stream_get_contents($this->outputStream)
        );

        $this->assertSame($expected, $type->read());
    }

    public static function dataProvider(): array
    {
        $entity = <<<BODY
<?php

declare(strict_types=1);

namespace Core\ModuleName\Entity;

use Core\App\Entity\AbstractEntity;
use Core\App\Entity\TimestampsTrait;
use Core\ModuleName\Repository\BookStoreRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BookStoreRepository::class)]
#[ORM\Table(name: 'book_store')]
#[ORM\HasLifecycleCallbacks]
class BookStore extends AbstractEntity
{
    use TimestampsTrait;

    public function __construct()
    {
        parent::__construct();

        \$this->created();
    }

    /**
     * @return array{
     *      uuid: non-empty-string,
     *      created: DateTimeImmutable,
     *      updated: DateTimeImmutable|null,
     * }
     */
    public function getArrayCopy(): array
    {
        return [
            'uuid'    => \$this->uuid->toString(),
            'created' => \$this->created,
            'updated' => \$this->updated,
        ];
    }
}

BODY;

        return [
            [$entity],
        ];
    }
}
