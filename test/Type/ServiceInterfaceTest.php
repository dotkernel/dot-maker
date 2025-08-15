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
use Dot\Maker\Type\Module;
use Dot\Maker\Type\ServiceInterface;
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

class ServiceInterfaceTest extends TestCase
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
                        "Admin\\\\App\\\\": "src/App/src/",
                        "Core\\\\App\\\\": "src/Core/src/App/src/"
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
        $file = $this->fileSystem->serviceInterface($this->resourceName);
        $this->assertFileDoesNotExist($file->getPath());
        $this->assertFalse($file->exists());

        $this->expectExceptionMessage('Invalid ServiceInterface name: "."');
        $serviceInterface = new ServiceInterface($this->fileSystem, $this->context, $this->config, $this->module);
        $serviceInterface->create('.');

        rewind($this->errorStream);
        $this->assertEmpty(stream_get_contents($this->errorStream));
        $this->assertFalse($file->exists());
        $this->assertFileDoesNotExist($file->getPath());
    }

    public function testCallToCreateWillFailWhenAlreadyExists(): void
    {
        $file = $this->fileSystem->serviceInterface($this->resourceName);
        $this->assertFileDoesNotExist($file->getPath());
        $this->assertFalse($file->exists());
        $file->create('...');
        $this->assertFileExists($file->getPath());
        $this->assertTrue($file->exists());

        $this->expectExceptionMessage(
            sprintf('Class "BookStoreServiceInterface" already exists at %s', $file->getPath())
        );
        $serviceInterface = new ServiceInterface($this->fileSystem, $this->context, $this->config, $this->module);
        $serviceInterface->create('BookStoreServiceInterface');

        rewind($this->errorStream);
        $this->assertEmpty(stream_get_contents($this->errorStream));
    }

    public function testCallToInvokeWillNotCreateFileOnEmptyInput(): void
    {
        $file = $this->fileSystem->serviceInterface($this->resourceName);
        $this->assertFileDoesNotExist($file->getPath());
        $this->assertFalse($file->exists());

        fwrite($this->inputStream, PHP_EOL);
        rewind($this->inputStream);

        $serviceInterface = new ServiceInterface($this->fileSystem, $this->context, $this->config, $this->module);
        $serviceInterface();

        rewind($this->errorStream);
        $this->assertEmpty(stream_get_contents($this->errorStream));
        $this->assertFalse($file->exists());
        $this->assertFileDoesNotExist($file->getPath());
    }

    public function testCallToInvokeWillOutputErrorAndWillNotCreateFileWhenNameIsInvalid(): void
    {
        $file = $this->fileSystem->serviceInterface($this->resourceName);
        $this->assertFalse($file->exists());
        $this->assertFileDoesNotExist($file->getPath());

        fwrite($this->inputStream, '.' . PHP_EOL);
        rewind($this->inputStream);

        $serviceInterface = new ServiceInterface($this->fileSystem, $this->context, $this->config, $this->module);
        $serviceInterface();

        rewind($this->errorStream);
        $this->assertStringContainsString(
            'Invalid ServiceInterface name: "."',
            stream_get_contents($this->errorStream)
        );
        $this->assertFalse($file->exists());
        $this->assertFileDoesNotExist($file->getPath());
    }

    public function testCallToInvokeWillSucceedWhenNameIsValidAndProjectTypeIsNotApi(): void
    {
        $file = $this->fileSystem->serviceInterface($this->resourceName);
        $this->assertFileDoesNotExist($file->getPath());
        $this->assertFalse($file->exists());

        $entity = $this->fileSystem->entity($this->resourceName);
        $entity->create('...');
        $this->assertFileExists($entity->getPath());
        $this->assertTrue($entity->exists());

        $repository = $this->fileSystem->repository($this->resourceName);
        $repository->create('...');
        $this->assertFileExists($repository->getPath());
        $this->assertTrue($repository->exists());

        fwrite($this->inputStream, $this->resourceName . PHP_EOL);
        rewind($this->inputStream);

        $serviceInterface = new ServiceInterface($this->fileSystem, $this->context, $this->config, $this->module);
        $serviceInterface();

        $this->assertFileExists($file->getPath());
        $this->assertTrue($file->exists());

        $this->assertSame($this->dataProviderNotApi($entity, $repository), $file->read());
    }

    public function testCallToCreateWillSucceedWhenNameIsValidAndProjectTypeIsNotApi(): void
    {
        $file = $this->fileSystem->serviceInterface($this->resourceName);
        $this->assertFileDoesNotExist($file->getPath());
        $this->assertFalse($file->exists());

        $entity = $this->fileSystem->entity($this->resourceName);
        $entity->create('...');
        $this->assertFileExists($entity->getPath());
        $this->assertTrue($entity->exists());

        $repository = $this->fileSystem->repository($this->resourceName);
        $repository->create('...');
        $this->assertFileExists($repository->getPath());
        $this->assertTrue($repository->exists());

        fwrite($this->inputStream, $this->resourceName . PHP_EOL);
        rewind($this->inputStream);

        $serviceInterface = new ServiceInterface($this->fileSystem, $this->context, $this->config, $this->module);
        $serviceInterface->create($this->resourceName);

        $this->assertFileExists($file->getPath());
        $this->assertTrue($file->exists());

        $this->assertSame($this->dataProviderNotApi($entity, $repository), $file->read());
    }

    public function testCallToInvokeWillSucceedWhenNameIsValidAndProjectTypeIsApi(): void
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

        $this->context    = new Context($root->url());
        $this->fileSystem = (new FileSystem($this->context))->setModuleName($this->moduleName);
        $this->import     = new Import($this->context);
        $this->module     = new Module($this->fileSystem, $this->context, $this->config);

        $file = $this->fileSystem->serviceInterface($this->resourceName);
        $this->assertFileDoesNotExist($file->getPath());
        $this->assertFalse($file->exists());

        $entity = $this->fileSystem->entity($this->resourceName);
        $entity->create('...');
        $this->assertFileExists($entity->getPath());
        $this->assertTrue($entity->exists());

        $repository = $this->fileSystem->repository($this->resourceName);
        $repository->create('...');
        $this->assertFileExists($repository->getPath());
        $this->assertTrue($repository->exists());

        fwrite($this->inputStream, $this->resourceName . PHP_EOL);
        rewind($this->inputStream);

        $serviceInterface = new ServiceInterface($this->fileSystem, $this->context, $this->config, $this->module);
        $serviceInterface();

        $this->assertFileExists($file->getPath());
        $this->assertTrue($file->exists());

        $this->assertSame($this->dataProviderApi($entity, $repository), $file->read());
    }

    public function testCallToCreateWillSucceedWhenNameIsValidAndProjectTypeIsApi(): void
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

        $this->context    = new Context($root->url());
        $this->fileSystem = (new FileSystem($this->context))->setModuleName($this->moduleName);
        $this->import     = new Import($this->context);
        $this->module     = new Module($this->fileSystem, $this->context, $this->config);

        $file = $this->fileSystem->serviceInterface($this->resourceName);
        $this->assertFileDoesNotExist($file->getPath());
        $this->assertFalse($file->exists());

        $entity = $this->fileSystem->entity($this->resourceName);
        $entity->create('...');
        $this->assertFileExists($entity->getPath());
        $this->assertTrue($entity->exists());

        $repository = $this->fileSystem->repository($this->resourceName);
        $repository->create('...');
        $this->assertFileExists($repository->getPath());
        $this->assertTrue($repository->exists());

        fwrite($this->inputStream, $this->resourceName . PHP_EOL);
        rewind($this->inputStream);

        $serviceInterface = new ServiceInterface($this->fileSystem, $this->context, $this->config, $this->module);
        $serviceInterface->create($this->resourceName);

        $this->assertFileExists($file->getPath());
        $this->assertTrue($file->exists());

        $this->assertSame($this->dataProviderApi($entity, $repository), $file->read());
    }

    private function dataProviderNotApi(File $entity, File $repository): string
    {
        $uses = [
            sprintf('use %s;', $this->import->getNotFoundExceptionFqcn()),
            sprintf('use %s;', $entity->getComponent()->getFqcn()),
            sprintf('use %s;', $repository->getComponent()->getFqcn()),
        ];
        $uses = implode(PHP_EOL, $uses);

        return <<<BODY
<?php

declare(strict_types=1);

namespace Admin\ModuleName\Service;

{$uses}

interface BookStoreServiceInterface
{
    public function getBookStoreRepository(): BookStoreRepository;

    public function deleteBookStore(
        BookStore \$bookStore,
    ): void;

    /**
     * @param array<non-empty-string, mixed> \$params
     */
    public function getBookStores(
        array \$params,
    ): array;

    /**
     * @param array<non-empty-string, mixed> \$data
     */
    public function saveBookStore(
        array \$data,
        ?BookStore \$bookStore = null,
    ): BookStore;

    /**
     * @throws NotFoundException
     */
    public function findBookStore(
        string \$uuid,
    ): BookStore;
}

BODY;
    }

    private function dataProviderApi(File $entity, File $repository): string
    {
        $uses = [
            sprintf('use %s;', $entity->getComponent()->getFqcn()),
            sprintf('use %s;', $repository->getComponent()->getFqcn()),
            sprintf('use %s;', Import::DOCTRINE_ORM_QUERYBUILDER),
        ];
        $uses = implode(PHP_EOL, $uses);

        return <<<BODY
<?php

declare(strict_types=1);

namespace Api\ModuleName\Service;

{$uses}

interface BookStoreServiceInterface
{
    public function getBookStoreRepository(): BookStoreRepository;

    public function deleteBookStore(
        BookStore \$bookStore,
    ): void;

    /**
     * @param array<non-empty-string, mixed> \$params
     */
    public function getBookStores(
        array \$params,
    ): QueryBuilder;

    /**
     * @param array<non-empty-string, mixed> \$data
     */
    public function saveBookStore(
        array \$data,
        ?BookStore \$bookStore = null,
    ): BookStore;
}

BODY;
    }
}
