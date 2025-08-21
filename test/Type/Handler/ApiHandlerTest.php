<?php

declare(strict_types=1);

namespace DotTest\Maker\Type\Handler;

use Dot\Maker\Component\Import;
use Dot\Maker\Config;
use Dot\Maker\Context;
use Dot\Maker\FileSystem;
use Dot\Maker\IO\Input;
use Dot\Maker\IO\Output;
use Dot\Maker\Type\Handler;
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

class ApiHandlerTest extends TestCase
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

    public function testCallToInvokeWillNotCreateFileOnEmptyInput(): void
    {
        $file = $this->fileSystem->handler($this->resourceName);
        $this->assertFileDoesNotExist($file->getPath());
        $this->assertFalse($file->exists());

        fwrite($this->inputStream, PHP_EOL);
        rewind($this->inputStream);

        $handler = new Handler($this->fileSystem, $this->context, $this->config, $this->module);
        $handler();

        rewind($this->errorStream);
        $this->assertEmpty(stream_get_contents($this->errorStream));
        $this->assertFalse($file->exists());
        $this->assertFileDoesNotExist($file->getPath());
    }

    public function testCallToInvokeWillOutputErrorAndWillNotCreateFileWhenNameIsInvalid(): void
    {
        $file = $this->fileSystem->handler($this->resourceName);
        $this->assertFalse($file->exists());
        $this->assertFileDoesNotExist($file->getPath());

        fwrite($this->inputStream, '.' . PHP_EOL);
        rewind($this->inputStream);

        $handler = new Handler($this->fileSystem, $this->context, $this->config, $this->module);
        $handler();

        rewind($this->errorStream);
        $this->assertStringContainsString('Invalid Handler name: "."', stream_get_contents($this->errorStream));
        $this->assertFalse($file->exists());
        $this->assertFileDoesNotExist($file->getPath());
    }

    public function testCallToInvokeWillCreateOnlyGetCollectionHandler(): void
    {
        fwrite($this->inputStream, $this->resourceName . PHP_EOL);
        fwrite($this->inputStream, 'yes' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        rewind($this->inputStream);

        $apiGetCollectionHandler = $this->fileSystem->apiGetCollectionHandler($this->resourceName);
        $this->assertFileDoesNotExist($apiGetCollectionHandler->getPath());
        $this->assertFalse($apiGetCollectionHandler->exists());

        $apiGetResourceHandler = $this->fileSystem->apiGetResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($apiGetResourceHandler->getPath());
        $this->assertFalse($apiGetResourceHandler->exists());

        $apiPostResourceHandler = $this->fileSystem->apiPostResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($apiPostResourceHandler->getPath());
        $this->assertFalse($apiPostResourceHandler->exists());

        $apiDeleteResourceHandler = $this->fileSystem->apiDeleteResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($apiDeleteResourceHandler->getPath());
        $this->assertFalse($apiDeleteResourceHandler->exists());

        $apiPatchResourceHandler = $this->fileSystem->apiPatchResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($apiPatchResourceHandler->getPath());
        $this->assertFalse($apiPatchResourceHandler->exists());

        $apiPutResourceHandler = $this->fileSystem->apiPutResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($apiPutResourceHandler->getPath());
        $this->assertFalse($apiPutResourceHandler->exists());

        $handler = new Handler($this->fileSystem, $this->context, $this->config, $this->module);
        $handler();

        $this->assertFileExists($apiGetCollectionHandler->getPath());
        $this->assertTrue($apiGetCollectionHandler->exists());
        $this->assertSame($this->dataProviderGetCollectionHandler(), $apiGetCollectionHandler->read());

        $collection = $this->fileSystem->collection($this->resourceName);
        $this->assertFileExists($collection->getPath());
        $this->assertTrue($collection->exists());

        $this->assertFileDoesNotExist($apiGetResourceHandler->getPath());
        $this->assertFalse($apiGetResourceHandler->exists());

        $this->assertFileDoesNotExist($apiPostResourceHandler->getPath());
        $this->assertFalse($apiPostResourceHandler->exists());

        $this->assertFileDoesNotExist($apiDeleteResourceHandler->getPath());
        $this->assertFalse($apiDeleteResourceHandler->exists());

        $this->assertFileDoesNotExist($apiPatchResourceHandler->getPath());
        $this->assertFalse($apiPatchResourceHandler->exists());

        $this->assertFileDoesNotExist($apiPutResourceHandler->getPath());
        $this->assertFalse($apiPutResourceHandler->exists());

        rewind($this->errorStream);
        $this->assertEmpty(stream_get_contents($this->errorStream));
    }

    public function testCallToInvokeWillCreateOnlyGetResourceHandler(): void
    {
        fwrite($this->inputStream, $this->resourceName . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'yes' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        rewind($this->inputStream);

        $apiGetCollectionHandler = $this->fileSystem->apiGetCollectionHandler($this->resourceName);
        $this->assertFileDoesNotExist($apiGetCollectionHandler->getPath());
        $this->assertFalse($apiGetCollectionHandler->exists());

        $apiGetResourceHandler = $this->fileSystem->apiGetResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($apiGetResourceHandler->getPath());
        $this->assertFalse($apiGetResourceHandler->exists());

        $apiPostResourceHandler = $this->fileSystem->apiPostResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($apiPostResourceHandler->getPath());
        $this->assertFalse($apiPostResourceHandler->exists());

        $apiDeleteResourceHandler = $this->fileSystem->apiDeleteResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($apiDeleteResourceHandler->getPath());
        $this->assertFalse($apiDeleteResourceHandler->exists());

        $apiPatchResourceHandler = $this->fileSystem->apiPatchResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($apiPatchResourceHandler->getPath());
        $this->assertFalse($apiPatchResourceHandler->exists());

        $apiPutResourceHandler = $this->fileSystem->apiPutResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($apiPutResourceHandler->getPath());
        $this->assertFalse($apiPutResourceHandler->exists());

        $handler = new Handler($this->fileSystem, $this->context, $this->config, $this->module);
        $handler();

        $this->assertFileDoesNotExist($apiGetCollectionHandler->getPath());
        $this->assertFalse($apiGetCollectionHandler->exists());

        $this->assertFileExists($apiGetResourceHandler->getPath());
        $this->assertTrue($apiGetResourceHandler->exists());
        $this->assertSame($this->dataProviderGetResourceHandler(), $apiGetResourceHandler->read());

        $this->assertFileDoesNotExist($apiPostResourceHandler->getPath());
        $this->assertFalse($apiPostResourceHandler->exists());

        $this->assertFileDoesNotExist($apiDeleteResourceHandler->getPath());
        $this->assertFalse($apiDeleteResourceHandler->exists());

        $this->assertFileDoesNotExist($apiPatchResourceHandler->getPath());
        $this->assertFalse($apiPatchResourceHandler->exists());

        $this->assertFileDoesNotExist($apiPutResourceHandler->getPath());
        $this->assertFalse($apiPutResourceHandler->exists());

        rewind($this->errorStream);
        $this->assertEmpty(stream_get_contents($this->errorStream));
    }

    public function testCallToInvokeWillCreateOnlyPostResourceHandler(): void
    {
        fwrite($this->inputStream, $this->resourceName . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'yes' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        rewind($this->inputStream);

        $apiGetCollectionHandler = $this->fileSystem->apiGetCollectionHandler($this->resourceName);
        $this->assertFileDoesNotExist($apiGetCollectionHandler->getPath());
        $this->assertFalse($apiGetCollectionHandler->exists());

        $apiGetResourceHandler = $this->fileSystem->apiGetResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($apiGetResourceHandler->getPath());
        $this->assertFalse($apiGetResourceHandler->exists());

        $apiPostResourceHandler = $this->fileSystem->apiPostResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($apiPostResourceHandler->getPath());
        $this->assertFalse($apiPostResourceHandler->exists());

        $apiDeleteResourceHandler = $this->fileSystem->apiDeleteResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($apiDeleteResourceHandler->getPath());
        $this->assertFalse($apiDeleteResourceHandler->exists());

        $apiPatchResourceHandler = $this->fileSystem->apiPatchResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($apiPatchResourceHandler->getPath());
        $this->assertFalse($apiPatchResourceHandler->exists());

        $apiPutResourceHandler = $this->fileSystem->apiPutResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($apiPutResourceHandler->getPath());
        $this->assertFalse($apiPutResourceHandler->exists());

        $handler = new Handler($this->fileSystem, $this->context, $this->config, $this->module);
        $handler();

        $this->assertFileDoesNotExist($apiGetCollectionHandler->getPath());
        $this->assertFalse($apiGetCollectionHandler->exists());

        $this->assertFileDoesNotExist($apiGetResourceHandler->getPath());
        $this->assertFalse($apiGetResourceHandler->exists());

        $this->assertFileExists($apiPostResourceHandler->getPath());
        $this->assertTrue($apiPostResourceHandler->exists());
        $this->assertSame($this->dataProviderPostResourceHandler(), $apiPostResourceHandler->read());

        $createResourceInputFilter = $this->fileSystem->createResourceInputFilter($this->resourceName);
        $this->assertFileExists($createResourceInputFilter->getPath());
        $this->assertTrue($createResourceInputFilter->exists());

        $this->assertFileDoesNotExist($apiDeleteResourceHandler->getPath());
        $this->assertFalse($apiDeleteResourceHandler->exists());

        $this->assertFileDoesNotExist($apiPatchResourceHandler->getPath());
        $this->assertFalse($apiPatchResourceHandler->exists());

        $this->assertFileDoesNotExist($apiPutResourceHandler->getPath());
        $this->assertFalse($apiPutResourceHandler->exists());

        rewind($this->errorStream);
        $this->assertEmpty(stream_get_contents($this->errorStream));
    }

    public function testCallToInvokeWillCreateOnlyDeleteResourceHandler(): void
    {
        fwrite($this->inputStream, $this->resourceName . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'yes' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        rewind($this->inputStream);

        $apiGetCollectionHandler = $this->fileSystem->apiGetCollectionHandler($this->resourceName);
        $this->assertFileDoesNotExist($apiGetCollectionHandler->getPath());
        $this->assertFalse($apiGetCollectionHandler->exists());

        $apiGetResourceHandler = $this->fileSystem->apiGetResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($apiGetResourceHandler->getPath());
        $this->assertFalse($apiGetResourceHandler->exists());

        $apiPostResourceHandler = $this->fileSystem->apiPostResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($apiPostResourceHandler->getPath());
        $this->assertFalse($apiPostResourceHandler->exists());

        $apiDeleteResourceHandler = $this->fileSystem->apiDeleteResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($apiDeleteResourceHandler->getPath());
        $this->assertFalse($apiDeleteResourceHandler->exists());

        $apiPatchResourceHandler = $this->fileSystem->apiPatchResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($apiPatchResourceHandler->getPath());
        $this->assertFalse($apiPatchResourceHandler->exists());

        $apiPutResourceHandler = $this->fileSystem->apiPutResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($apiPutResourceHandler->getPath());
        $this->assertFalse($apiPutResourceHandler->exists());

        $handler = new Handler($this->fileSystem, $this->context, $this->config, $this->module);
        $handler();

        $this->assertFileDoesNotExist($apiGetCollectionHandler->getPath());
        $this->assertFalse($apiGetCollectionHandler->exists());

        $this->assertFileDoesNotExist($apiGetResourceHandler->getPath());
        $this->assertFalse($apiGetResourceHandler->exists());

        $this->assertFileDoesNotExist($apiPostResourceHandler->getPath());
        $this->assertFalse($apiPostResourceHandler->exists());

        $this->assertFileExists($apiDeleteResourceHandler->getPath());
        $this->assertTrue($apiDeleteResourceHandler->exists());
        $this->assertSame($this->dataProviderDeleteResourceHandler(), $apiDeleteResourceHandler->read());

        $this->assertFileDoesNotExist($apiPatchResourceHandler->getPath());
        $this->assertFalse($apiPatchResourceHandler->exists());

        $this->assertFileDoesNotExist($apiPutResourceHandler->getPath());
        $this->assertFalse($apiPutResourceHandler->exists());

        rewind($this->errorStream);
        $this->assertEmpty(stream_get_contents($this->errorStream));
    }

    public function testCallToInvokeWillCreateOnlyPatchResourceHandler(): void
    {
        fwrite($this->inputStream, $this->resourceName . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'yes' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        rewind($this->inputStream);

        $apiGetCollectionHandler = $this->fileSystem->apiGetCollectionHandler($this->resourceName);
        $this->assertFileDoesNotExist($apiGetCollectionHandler->getPath());
        $this->assertFalse($apiGetCollectionHandler->exists());

        $apiGetResourceHandler = $this->fileSystem->apiGetResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($apiGetResourceHandler->getPath());
        $this->assertFalse($apiGetResourceHandler->exists());

        $apiPostResourceHandler = $this->fileSystem->apiPostResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($apiPostResourceHandler->getPath());
        $this->assertFalse($apiPostResourceHandler->exists());

        $apiDeleteResourceHandler = $this->fileSystem->apiDeleteResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($apiDeleteResourceHandler->getPath());
        $this->assertFalse($apiDeleteResourceHandler->exists());

        $apiPatchResourceHandler = $this->fileSystem->apiPatchResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($apiPatchResourceHandler->getPath());
        $this->assertFalse($apiPatchResourceHandler->exists());

        $apiPutResourceHandler = $this->fileSystem->apiPutResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($apiPutResourceHandler->getPath());
        $this->assertFalse($apiPutResourceHandler->exists());

        $handler = new Handler($this->fileSystem, $this->context, $this->config, $this->module);
        $handler();

        $this->assertFileDoesNotExist($apiGetCollectionHandler->getPath());
        $this->assertFalse($apiGetCollectionHandler->exists());

        $this->assertFileDoesNotExist($apiGetResourceHandler->getPath());
        $this->assertFalse($apiGetResourceHandler->exists());

        $this->assertFileDoesNotExist($apiPostResourceHandler->getPath());
        $this->assertFalse($apiPostResourceHandler->exists());

        $this->assertFileDoesNotExist($apiDeleteResourceHandler->getPath());
        $this->assertFalse($apiDeleteResourceHandler->exists());

        $this->assertFileExists($apiPatchResourceHandler->getPath());
        $this->assertTrue($apiPatchResourceHandler->exists());
        $this->assertSame($this->dataProviderPatchResourceHandler(), $apiPatchResourceHandler->read());

        $editResourceInputFilter = $this->fileSystem->editResourceInputFilter($this->resourceName);
        $this->assertFileExists($editResourceInputFilter->getPath());
        $this->assertTrue($editResourceInputFilter->exists());

        $this->assertFileDoesNotExist($apiPutResourceHandler->getPath());
        $this->assertFalse($apiPutResourceHandler->exists());

        rewind($this->errorStream);
        $this->assertEmpty(stream_get_contents($this->errorStream));
    }

    public function testCallToInvokeWillCreateOnlyPutResourceHandler(): void
    {
        fwrite($this->inputStream, $this->resourceName . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'yes' . PHP_EOL);
        rewind($this->inputStream);

        $apiGetCollectionHandler = $this->fileSystem->apiGetCollectionHandler($this->resourceName);
        $this->assertFileDoesNotExist($apiGetCollectionHandler->getPath());
        $this->assertFalse($apiGetCollectionHandler->exists());

        $apiGetResourceHandler = $this->fileSystem->apiGetResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($apiGetResourceHandler->getPath());
        $this->assertFalse($apiGetResourceHandler->exists());

        $apiPostResourceHandler = $this->fileSystem->apiPostResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($apiPostResourceHandler->getPath());
        $this->assertFalse($apiPostResourceHandler->exists());

        $apiDeleteResourceHandler = $this->fileSystem->apiDeleteResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($apiDeleteResourceHandler->getPath());
        $this->assertFalse($apiDeleteResourceHandler->exists());

        $apiPatchResourceHandler = $this->fileSystem->apiPatchResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($apiPatchResourceHandler->getPath());
        $this->assertFalse($apiPatchResourceHandler->exists());

        $apiPutResourceHandler = $this->fileSystem->apiPutResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($apiPutResourceHandler->getPath());
        $this->assertFalse($apiPutResourceHandler->exists());

        $handler = new Handler($this->fileSystem, $this->context, $this->config, $this->module);
        $handler();

        $this->assertFileDoesNotExist($apiGetCollectionHandler->getPath());
        $this->assertFalse($apiGetCollectionHandler->exists());

        $this->assertFileDoesNotExist($apiGetResourceHandler->getPath());
        $this->assertFalse($apiGetResourceHandler->exists());

        $this->assertFileDoesNotExist($apiPostResourceHandler->getPath());
        $this->assertFalse($apiPostResourceHandler->exists());

        $this->assertFileDoesNotExist($apiDeleteResourceHandler->getPath());
        $this->assertFalse($apiDeleteResourceHandler->exists());

        $this->assertFileDoesNotExist($apiPatchResourceHandler->getPath());
        $this->assertFalse($apiPatchResourceHandler->exists());

        $this->assertFileExists($apiPutResourceHandler->getPath());
        $this->assertTrue($apiPutResourceHandler->exists());
        $this->assertSame($this->dataProviderPutResourceHandler(), $apiPutResourceHandler->read());

        $replaceResourceInputFilter = $this->fileSystem->replaceResourceInputFilter($this->resourceName);
        $this->assertFileExists($replaceResourceInputFilter->getPath());
        $this->assertTrue($replaceResourceInputFilter->exists());

        rewind($this->errorStream);
        $this->assertEmpty(stream_get_contents($this->errorStream));
    }

    public function testCallToCreateWillCreateOnlyGetCollectionHandler(): void
    {
        fwrite($this->inputStream, $this->resourceName . PHP_EOL);
        fwrite($this->inputStream, 'yes' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        rewind($this->inputStream);

        $apiGetCollectionHandler = $this->fileSystem->apiGetCollectionHandler($this->resourceName);
        $this->assertFileDoesNotExist($apiGetCollectionHandler->getPath());
        $this->assertFalse($apiGetCollectionHandler->exists());

        $apiGetResourceHandler = $this->fileSystem->apiGetResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($apiGetResourceHandler->getPath());
        $this->assertFalse($apiGetResourceHandler->exists());

        $apiPostResourceHandler = $this->fileSystem->apiPostResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($apiPostResourceHandler->getPath());
        $this->assertFalse($apiPostResourceHandler->exists());

        $apiDeleteResourceHandler = $this->fileSystem->apiDeleteResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($apiDeleteResourceHandler->getPath());
        $this->assertFalse($apiDeleteResourceHandler->exists());

        $apiPatchResourceHandler = $this->fileSystem->apiPatchResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($apiPatchResourceHandler->getPath());
        $this->assertFalse($apiPatchResourceHandler->exists());

        $apiPutResourceHandler = $this->fileSystem->apiPutResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($apiPutResourceHandler->getPath());
        $this->assertFalse($apiPutResourceHandler->exists());

        $handler = new Handler($this->fileSystem, $this->context, $this->config, $this->module);
        $handler->create($this->resourceName);

        $this->assertFileExists($apiGetCollectionHandler->getPath());
        $this->assertTrue($apiGetCollectionHandler->exists());
        $this->assertSame($this->dataProviderGetCollectionHandler(), $apiGetCollectionHandler->read());

        $collection = $this->fileSystem->collection($this->resourceName);
        $this->assertFileExists($collection->getPath());
        $this->assertTrue($collection->exists());

        $this->assertFileDoesNotExist($apiGetResourceHandler->getPath());
        $this->assertFalse($apiGetResourceHandler->exists());

        $this->assertFileDoesNotExist($apiPostResourceHandler->getPath());
        $this->assertFalse($apiPostResourceHandler->exists());

        $this->assertFileDoesNotExist($apiDeleteResourceHandler->getPath());
        $this->assertFalse($apiDeleteResourceHandler->exists());

        $this->assertFileDoesNotExist($apiPatchResourceHandler->getPath());
        $this->assertFalse($apiPatchResourceHandler->exists());

        $this->assertFileDoesNotExist($apiPutResourceHandler->getPath());
        $this->assertFalse($apiPutResourceHandler->exists());

        rewind($this->errorStream);
        $this->assertEmpty(stream_get_contents($this->errorStream));
    }

    public function testCallToCreateWillCreateOnlyGetResourceHandler(): void
    {
        fwrite($this->inputStream, $this->resourceName . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'yes' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        rewind($this->inputStream);

        $apiGetCollectionHandler = $this->fileSystem->apiGetCollectionHandler($this->resourceName);
        $this->assertFileDoesNotExist($apiGetCollectionHandler->getPath());
        $this->assertFalse($apiGetCollectionHandler->exists());

        $apiGetResourceHandler = $this->fileSystem->apiGetResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($apiGetResourceHandler->getPath());
        $this->assertFalse($apiGetResourceHandler->exists());

        $apiPostResourceHandler = $this->fileSystem->apiPostResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($apiPostResourceHandler->getPath());
        $this->assertFalse($apiPostResourceHandler->exists());

        $apiDeleteResourceHandler = $this->fileSystem->apiDeleteResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($apiDeleteResourceHandler->getPath());
        $this->assertFalse($apiDeleteResourceHandler->exists());

        $apiPatchResourceHandler = $this->fileSystem->apiPatchResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($apiPatchResourceHandler->getPath());
        $this->assertFalse($apiPatchResourceHandler->exists());

        $apiPutResourceHandler = $this->fileSystem->apiPutResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($apiPutResourceHandler->getPath());
        $this->assertFalse($apiPutResourceHandler->exists());

        $handler = new Handler($this->fileSystem, $this->context, $this->config, $this->module);
        $handler->create($this->resourceName);

        $this->assertFileDoesNotExist($apiGetCollectionHandler->getPath());
        $this->assertFalse($apiGetCollectionHandler->exists());

        $this->assertFileExists($apiGetResourceHandler->getPath());
        $this->assertTrue($apiGetResourceHandler->exists());
        $this->assertSame($this->dataProviderGetResourceHandler(), $apiGetResourceHandler->read());

        $this->assertFileDoesNotExist($apiPostResourceHandler->getPath());
        $this->assertFalse($apiPostResourceHandler->exists());

        $this->assertFileDoesNotExist($apiDeleteResourceHandler->getPath());
        $this->assertFalse($apiDeleteResourceHandler->exists());

        $this->assertFileDoesNotExist($apiPatchResourceHandler->getPath());
        $this->assertFalse($apiPatchResourceHandler->exists());

        $this->assertFileDoesNotExist($apiPutResourceHandler->getPath());
        $this->assertFalse($apiPutResourceHandler->exists());

        rewind($this->errorStream);
        $this->assertEmpty(stream_get_contents($this->errorStream));
    }

    public function testCallToCreateWillCreateOnlyPostResourceHandler(): void
    {
        fwrite($this->inputStream, $this->resourceName . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'yes' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        rewind($this->inputStream);

        $apiGetCollectionHandler = $this->fileSystem->apiGetCollectionHandler($this->resourceName);
        $this->assertFileDoesNotExist($apiGetCollectionHandler->getPath());
        $this->assertFalse($apiGetCollectionHandler->exists());

        $apiGetResourceHandler = $this->fileSystem->apiGetResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($apiGetResourceHandler->getPath());
        $this->assertFalse($apiGetResourceHandler->exists());

        $apiPostResourceHandler = $this->fileSystem->apiPostResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($apiPostResourceHandler->getPath());
        $this->assertFalse($apiPostResourceHandler->exists());

        $apiDeleteResourceHandler = $this->fileSystem->apiDeleteResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($apiDeleteResourceHandler->getPath());
        $this->assertFalse($apiDeleteResourceHandler->exists());

        $apiPatchResourceHandler = $this->fileSystem->apiPatchResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($apiPatchResourceHandler->getPath());
        $this->assertFalse($apiPatchResourceHandler->exists());

        $apiPutResourceHandler = $this->fileSystem->apiPutResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($apiPutResourceHandler->getPath());
        $this->assertFalse($apiPutResourceHandler->exists());

        $handler = new Handler($this->fileSystem, $this->context, $this->config, $this->module);
        $handler->create($this->resourceName);

        $this->assertFileDoesNotExist($apiGetCollectionHandler->getPath());
        $this->assertFalse($apiGetCollectionHandler->exists());

        $this->assertFileDoesNotExist($apiGetResourceHandler->getPath());
        $this->assertFalse($apiGetResourceHandler->exists());

        $this->assertFileExists($apiPostResourceHandler->getPath());
        $this->assertTrue($apiPostResourceHandler->exists());
        $this->assertSame($this->dataProviderPostResourceHandler(), $apiPostResourceHandler->read());

        $createResourceInputFilter = $this->fileSystem->createResourceInputFilter($this->resourceName);
        $this->assertFileExists($createResourceInputFilter->getPath());
        $this->assertTrue($createResourceInputFilter->exists());

        $this->assertFileDoesNotExist($apiDeleteResourceHandler->getPath());
        $this->assertFalse($apiDeleteResourceHandler->exists());

        $this->assertFileDoesNotExist($apiPatchResourceHandler->getPath());
        $this->assertFalse($apiPatchResourceHandler->exists());

        $this->assertFileDoesNotExist($apiPutResourceHandler->getPath());
        $this->assertFalse($apiPutResourceHandler->exists());

        rewind($this->errorStream);
        $this->assertEmpty(stream_get_contents($this->errorStream));
    }

    public function testCallToCreateWillCreateOnlyDeleteResourceHandler(): void
    {
        fwrite($this->inputStream, $this->resourceName . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'yes' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        rewind($this->inputStream);

        $apiGetCollectionHandler = $this->fileSystem->apiGetCollectionHandler($this->resourceName);
        $this->assertFileDoesNotExist($apiGetCollectionHandler->getPath());
        $this->assertFalse($apiGetCollectionHandler->exists());

        $apiGetResourceHandler = $this->fileSystem->apiGetResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($apiGetResourceHandler->getPath());
        $this->assertFalse($apiGetResourceHandler->exists());

        $apiPostResourceHandler = $this->fileSystem->apiPostResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($apiPostResourceHandler->getPath());
        $this->assertFalse($apiPostResourceHandler->exists());

        $apiDeleteResourceHandler = $this->fileSystem->apiDeleteResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($apiDeleteResourceHandler->getPath());
        $this->assertFalse($apiDeleteResourceHandler->exists());

        $apiPatchResourceHandler = $this->fileSystem->apiPatchResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($apiPatchResourceHandler->getPath());
        $this->assertFalse($apiPatchResourceHandler->exists());

        $apiPutResourceHandler = $this->fileSystem->apiPutResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($apiPutResourceHandler->getPath());
        $this->assertFalse($apiPutResourceHandler->exists());

        $handler = new Handler($this->fileSystem, $this->context, $this->config, $this->module);
        $handler->create($this->resourceName);

        $this->assertFileDoesNotExist($apiGetCollectionHandler->getPath());
        $this->assertFalse($apiGetCollectionHandler->exists());

        $this->assertFileDoesNotExist($apiGetResourceHandler->getPath());
        $this->assertFalse($apiGetResourceHandler->exists());

        $this->assertFileDoesNotExist($apiPostResourceHandler->getPath());
        $this->assertFalse($apiPostResourceHandler->exists());

        $this->assertFileExists($apiDeleteResourceHandler->getPath());
        $this->assertTrue($apiDeleteResourceHandler->exists());
        $this->assertSame($this->dataProviderDeleteResourceHandler(), $apiDeleteResourceHandler->read());

        $this->assertFileDoesNotExist($apiPatchResourceHandler->getPath());
        $this->assertFalse($apiPatchResourceHandler->exists());

        $this->assertFileDoesNotExist($apiPutResourceHandler->getPath());
        $this->assertFalse($apiPutResourceHandler->exists());

        rewind($this->errorStream);
        $this->assertEmpty(stream_get_contents($this->errorStream));
    }

    public function testCallToCreateWillCreateOnlyPatchResourceHandler(): void
    {
        fwrite($this->inputStream, $this->resourceName . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'yes' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        rewind($this->inputStream);

        $apiGetCollectionHandler = $this->fileSystem->apiGetCollectionHandler($this->resourceName);
        $this->assertFileDoesNotExist($apiGetCollectionHandler->getPath());
        $this->assertFalse($apiGetCollectionHandler->exists());

        $apiGetResourceHandler = $this->fileSystem->apiGetResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($apiGetResourceHandler->getPath());
        $this->assertFalse($apiGetResourceHandler->exists());

        $apiPostResourceHandler = $this->fileSystem->apiPostResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($apiPostResourceHandler->getPath());
        $this->assertFalse($apiPostResourceHandler->exists());

        $apiDeleteResourceHandler = $this->fileSystem->apiDeleteResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($apiDeleteResourceHandler->getPath());
        $this->assertFalse($apiDeleteResourceHandler->exists());

        $apiPatchResourceHandler = $this->fileSystem->apiPatchResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($apiPatchResourceHandler->getPath());
        $this->assertFalse($apiPatchResourceHandler->exists());

        $apiPutResourceHandler = $this->fileSystem->apiPutResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($apiPutResourceHandler->getPath());
        $this->assertFalse($apiPutResourceHandler->exists());

        $handler = new Handler($this->fileSystem, $this->context, $this->config, $this->module);
        $handler->create($this->resourceName);

        $this->assertFileDoesNotExist($apiGetCollectionHandler->getPath());
        $this->assertFalse($apiGetCollectionHandler->exists());

        $this->assertFileDoesNotExist($apiGetResourceHandler->getPath());
        $this->assertFalse($apiGetResourceHandler->exists());

        $this->assertFileDoesNotExist($apiPostResourceHandler->getPath());
        $this->assertFalse($apiPostResourceHandler->exists());

        $this->assertFileDoesNotExist($apiDeleteResourceHandler->getPath());
        $this->assertFalse($apiDeleteResourceHandler->exists());

        $this->assertFileExists($apiPatchResourceHandler->getPath());
        $this->assertTrue($apiPatchResourceHandler->exists());
        $this->assertSame($this->dataProviderPatchResourceHandler(), $apiPatchResourceHandler->read());

        $editResourceInputFilter = $this->fileSystem->editResourceInputFilter($this->resourceName);
        $this->assertFileExists($editResourceInputFilter->getPath());
        $this->assertTrue($editResourceInputFilter->exists());

        $this->assertFileDoesNotExist($apiPutResourceHandler->getPath());
        $this->assertFalse($apiPutResourceHandler->exists());

        rewind($this->errorStream);
        $this->assertEmpty(stream_get_contents($this->errorStream));
    }

    public function testCallToCreateWillCreateOnlyPutResourceHandler(): void
    {
        fwrite($this->inputStream, $this->resourceName . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'yes' . PHP_EOL);
        rewind($this->inputStream);

        $apiGetCollectionHandler = $this->fileSystem->apiGetCollectionHandler($this->resourceName);
        $this->assertFileDoesNotExist($apiGetCollectionHandler->getPath());
        $this->assertFalse($apiGetCollectionHandler->exists());

        $apiGetResourceHandler = $this->fileSystem->apiGetResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($apiGetResourceHandler->getPath());
        $this->assertFalse($apiGetResourceHandler->exists());

        $apiPostResourceHandler = $this->fileSystem->apiPostResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($apiPostResourceHandler->getPath());
        $this->assertFalse($apiPostResourceHandler->exists());

        $apiDeleteResourceHandler = $this->fileSystem->apiDeleteResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($apiDeleteResourceHandler->getPath());
        $this->assertFalse($apiDeleteResourceHandler->exists());

        $apiPatchResourceHandler = $this->fileSystem->apiPatchResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($apiPatchResourceHandler->getPath());
        $this->assertFalse($apiPatchResourceHandler->exists());

        $apiPutResourceHandler = $this->fileSystem->apiPutResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($apiPutResourceHandler->getPath());
        $this->assertFalse($apiPutResourceHandler->exists());

        $handler = new Handler($this->fileSystem, $this->context, $this->config, $this->module);
        $handler->create($this->resourceName);

        $this->assertFileDoesNotExist($apiGetCollectionHandler->getPath());
        $this->assertFalse($apiGetCollectionHandler->exists());

        $this->assertFileDoesNotExist($apiGetResourceHandler->getPath());
        $this->assertFalse($apiGetResourceHandler->exists());

        $this->assertFileDoesNotExist($apiPostResourceHandler->getPath());
        $this->assertFalse($apiPostResourceHandler->exists());

        $this->assertFileDoesNotExist($apiDeleteResourceHandler->getPath());
        $this->assertFalse($apiDeleteResourceHandler->exists());

        $this->assertFileDoesNotExist($apiPatchResourceHandler->getPath());
        $this->assertFalse($apiPatchResourceHandler->exists());

        $this->assertFileExists($apiPutResourceHandler->getPath());
        $this->assertTrue($apiPutResourceHandler->exists());
        $this->assertSame($this->dataProviderPutResourceHandler(), $apiPutResourceHandler->read());

        $replaceResourceInputFilter = $this->fileSystem->replaceResourceInputFilter($this->resourceName);
        $this->assertFileExists($replaceResourceInputFilter->getPath());
        $this->assertTrue($replaceResourceInputFilter->exists());

        rewind($this->errorStream);
        $this->assertEmpty(stream_get_contents($this->errorStream));
    }

    private function dataProviderGetCollectionHandler(): string
    {
        $collection       = $this->fileSystem->collection($this->resourceName);
        $serviceInterface = $this->fileSystem->serviceInterface($this->resourceName);

        $uses = [
            sprintf('use %s;', $this->import->getAbstractHandlerFqcn()),
            sprintf('use %s;', $collection->getComponent()->getFqcn()),
            sprintf('use %s;', $serviceInterface->getComponent()->getFqcn()),
            sprintf('use %s;', Import::DOT_DEPENDENCYINJECTION_ATTRIBUTE_INJECT),
            sprintf('use %s;', Import::PSR_HTTP_MESSAGE_RESPONSEINTERFACE),
            sprintf('use %s;', Import::PSR_HTTP_MESSAGE_SERVERREQUESTINTERFACE),
        ];
        $uses = implode(PHP_EOL, $uses);

        return <<<BODY
<?php

declare(strict_types=1);

namespace Api\ModuleName\Handler\BookStore;

{$uses}

class GetBookStoreCollectionHandler extends AbstractHandler
{
    #[Inject(
        BookStoreServiceInterface::class,
    )]
    public function __construct(
        protected BookStoreServiceInterface \$bookStoreService,
    ) {
    }

    public function handle(
        ServerRequestInterface \$request,
    ): ResponseInterface {
        return \$this->createResponse(
            \$request,
            new BookStoreCollection(\$this->bookStoreService->getBookStores(\$request->getQueryParams()))
        );
    }
}

BODY;
    }

    private function dataProviderGetResourceHandler(): string
    {
        $entity = $this->fileSystem->entity($this->resourceName);

        $uses = [
            sprintf('use %s;', $this->import->getResourceAttributeFqcn()),
            sprintf('use %s;', $this->import->getAbstractHandlerFqcn()),
            sprintf('use %s;', $entity->getComponent()->getFqcn()),
            sprintf('use %s;', Import::PSR_HTTP_MESSAGE_RESPONSEINTERFACE),
            sprintf('use %s;', Import::PSR_HTTP_MESSAGE_SERVERREQUESTINTERFACE),
        ];
        $uses = implode(PHP_EOL, $uses);

        return <<<BODY
<?php

declare(strict_types=1);

namespace Api\ModuleName\Handler\BookStore;

{$uses}

class GetBookStoreResourceHandler extends AbstractHandler
{
    #[Resource(entity: BookStore::class)]
    public function handle(
        ServerRequestInterface \$request,
    ): ResponseInterface {
        return \$this->createResponse(
            \$request,
            \$request->getAttribute(BookStore::class)
        );
    }
}

BODY;
    }

    private function dataProviderPostResourceHandler(): string
    {
        $createResourceInputFilter = $this->fileSystem->createResourceInputFilter($this->resourceName);
        $serviceInterface          = $this->fileSystem->serviceInterface($this->resourceName);

        $uses = [
            sprintf('use %s;', $this->import->getBadRequestExceptionFqcn()),
            sprintf('use %s;', $this->import->getAbstractHandlerFqcn()),
            sprintf('use %s;', $this->import->getAppMessageFqcn()),
            sprintf('use %s;', $createResourceInputFilter->getComponent()->getFqcn()),
            sprintf('use %s;', $serviceInterface->getComponent()->getFqcn()),
            sprintf('use %s;', Import::DOT_DEPENDENCYINJECTION_ATTRIBUTE_INJECT),
            sprintf('use %s;', Import::PSR_HTTP_MESSAGE_RESPONSEINTERFACE),
            sprintf('use %s;', Import::PSR_HTTP_MESSAGE_SERVERREQUESTINTERFACE),
        ];
        $uses = implode(PHP_EOL, $uses);

        return <<<BODY
<?php

declare(strict_types=1);

namespace Api\ModuleName\Handler\BookStore;

{$uses}

class PostBookStoreResourceHandler extends AbstractHandler
{
    #[Inject(
        BookStoreServiceInterface::class,
        CreateBookStoreInputFilter::class,
    )]
    public function __construct(
        protected BookStoreServiceInterface \$bookStoreService,
        protected CreateBookStoreInputFilter \$inputFilter,
    ) {
    }

    /**
     * @throws BadRequestException
     */
    public function handle(
        ServerRequestInterface \$request,
    ): ResponseInterface {
        \$this->inputFilter->setData((array) \$request->getParsedBody());
        if (! \$this->inputFilter->isValid()) {
            throw BadRequestException::create(
                detail: Message::VALIDATOR_INVALID_DATA,
                additional: ['errors' => \$this->inputFilter->getMessages()]
            );
        }

        /** @var non-empty-array<non-empty-string, mixed> \$data */
        \$data = (array) \$this->inputFilter->getValues();

        return \$this->createdResponse(\$request, \$this->bookStoreService->saveBookStore(\$data));
    }
}

BODY;
    }

    private function dataProviderDeleteResourceHandler(): string
    {
        $serviceInterface = $this->fileSystem->serviceInterface($this->resourceName);
        $entity           = $this->fileSystem->entity($this->resourceName);

        $uses = [
            sprintf('use %s;', $this->import->getResourceAttributeFqcn()),
            sprintf('use %s;', $this->import->getAbstractHandlerFqcn()),
            sprintf('use %s;', $entity->getComponent()->getFqcn()),
            sprintf('use %s;', $serviceInterface->getComponent()->getFqcn()),
            sprintf('use %s;', Import::DOT_DEPENDENCYINJECTION_ATTRIBUTE_INJECT),
            sprintf('use %s;', Import::PSR_HTTP_MESSAGE_RESPONSEINTERFACE),
            sprintf('use %s;', Import::PSR_HTTP_MESSAGE_SERVERREQUESTINTERFACE),
        ];
        $uses = implode(PHP_EOL, $uses);

        return <<<BODY
<?php

declare(strict_types=1);

namespace Api\ModuleName\Handler\BookStore;

{$uses}

class DeleteBookStoreResourceHandler extends AbstractHandler
{
    #[Inject(
        BookStoreServiceInterface::class,
    )]
    public function __construct(
        protected BookStoreServiceInterface \$bookStoreService,
    ) {
    }

    #[Resource(entity: BookStore::class)]
    public function handle(
        ServerRequestInterface \$request,
    ): ResponseInterface {
        \$this->bookStoreService->deleteBookStore(
            \$request->getAttribute(BookStore::class)
        );

        return \$this->noContentResponse();
    }
}

BODY;
    }

    private function dataProviderPatchResourceHandler(): string
    {
        $editResourceInputFilter = $this->fileSystem->editResourceInputFilter($this->resourceName);
        $serviceInterface        = $this->fileSystem->serviceInterface($this->resourceName);
        $entity                  = $this->fileSystem->entity($this->resourceName);

        $uses = [
            sprintf('use %s;', $this->import->getResourceAttributeFqcn()),
            sprintf('use %s;', $this->import->getBadRequestExceptionFqcn()),
            sprintf('use %s;', $this->import->getAbstractHandlerFqcn()),
            sprintf('use %s;', $this->import->getAppMessageFqcn()),
            sprintf('use %s;', $entity->getComponent()->getFqcn()),
            sprintf('use %s;', $editResourceInputFilter->getComponent()->getFqcn()),
            sprintf('use %s;', $serviceInterface->getComponent()->getFqcn()),
            sprintf('use %s;', Import::DOT_DEPENDENCYINJECTION_ATTRIBUTE_INJECT),
            sprintf('use %s;', Import::PSR_HTTP_MESSAGE_RESPONSEINTERFACE),
            sprintf('use %s;', Import::PSR_HTTP_MESSAGE_SERVERREQUESTINTERFACE),
        ];
        $uses = implode(PHP_EOL, $uses);

        return <<<BODY
<?php

declare(strict_types=1);

namespace Api\ModuleName\Handler\BookStore;

{$uses}

class PatchBookStoreResourceHandler extends AbstractHandler
{
    #[Inject(
        BookStoreServiceInterface::class,
        EditBookStoreInputFilter::class,
    )]
    public function __construct(
        protected BookStoreServiceInterface \$bookStoreService,
        protected EditBookStoreInputFilter \$inputFilter,
    ) {
    }

    /**
     * @throws BadRequestException
     */
    #[Resource(entity: BookStore::class)]
    public function handle(
        ServerRequestInterface \$request,
    ): ResponseInterface {
        \$this->inputFilter->setData((array) \$request->getParsedBody());
        if (! \$this->inputFilter->isValid()) {
            throw BadRequestException::create(
                detail: Message::VALIDATOR_INVALID_DATA,
                additional: ['errors' => \$this->inputFilter->getMessages()]
            );
        }

        /** @var non-empty-array<non-empty-string, mixed> \$data */
        \$data = (array) \$this->inputFilter->getValues();

        return \$this->createResponse(
            \$request,
            \$this->bookStoreService->saveBookStore(\$data, \$request->getAttribute(BookStore::class))
        );
    }
}

BODY;
    }

    private function dataProviderPutResourceHandler(): string
    {
        $replaceResourceInputFilter = $this->fileSystem->replaceResourceInputFilter($this->resourceName);
        $serviceInterface           = $this->fileSystem->serviceInterface($this->resourceName);
        $entity                     = $this->fileSystem->entity($this->resourceName);

        $uses = [
            sprintf('use %s;', $this->import->getResourceAttributeFqcn()),
            sprintf('use %s;', $this->import->getBadRequestExceptionFqcn()),
            sprintf('use %s;', $this->import->getAbstractHandlerFqcn()),
            sprintf('use %s;', $this->import->getAppMessageFqcn()),
            sprintf('use %s;', $entity->getComponent()->getFqcn()),
            sprintf('use %s;', $replaceResourceInputFilter->getComponent()->getFqcn()),
            sprintf('use %s;', $serviceInterface->getComponent()->getFqcn()),
            sprintf('use %s;', Import::DOT_DEPENDENCYINJECTION_ATTRIBUTE_INJECT),
            sprintf('use %s;', Import::PSR_HTTP_MESSAGE_RESPONSEINTERFACE),
            sprintf('use %s;', Import::PSR_HTTP_MESSAGE_SERVERREQUESTINTERFACE),
        ];
        $uses = implode(PHP_EOL, $uses);

        return <<<BODY
<?php

declare(strict_types=1);

namespace Api\ModuleName\Handler\BookStore;

{$uses}

class PutBookStoreResourceHandler extends AbstractHandler
{
    #[Inject(
        BookStoreServiceInterface::class,
        ReplaceBookStoreInputFilter::class,
    )]
    public function __construct(
        protected BookStoreServiceInterface \$bookStoreService,
        protected ReplaceBookStoreInputFilter \$inputFilter,
    ) {
    }

    /**
     * @throws BadRequestException
     */
    #[Resource(entity: BookStore::class)]
    public function handle(
        ServerRequestInterface \$request,
    ): ResponseInterface {
        \$this->inputFilter->setData((array) \$request->getParsedBody());
        if (! \$this->inputFilter->isValid()) {
            throw BadRequestException::create(
                detail: Message::VALIDATOR_INVALID_DATA,
                additional: ['errors' => \$this->inputFilter->getMessages()]
            );
        }

        /** @var non-empty-array<non-empty-string, mixed> \$data */
        \$data = (array) \$this->inputFilter->getValues();

        return \$this->createResponse(
            \$request,
            \$this->bookStoreService->saveBookStore(\$data, \$request->getAttribute(BookStore::class))
        );
    }
}

BODY;
    }
}
