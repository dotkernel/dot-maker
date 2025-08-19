<?php

declare(strict_types=1);

namespace DotTest\Maker\Type;

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

class HandlerTest extends TestCase
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

    public function testCallToInvokeWhenProjectTypeIsApiWillCreateOnlyGetCollectionHandler(): void
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

    public function testCallToInvokeWhenProjectTypeIsApiWillCreateOnlyGetResourceHandler(): void
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

    public function testCallToInvokeWhenProjectTypeIsApiWillCreateOnlyPostResourceHandler(): void
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

    public function testCallToInvokeWhenProjectTypeIsApiWillCreateOnlyDeleteResourceHandler(): void
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

    public function testCallToInvokeWhenProjectTypeIsApiWillCreateOnlyPatchResourceHandler(): void
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

    public function testCallToInvokeWhenProjectTypeIsApiWillCreateOnlyPutResourceHandler(): void
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

    public function testCallToInvokeWhenProjectTypeIsNotApiWillCreateOnlyListResourceHandler(): void
    {
        $root = vfsStream::setup('root', 0644, [
            'composer.json' => '{
                "autoload": {
                    "psr-4": {
                        "Admin\\\\App\\\\": "src/App/src/"
                    }
                }
            }',
        ]);

        $this->config     = new Config($root->url());
        $this->context    = new Context($root->url());
        $this->fileSystem = (new FileSystem($this->context))->setModuleName($this->moduleName);
        $this->import     = new Import($this->context);
        $this->module     = new Module($this->fileSystem, $this->context, $this->config);

        fwrite($this->inputStream, $this->resourceName . PHP_EOL);
        fwrite($this->inputStream, 'yes' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        rewind($this->inputStream);

        $getListResourcesHandler = $this->fileSystem->getListResourcesHandler($this->resourceName);
        $this->assertFileDoesNotExist($getListResourcesHandler->getPath());
        $this->assertFalse($getListResourcesHandler->exists());

        $getViewResourceHandler = $this->fileSystem->getViewResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($getViewResourceHandler->getPath());
        $this->assertFalse($getViewResourceHandler->exists());

        $getCreateResourceHandler = $this->fileSystem->getCreateResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($getCreateResourceHandler->getPath());
        $this->assertFalse($getCreateResourceHandler->exists());

        $postCreateResourceHandler = $this->fileSystem->postCreateResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($postCreateResourceHandler->getPath());
        $this->assertFalse($postCreateResourceHandler->exists());

        $getDeleteResourceHandler = $this->fileSystem->getDeleteResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($getDeleteResourceHandler->getPath());
        $this->assertFalse($getDeleteResourceHandler->exists());

        $postDeleteResourceHandler = $this->fileSystem->postDeleteResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($postDeleteResourceHandler->getPath());
        $this->assertFalse($postDeleteResourceHandler->exists());

        $getEditResourceHandler = $this->fileSystem->getEditResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($getEditResourceHandler->getPath());
        $this->assertFalse($getEditResourceHandler->exists());

        $postEditResourceHandler = $this->fileSystem->postEditResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($postEditResourceHandler->getPath());
        $this->assertFalse($postEditResourceHandler->exists());

        $handler = new Handler($this->fileSystem, $this->context, $this->config, $this->module);
        $handler();

        $getListResourcesHandler = $this->fileSystem->getListResourcesHandler($this->resourceName);
        $this->assertFileExists($getListResourcesHandler->getPath());
        $this->assertTrue($getListResourcesHandler->exists());
        $this->assertSame($this->dataProviderGetListResourceHandler(), $getListResourcesHandler->read());

        $getViewResourceHandler = $this->fileSystem->getViewResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($getViewResourceHandler->getPath());
        $this->assertFalse($getViewResourceHandler->exists());

        $getCreateResourceHandler = $this->fileSystem->getCreateResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($getCreateResourceHandler->getPath());
        $this->assertFalse($getCreateResourceHandler->exists());

        $postCreateResourceHandler = $this->fileSystem->postCreateResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($postCreateResourceHandler->getPath());
        $this->assertFalse($postCreateResourceHandler->exists());

        $getDeleteResourceHandler = $this->fileSystem->getDeleteResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($getDeleteResourceHandler->getPath());
        $this->assertFalse($getDeleteResourceHandler->exists());

        $postDeleteResourceHandler = $this->fileSystem->postDeleteResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($postDeleteResourceHandler->getPath());
        $this->assertFalse($postDeleteResourceHandler->exists());

        $getEditResourceHandler = $this->fileSystem->getEditResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($getEditResourceHandler->getPath());
        $this->assertFalse($getEditResourceHandler->exists());

        $postEditResourceHandler = $this->fileSystem->postEditResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($postEditResourceHandler->getPath());
        $this->assertFalse($postEditResourceHandler->exists());

        $entity = $this->fileSystem->entity($this->resourceName);

        $templateDir = $this->fileSystem->templateDir($entity->getComponent()->toKebabCase());
        $this->assertDirectoryExists($templateDir->getPath());
        $this->assertTrue($templateDir->exists());

        $templateFile = $this->fileSystem->templateFile($entity->getComponent()->toKebabCase(), 'book-store-list');
        $this->assertFileExists($templateFile->getPath());
        $this->assertTrue($templateFile->exists());

        rewind($this->errorStream);
        $this->assertEmpty(stream_get_contents($this->errorStream));
    }

    public function testCallToInvokeWhenProjectTypeIsNotApiWillCreateOnlyViewResourceHandler(): void
    {
        $root = vfsStream::setup('root', 0644, [
            'composer.json' => '{
                "autoload": {
                    "psr-4": {
                        "Admin\\\\App\\\\": "src/App/src/"
                    }
                }
            }',
        ]);

        $this->config     = new Config($root->url());
        $this->context    = new Context($root->url());
        $this->fileSystem = (new FileSystem($this->context))->setModuleName($this->moduleName);
        $this->import     = new Import($this->context);
        $this->module     = new Module($this->fileSystem, $this->context, $this->config);

        fwrite($this->inputStream, $this->resourceName . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'yes' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        rewind($this->inputStream);

        $getListResourcesHandler = $this->fileSystem->getListResourcesHandler($this->resourceName);
        $this->assertFileDoesNotExist($getListResourcesHandler->getPath());
        $this->assertFalse($getListResourcesHandler->exists());

        $getViewResourceHandler = $this->fileSystem->getViewResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($getViewResourceHandler->getPath());
        $this->assertFalse($getViewResourceHandler->exists());

        $getCreateResourceHandler = $this->fileSystem->getCreateResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($getCreateResourceHandler->getPath());
        $this->assertFalse($getCreateResourceHandler->exists());

        $postCreateResourceHandler = $this->fileSystem->postCreateResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($postCreateResourceHandler->getPath());
        $this->assertFalse($postCreateResourceHandler->exists());

        $getDeleteResourceHandler = $this->fileSystem->getDeleteResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($getDeleteResourceHandler->getPath());
        $this->assertFalse($getDeleteResourceHandler->exists());

        $postDeleteResourceHandler = $this->fileSystem->postDeleteResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($postDeleteResourceHandler->getPath());
        $this->assertFalse($postDeleteResourceHandler->exists());

        $getEditResourceHandler = $this->fileSystem->getEditResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($getEditResourceHandler->getPath());
        $this->assertFalse($getEditResourceHandler->exists());

        $postEditResourceHandler = $this->fileSystem->postEditResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($postEditResourceHandler->getPath());
        $this->assertFalse($postEditResourceHandler->exists());

        $handler = new Handler($this->fileSystem, $this->context, $this->config, $this->module);
        $handler();

        $getListResourcesHandler = $this->fileSystem->getListResourcesHandler($this->resourceName);
        $this->assertFileDoesNotExist($getListResourcesHandler->getPath());
        $this->assertFalse($getListResourcesHandler->exists());

        $getViewResourceHandler = $this->fileSystem->getViewResourceHandler($this->resourceName);
        $this->assertFileExists($getViewResourceHandler->getPath());
        $this->assertTrue($getViewResourceHandler->exists());
        $this->assertSame($this->dataProviderGetViewResourceHandler(), $getViewResourceHandler->read());

        $getCreateResourceHandler = $this->fileSystem->getCreateResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($getCreateResourceHandler->getPath());
        $this->assertFalse($getCreateResourceHandler->exists());

        $postCreateResourceHandler = $this->fileSystem->postCreateResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($postCreateResourceHandler->getPath());
        $this->assertFalse($postCreateResourceHandler->exists());

        $getDeleteResourceHandler = $this->fileSystem->getDeleteResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($getDeleteResourceHandler->getPath());
        $this->assertFalse($getDeleteResourceHandler->exists());

        $postDeleteResourceHandler = $this->fileSystem->postDeleteResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($postDeleteResourceHandler->getPath());
        $this->assertFalse($postDeleteResourceHandler->exists());

        $getEditResourceHandler = $this->fileSystem->getEditResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($getEditResourceHandler->getPath());
        $this->assertFalse($getEditResourceHandler->exists());

        $postEditResourceHandler = $this->fileSystem->postEditResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($postEditResourceHandler->getPath());
        $this->assertFalse($postEditResourceHandler->exists());

        $entity = $this->fileSystem->entity($this->resourceName);

        $templateDir = $this->fileSystem->templateDir($entity->getComponent()->toKebabCase());
        $this->assertDirectoryExists($templateDir->getPath());
        $this->assertTrue($templateDir->exists());

        $templateFile = $this->fileSystem->templateFile($entity->getComponent()->toKebabCase(), 'book-store-view');
        $this->assertFileExists($templateFile->getPath());
        $this->assertTrue($templateFile->exists());

        rewind($this->errorStream);
        $this->assertEmpty(stream_get_contents($this->errorStream));
    }

    public function testCallToInvokeWhenProjectTypeIsNotApiWillCreateOnlyCreateResourceHandler(): void
    {
        $root = vfsStream::setup('root', 0644, [
            'composer.json' => '{
                "autoload": {
                    "psr-4": {
                        "Admin\\\\App\\\\": "src/App/src/"
                    }
                }
            }',
        ]);

        $this->config     = new Config($root->url());
        $this->context    = new Context($root->url());
        $this->fileSystem = (new FileSystem($this->context))->setModuleName($this->moduleName);
        $this->import     = new Import($this->context);
        $this->module     = new Module($this->fileSystem, $this->context, $this->config);

        fwrite($this->inputStream, $this->resourceName . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'yes' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        rewind($this->inputStream);

        $getListResourcesHandler = $this->fileSystem->getListResourcesHandler($this->resourceName);
        $this->assertFileDoesNotExist($getListResourcesHandler->getPath());
        $this->assertFalse($getListResourcesHandler->exists());

        $getViewResourceHandler = $this->fileSystem->getViewResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($getViewResourceHandler->getPath());
        $this->assertFalse($getViewResourceHandler->exists());

        $getCreateResourceHandler = $this->fileSystem->getCreateResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($getCreateResourceHandler->getPath());
        $this->assertFalse($getCreateResourceHandler->exists());

        $postCreateResourceHandler = $this->fileSystem->postCreateResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($postCreateResourceHandler->getPath());
        $this->assertFalse($postCreateResourceHandler->exists());

        $getDeleteResourceHandler = $this->fileSystem->getDeleteResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($getDeleteResourceHandler->getPath());
        $this->assertFalse($getDeleteResourceHandler->exists());

        $postDeleteResourceHandler = $this->fileSystem->postDeleteResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($postDeleteResourceHandler->getPath());
        $this->assertFalse($postDeleteResourceHandler->exists());

        $getEditResourceHandler = $this->fileSystem->getEditResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($getEditResourceHandler->getPath());
        $this->assertFalse($getEditResourceHandler->exists());

        $postEditResourceHandler = $this->fileSystem->postEditResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($postEditResourceHandler->getPath());
        $this->assertFalse($postEditResourceHandler->exists());

        $handler = new Handler($this->fileSystem, $this->context, $this->config, $this->module);
        $handler();

        $getListResourcesHandler = $this->fileSystem->getListResourcesHandler($this->resourceName);
        $this->assertFileDoesNotExist($getListResourcesHandler->getPath());
        $this->assertFalse($getListResourcesHandler->exists());

        $getViewResourceHandler = $this->fileSystem->getViewResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($getViewResourceHandler->getPath());
        $this->assertFalse($getViewResourceHandler->exists());

        $getCreateResourceHandler = $this->fileSystem->getCreateResourceHandler($this->resourceName);
        $this->assertFileExists($getCreateResourceHandler->getPath());
        $this->assertTrue($getCreateResourceHandler->exists());
        $this->assertSame($this->dataProviderGetCreateResourceHandler(), $getCreateResourceHandler->read());

        $postCreateResourceHandler = $this->fileSystem->postCreateResourceHandler($this->resourceName);
        $this->assertFileExists($postCreateResourceHandler->getPath());
        $this->assertTrue($postCreateResourceHandler->exists());
        $this->assertSame($this->dataProviderPostCreateResourceHandler(), $postCreateResourceHandler->read());

        $getDeleteResourceHandler = $this->fileSystem->getDeleteResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($getDeleteResourceHandler->getPath());
        $this->assertFalse($getDeleteResourceHandler->exists());

        $postDeleteResourceHandler = $this->fileSystem->postDeleteResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($postDeleteResourceHandler->getPath());
        $this->assertFalse($postDeleteResourceHandler->exists());

        $getEditResourceHandler = $this->fileSystem->getEditResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($getEditResourceHandler->getPath());
        $this->assertFalse($getEditResourceHandler->exists());

        $postEditResourceHandler = $this->fileSystem->postEditResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($postEditResourceHandler->getPath());
        $this->assertFalse($postEditResourceHandler->exists());

        $entity = $this->fileSystem->entity($this->resourceName);

        $templateDir = $this->fileSystem->templateDir($entity->getComponent()->toKebabCase());
        $this->assertDirectoryExists($templateDir->getPath());
        $this->assertTrue($templateDir->exists());

        $templateFile = $this->fileSystem->templateFile(
            $entity->getComponent()->toKebabCase(),
            'book-store-create-form'
        );
        $this->assertFileExists($templateFile->getPath());
        $this->assertTrue($templateFile->exists());

        rewind($this->errorStream);
        $this->assertEmpty(stream_get_contents($this->errorStream));
    }

    public function testCallToInvokeWhenProjectTypeIsNotApiWillCreateOnlyDeleteResourceHandler(): void
    {
        $root = vfsStream::setup('root', 0644, [
            'composer.json' => '{
                "autoload": {
                    "psr-4": {
                        "Admin\\\\App\\\\": "src/App/src/"
                    }
                }
            }',
        ]);

        $this->config     = new Config($root->url());
        $this->context    = new Context($root->url());
        $this->fileSystem = (new FileSystem($this->context))->setModuleName($this->moduleName);
        $this->import     = new Import($this->context);
        $this->module     = new Module($this->fileSystem, $this->context, $this->config);

        fwrite($this->inputStream, $this->resourceName . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'yes' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        rewind($this->inputStream);

        $getListResourcesHandler = $this->fileSystem->getListResourcesHandler($this->resourceName);
        $this->assertFileDoesNotExist($getListResourcesHandler->getPath());
        $this->assertFalse($getListResourcesHandler->exists());

        $getViewResourceHandler = $this->fileSystem->getViewResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($getViewResourceHandler->getPath());
        $this->assertFalse($getViewResourceHandler->exists());

        $getCreateResourceHandler = $this->fileSystem->getCreateResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($getCreateResourceHandler->getPath());
        $this->assertFalse($getCreateResourceHandler->exists());

        $postCreateResourceHandler = $this->fileSystem->postCreateResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($postCreateResourceHandler->getPath());
        $this->assertFalse($postCreateResourceHandler->exists());

        $getDeleteResourceHandler = $this->fileSystem->getDeleteResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($getDeleteResourceHandler->getPath());
        $this->assertFalse($getDeleteResourceHandler->exists());

        $postDeleteResourceHandler = $this->fileSystem->postDeleteResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($postDeleteResourceHandler->getPath());
        $this->assertFalse($postDeleteResourceHandler->exists());

        $getEditResourceHandler = $this->fileSystem->getEditResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($getEditResourceHandler->getPath());
        $this->assertFalse($getEditResourceHandler->exists());

        $postEditResourceHandler = $this->fileSystem->postEditResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($postEditResourceHandler->getPath());
        $this->assertFalse($postEditResourceHandler->exists());

        $handler = new Handler($this->fileSystem, $this->context, $this->config, $this->module);
        $handler();

        $getListResourcesHandler = $this->fileSystem->getListResourcesHandler($this->resourceName);
        $this->assertFileDoesNotExist($getListResourcesHandler->getPath());
        $this->assertFalse($getListResourcesHandler->exists());

        $getViewResourceHandler = $this->fileSystem->getViewResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($getViewResourceHandler->getPath());
        $this->assertFalse($getViewResourceHandler->exists());

        $getCreateResourceHandler = $this->fileSystem->getCreateResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($getCreateResourceHandler->getPath());
        $this->assertFalse($getCreateResourceHandler->exists());

        $postCreateResourceHandler = $this->fileSystem->postCreateResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($postCreateResourceHandler->getPath());
        $this->assertFalse($postCreateResourceHandler->exists());

        $getDeleteResourceHandler = $this->fileSystem->getDeleteResourceHandler($this->resourceName);
        $this->assertFileExists($getDeleteResourceHandler->getPath());
        $this->assertTrue($getDeleteResourceHandler->exists());
        $this->assertSame($this->dataProviderGetDeleteResourceHandler(), $getDeleteResourceHandler->read());

        $postDeleteResourceHandler = $this->fileSystem->postDeleteResourceHandler($this->resourceName);
        $this->assertFileExists($postDeleteResourceHandler->getPath());
        $this->assertTrue($postDeleteResourceHandler->exists());
        $this->assertSame($this->dataProviderPostDeleteResourceHandler(), $postDeleteResourceHandler->read());

        $getEditResourceHandler = $this->fileSystem->getEditResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($getEditResourceHandler->getPath());
        $this->assertFalse($getEditResourceHandler->exists());

        $postEditResourceHandler = $this->fileSystem->postEditResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($postEditResourceHandler->getPath());
        $this->assertFalse($postEditResourceHandler->exists());

        $entity = $this->fileSystem->entity($this->resourceName);

        $templateDir = $this->fileSystem->templateDir($entity->getComponent()->toKebabCase());
        $this->assertDirectoryExists($templateDir->getPath());
        $this->assertTrue($templateDir->exists());

        $templateFile = $this->fileSystem->templateFile(
            $entity->getComponent()->toKebabCase(),
            'book-store-delete-form'
        );
        $this->assertFileExists($templateFile->getPath());
        $this->assertTrue($templateFile->exists());

        rewind($this->errorStream);
        $this->assertEmpty(stream_get_contents($this->errorStream));
    }

    public function testCallToInvokeWhenProjectTypeIsNotApiWillCreateOnlyEditResourceHandler(): void
    {
        $root = vfsStream::setup('root', 0644, [
            'composer.json' => '{
                "autoload": {
                    "psr-4": {
                        "Admin\\\\App\\\\": "src/App/src/"
                    }
                }
            }',
        ]);

        $this->config     = new Config($root->url());
        $this->context    = new Context($root->url());
        $this->fileSystem = (new FileSystem($this->context))->setModuleName($this->moduleName);
        $this->import     = new Import($this->context);
        $this->module     = new Module($this->fileSystem, $this->context, $this->config);

        fwrite($this->inputStream, $this->resourceName . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'yes' . PHP_EOL);
        rewind($this->inputStream);

        $getListResourcesHandler = $this->fileSystem->getListResourcesHandler($this->resourceName);
        $this->assertFileDoesNotExist($getListResourcesHandler->getPath());
        $this->assertFalse($getListResourcesHandler->exists());

        $getViewResourceHandler = $this->fileSystem->getViewResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($getViewResourceHandler->getPath());
        $this->assertFalse($getViewResourceHandler->exists());

        $getCreateResourceHandler = $this->fileSystem->getCreateResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($getCreateResourceHandler->getPath());
        $this->assertFalse($getCreateResourceHandler->exists());

        $postCreateResourceHandler = $this->fileSystem->postCreateResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($postCreateResourceHandler->getPath());
        $this->assertFalse($postCreateResourceHandler->exists());

        $getDeleteResourceHandler = $this->fileSystem->getDeleteResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($getDeleteResourceHandler->getPath());
        $this->assertFalse($getDeleteResourceHandler->exists());

        $postDeleteResourceHandler = $this->fileSystem->postDeleteResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($postDeleteResourceHandler->getPath());
        $this->assertFalse($postDeleteResourceHandler->exists());

        $getEditResourceHandler = $this->fileSystem->getEditResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($getEditResourceHandler->getPath());
        $this->assertFalse($getEditResourceHandler->exists());

        $postEditResourceHandler = $this->fileSystem->postEditResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($postEditResourceHandler->getPath());
        $this->assertFalse($postEditResourceHandler->exists());

        $handler = new Handler($this->fileSystem, $this->context, $this->config, $this->module);
        $handler();

        $getListResourcesHandler = $this->fileSystem->getListResourcesHandler($this->resourceName);
        $this->assertFileDoesNotExist($getListResourcesHandler->getPath());
        $this->assertFalse($getListResourcesHandler->exists());

        $getViewResourceHandler = $this->fileSystem->getViewResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($getViewResourceHandler->getPath());
        $this->assertFalse($getViewResourceHandler->exists());

        $getCreateResourceHandler = $this->fileSystem->getCreateResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($getCreateResourceHandler->getPath());
        $this->assertFalse($getCreateResourceHandler->exists());

        $postCreateResourceHandler = $this->fileSystem->postCreateResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($postCreateResourceHandler->getPath());
        $this->assertFalse($postCreateResourceHandler->exists());

        $getDeleteResourceHandler = $this->fileSystem->getDeleteResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($getDeleteResourceHandler->getPath());
        $this->assertFalse($getDeleteResourceHandler->exists());

        $postDeleteResourceHandler = $this->fileSystem->postDeleteResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($postDeleteResourceHandler->getPath());
        $this->assertFalse($postDeleteResourceHandler->exists());

        $getEditResourceHandler = $this->fileSystem->getEditResourceHandler($this->resourceName);
        $this->assertFileExists($getEditResourceHandler->getPath());
        $this->assertTrue($getEditResourceHandler->exists());
        $this->assertSame($this->dataProviderGetEditResourceHandler(), $getEditResourceHandler->read());

        $postEditResourceHandler = $this->fileSystem->postEditResourceHandler($this->resourceName);
        $this->assertFileExists($postEditResourceHandler->getPath());
        $this->assertTrue($postEditResourceHandler->exists());
        $this->assertSame($this->dataProviderPostEditResourceHandler(), $postEditResourceHandler->read());

        $entity = $this->fileSystem->entity($this->resourceName);

        $templateDir = $this->fileSystem->templateDir($entity->getComponent()->toKebabCase());
        $this->assertDirectoryExists($templateDir->getPath());
        $this->assertTrue($templateDir->exists());

        $templateFile = $this->fileSystem->templateFile(
            $entity->getComponent()->toKebabCase(),
            'book-store-edit-form'
        );
        $this->assertFileExists($templateFile->getPath());
        $this->assertTrue($templateFile->exists());

        rewind($this->errorStream);
        $this->assertEmpty(stream_get_contents($this->errorStream));
    }

    public function testCallToCreateWhenProjectTypeIsApiWillCreateOnlyGetCollectionHandler(): void
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

    public function testCallToCreateWhenProjectTypeIsApiWillCreateOnlyGetResourceHandler(): void
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

    public function testCallToCreateWhenProjectTypeIsApiWillCreateOnlyPostResourceHandler(): void
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

    public function testCallToCreateWhenProjectTypeIsApiWillCreateOnlyDeleteResourceHandler(): void
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

    public function testCallToCreateWhenProjectTypeIsApiWillCreateOnlyPatchResourceHandler(): void
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

    public function testCallToCreateWhenProjectTypeIsApiWillCreateOnlyPutResourceHandler(): void
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

    public function testCallToCreateWhenProjectTypeIsNotApiWillCreateOnlyListResourceHandler(): void
    {
        $root = vfsStream::setup('root', 0644, [
            'composer.json' => '{
                "autoload": {
                    "psr-4": {
                        "Admin\\\\App\\\\": "src/App/src/"
                    }
                }
            }',
        ]);

        $this->config     = new Config($root->url());
        $this->context    = new Context($root->url());
        $this->fileSystem = (new FileSystem($this->context))->setModuleName($this->moduleName);
        $this->import     = new Import($this->context);
        $this->module     = new Module($this->fileSystem, $this->context, $this->config);

        fwrite($this->inputStream, $this->resourceName . PHP_EOL);
        fwrite($this->inputStream, 'yes' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        rewind($this->inputStream);

        $getListResourcesHandler = $this->fileSystem->getListResourcesHandler($this->resourceName);
        $this->assertFileDoesNotExist($getListResourcesHandler->getPath());
        $this->assertFalse($getListResourcesHandler->exists());

        $getViewResourceHandler = $this->fileSystem->getViewResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($getViewResourceHandler->getPath());
        $this->assertFalse($getViewResourceHandler->exists());

        $getCreateResourceHandler = $this->fileSystem->getCreateResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($getCreateResourceHandler->getPath());
        $this->assertFalse($getCreateResourceHandler->exists());

        $postCreateResourceHandler = $this->fileSystem->postCreateResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($postCreateResourceHandler->getPath());
        $this->assertFalse($postCreateResourceHandler->exists());

        $getDeleteResourceHandler = $this->fileSystem->getDeleteResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($getDeleteResourceHandler->getPath());
        $this->assertFalse($getDeleteResourceHandler->exists());

        $postDeleteResourceHandler = $this->fileSystem->postDeleteResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($postDeleteResourceHandler->getPath());
        $this->assertFalse($postDeleteResourceHandler->exists());

        $getEditResourceHandler = $this->fileSystem->getEditResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($getEditResourceHandler->getPath());
        $this->assertFalse($getEditResourceHandler->exists());

        $postEditResourceHandler = $this->fileSystem->postEditResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($postEditResourceHandler->getPath());
        $this->assertFalse($postEditResourceHandler->exists());

        $handler = new Handler($this->fileSystem, $this->context, $this->config, $this->module);
        $handler->create($this->resourceName);

        $getListResourcesHandler = $this->fileSystem->getListResourcesHandler($this->resourceName);
        $this->assertFileExists($getListResourcesHandler->getPath());
        $this->assertTrue($getListResourcesHandler->exists());
        $this->assertSame($this->dataProviderGetListResourceHandler(), $getListResourcesHandler->read());

        $getViewResourceHandler = $this->fileSystem->getViewResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($getViewResourceHandler->getPath());
        $this->assertFalse($getViewResourceHandler->exists());

        $getCreateResourceHandler = $this->fileSystem->getCreateResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($getCreateResourceHandler->getPath());
        $this->assertFalse($getCreateResourceHandler->exists());

        $postCreateResourceHandler = $this->fileSystem->postCreateResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($postCreateResourceHandler->getPath());
        $this->assertFalse($postCreateResourceHandler->exists());

        $getDeleteResourceHandler = $this->fileSystem->getDeleteResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($getDeleteResourceHandler->getPath());
        $this->assertFalse($getDeleteResourceHandler->exists());

        $postDeleteResourceHandler = $this->fileSystem->postDeleteResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($postDeleteResourceHandler->getPath());
        $this->assertFalse($postDeleteResourceHandler->exists());

        $getEditResourceHandler = $this->fileSystem->getEditResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($getEditResourceHandler->getPath());
        $this->assertFalse($getEditResourceHandler->exists());

        $postEditResourceHandler = $this->fileSystem->postEditResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($postEditResourceHandler->getPath());
        $this->assertFalse($postEditResourceHandler->exists());

        $entity = $this->fileSystem->entity($this->resourceName);

        $templateDir = $this->fileSystem->templateDir($entity->getComponent()->toKebabCase());
        $this->assertDirectoryExists($templateDir->getPath());
        $this->assertTrue($templateDir->exists());

        $templateFile = $this->fileSystem->templateFile($entity->getComponent()->toKebabCase(), 'book-store-list');
        $this->assertFileExists($templateFile->getPath());
        $this->assertTrue($templateFile->exists());

        rewind($this->errorStream);
        $this->assertEmpty(stream_get_contents($this->errorStream));
    }

    public function testCallToCreateWhenProjectTypeIsNotApiWillCreateOnlyViewResourceHandler(): void
    {
        $root = vfsStream::setup('root', 0644, [
            'composer.json' => '{
                "autoload": {
                    "psr-4": {
                        "Admin\\\\App\\\\": "src/App/src/"
                    }
                }
            }',
        ]);

        $this->config     = new Config($root->url());
        $this->context    = new Context($root->url());
        $this->fileSystem = (new FileSystem($this->context))->setModuleName($this->moduleName);
        $this->import     = new Import($this->context);
        $this->module     = new Module($this->fileSystem, $this->context, $this->config);

        fwrite($this->inputStream, $this->resourceName . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'yes' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        rewind($this->inputStream);

        $getListResourcesHandler = $this->fileSystem->getListResourcesHandler($this->resourceName);
        $this->assertFileDoesNotExist($getListResourcesHandler->getPath());
        $this->assertFalse($getListResourcesHandler->exists());

        $getViewResourceHandler = $this->fileSystem->getViewResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($getViewResourceHandler->getPath());
        $this->assertFalse($getViewResourceHandler->exists());

        $getCreateResourceHandler = $this->fileSystem->getCreateResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($getCreateResourceHandler->getPath());
        $this->assertFalse($getCreateResourceHandler->exists());

        $postCreateResourceHandler = $this->fileSystem->postCreateResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($postCreateResourceHandler->getPath());
        $this->assertFalse($postCreateResourceHandler->exists());

        $getDeleteResourceHandler = $this->fileSystem->getDeleteResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($getDeleteResourceHandler->getPath());
        $this->assertFalse($getDeleteResourceHandler->exists());

        $postDeleteResourceHandler = $this->fileSystem->postDeleteResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($postDeleteResourceHandler->getPath());
        $this->assertFalse($postDeleteResourceHandler->exists());

        $getEditResourceHandler = $this->fileSystem->getEditResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($getEditResourceHandler->getPath());
        $this->assertFalse($getEditResourceHandler->exists());

        $postEditResourceHandler = $this->fileSystem->postEditResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($postEditResourceHandler->getPath());
        $this->assertFalse($postEditResourceHandler->exists());

        $handler = new Handler($this->fileSystem, $this->context, $this->config, $this->module);
        $handler->create($this->resourceName);

        $getListResourcesHandler = $this->fileSystem->getListResourcesHandler($this->resourceName);
        $this->assertFileDoesNotExist($getListResourcesHandler->getPath());
        $this->assertFalse($getListResourcesHandler->exists());

        $getViewResourceHandler = $this->fileSystem->getViewResourceHandler($this->resourceName);
        $this->assertFileExists($getViewResourceHandler->getPath());
        $this->assertTrue($getViewResourceHandler->exists());
        $this->assertSame($this->dataProviderGetViewResourceHandler(), $getViewResourceHandler->read());

        $getCreateResourceHandler = $this->fileSystem->getCreateResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($getCreateResourceHandler->getPath());
        $this->assertFalse($getCreateResourceHandler->exists());

        $postCreateResourceHandler = $this->fileSystem->postCreateResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($postCreateResourceHandler->getPath());
        $this->assertFalse($postCreateResourceHandler->exists());

        $getDeleteResourceHandler = $this->fileSystem->getDeleteResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($getDeleteResourceHandler->getPath());
        $this->assertFalse($getDeleteResourceHandler->exists());

        $postDeleteResourceHandler = $this->fileSystem->postDeleteResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($postDeleteResourceHandler->getPath());
        $this->assertFalse($postDeleteResourceHandler->exists());

        $getEditResourceHandler = $this->fileSystem->getEditResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($getEditResourceHandler->getPath());
        $this->assertFalse($getEditResourceHandler->exists());

        $postEditResourceHandler = $this->fileSystem->postEditResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($postEditResourceHandler->getPath());
        $this->assertFalse($postEditResourceHandler->exists());

        $entity = $this->fileSystem->entity($this->resourceName);

        $templateDir = $this->fileSystem->templateDir($entity->getComponent()->toKebabCase());
        $this->assertDirectoryExists($templateDir->getPath());
        $this->assertTrue($templateDir->exists());

        $templateFile = $this->fileSystem->templateFile($entity->getComponent()->toKebabCase(), 'book-store-view');
        $this->assertFileExists($templateFile->getPath());
        $this->assertTrue($templateFile->exists());

        rewind($this->errorStream);
        $this->assertEmpty(stream_get_contents($this->errorStream));
    }

    public function testCallToCreateWhenProjectTypeIsNotApiWillCreateOnlyCreateResourceHandler(): void
    {
        $root = vfsStream::setup('root', 0644, [
            'composer.json' => '{
                "autoload": {
                    "psr-4": {
                        "Admin\\\\App\\\\": "src/App/src/"
                    }
                }
            }',
        ]);

        $this->config     = new Config($root->url());
        $this->context    = new Context($root->url());
        $this->fileSystem = (new FileSystem($this->context))->setModuleName($this->moduleName);
        $this->import     = new Import($this->context);
        $this->module     = new Module($this->fileSystem, $this->context, $this->config);

        fwrite($this->inputStream, $this->resourceName . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'yes' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        rewind($this->inputStream);

        $getListResourcesHandler = $this->fileSystem->getListResourcesHandler($this->resourceName);
        $this->assertFileDoesNotExist($getListResourcesHandler->getPath());
        $this->assertFalse($getListResourcesHandler->exists());

        $getViewResourceHandler = $this->fileSystem->getViewResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($getViewResourceHandler->getPath());
        $this->assertFalse($getViewResourceHandler->exists());

        $getCreateResourceHandler = $this->fileSystem->getCreateResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($getCreateResourceHandler->getPath());
        $this->assertFalse($getCreateResourceHandler->exists());

        $postCreateResourceHandler = $this->fileSystem->postCreateResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($postCreateResourceHandler->getPath());
        $this->assertFalse($postCreateResourceHandler->exists());

        $getDeleteResourceHandler = $this->fileSystem->getDeleteResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($getDeleteResourceHandler->getPath());
        $this->assertFalse($getDeleteResourceHandler->exists());

        $postDeleteResourceHandler = $this->fileSystem->postDeleteResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($postDeleteResourceHandler->getPath());
        $this->assertFalse($postDeleteResourceHandler->exists());

        $getEditResourceHandler = $this->fileSystem->getEditResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($getEditResourceHandler->getPath());
        $this->assertFalse($getEditResourceHandler->exists());

        $postEditResourceHandler = $this->fileSystem->postEditResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($postEditResourceHandler->getPath());
        $this->assertFalse($postEditResourceHandler->exists());

        $handler = new Handler($this->fileSystem, $this->context, $this->config, $this->module);
        $handler->create($this->resourceName);

        $getListResourcesHandler = $this->fileSystem->getListResourcesHandler($this->resourceName);
        $this->assertFileDoesNotExist($getListResourcesHandler->getPath());
        $this->assertFalse($getListResourcesHandler->exists());

        $getViewResourceHandler = $this->fileSystem->getViewResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($getViewResourceHandler->getPath());
        $this->assertFalse($getViewResourceHandler->exists());

        $getCreateResourceHandler = $this->fileSystem->getCreateResourceHandler($this->resourceName);
        $this->assertFileExists($getCreateResourceHandler->getPath());
        $this->assertTrue($getCreateResourceHandler->exists());
        $this->assertSame($this->dataProviderGetCreateResourceHandler(), $getCreateResourceHandler->read());

        $postCreateResourceHandler = $this->fileSystem->postCreateResourceHandler($this->resourceName);
        $this->assertFileExists($postCreateResourceHandler->getPath());
        $this->assertTrue($postCreateResourceHandler->exists());
        $this->assertSame($this->dataProviderPostCreateResourceHandler(), $postCreateResourceHandler->read());

        $getDeleteResourceHandler = $this->fileSystem->getDeleteResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($getDeleteResourceHandler->getPath());
        $this->assertFalse($getDeleteResourceHandler->exists());

        $postDeleteResourceHandler = $this->fileSystem->postDeleteResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($postDeleteResourceHandler->getPath());
        $this->assertFalse($postDeleteResourceHandler->exists());

        $getEditResourceHandler = $this->fileSystem->getEditResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($getEditResourceHandler->getPath());
        $this->assertFalse($getEditResourceHandler->exists());

        $postEditResourceHandler = $this->fileSystem->postEditResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($postEditResourceHandler->getPath());
        $this->assertFalse($postEditResourceHandler->exists());

        $entity = $this->fileSystem->entity($this->resourceName);

        $templateDir = $this->fileSystem->templateDir($entity->getComponent()->toKebabCase());
        $this->assertDirectoryExists($templateDir->getPath());
        $this->assertTrue($templateDir->exists());

        $templateFile = $this->fileSystem->templateFile(
            $entity->getComponent()->toKebabCase(),
            'book-store-create-form'
        );
        $this->assertFileExists($templateFile->getPath());
        $this->assertTrue($templateFile->exists());

        rewind($this->errorStream);
        $this->assertEmpty(stream_get_contents($this->errorStream));
    }

    public function testCallToCreateWhenProjectTypeIsNotApiWillCreateOnlyDeleteResourceHandler(): void
    {
        $root = vfsStream::setup('root', 0644, [
            'composer.json' => '{
                "autoload": {
                    "psr-4": {
                        "Admin\\\\App\\\\": "src/App/src/"
                    }
                }
            }',
        ]);

        $this->config     = new Config($root->url());
        $this->context    = new Context($root->url());
        $this->fileSystem = (new FileSystem($this->context))->setModuleName($this->moduleName);
        $this->import     = new Import($this->context);
        $this->module     = new Module($this->fileSystem, $this->context, $this->config);

        fwrite($this->inputStream, $this->resourceName . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'yes' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        rewind($this->inputStream);

        $getListResourcesHandler = $this->fileSystem->getListResourcesHandler($this->resourceName);
        $this->assertFileDoesNotExist($getListResourcesHandler->getPath());
        $this->assertFalse($getListResourcesHandler->exists());

        $getViewResourceHandler = $this->fileSystem->getViewResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($getViewResourceHandler->getPath());
        $this->assertFalse($getViewResourceHandler->exists());

        $getCreateResourceHandler = $this->fileSystem->getCreateResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($getCreateResourceHandler->getPath());
        $this->assertFalse($getCreateResourceHandler->exists());

        $postCreateResourceHandler = $this->fileSystem->postCreateResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($postCreateResourceHandler->getPath());
        $this->assertFalse($postCreateResourceHandler->exists());

        $getDeleteResourceHandler = $this->fileSystem->getDeleteResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($getDeleteResourceHandler->getPath());
        $this->assertFalse($getDeleteResourceHandler->exists());

        $postDeleteResourceHandler = $this->fileSystem->postDeleteResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($postDeleteResourceHandler->getPath());
        $this->assertFalse($postDeleteResourceHandler->exists());

        $getEditResourceHandler = $this->fileSystem->getEditResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($getEditResourceHandler->getPath());
        $this->assertFalse($getEditResourceHandler->exists());

        $postEditResourceHandler = $this->fileSystem->postEditResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($postEditResourceHandler->getPath());
        $this->assertFalse($postEditResourceHandler->exists());

        $handler = new Handler($this->fileSystem, $this->context, $this->config, $this->module);
        $handler->create($this->resourceName);

        $getListResourcesHandler = $this->fileSystem->getListResourcesHandler($this->resourceName);
        $this->assertFileDoesNotExist($getListResourcesHandler->getPath());
        $this->assertFalse($getListResourcesHandler->exists());

        $getViewResourceHandler = $this->fileSystem->getViewResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($getViewResourceHandler->getPath());
        $this->assertFalse($getViewResourceHandler->exists());

        $getCreateResourceHandler = $this->fileSystem->getCreateResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($getCreateResourceHandler->getPath());
        $this->assertFalse($getCreateResourceHandler->exists());

        $postCreateResourceHandler = $this->fileSystem->postCreateResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($postCreateResourceHandler->getPath());
        $this->assertFalse($postCreateResourceHandler->exists());

        $getDeleteResourceHandler = $this->fileSystem->getDeleteResourceHandler($this->resourceName);
        $this->assertFileExists($getDeleteResourceHandler->getPath());
        $this->assertTrue($getDeleteResourceHandler->exists());
        $this->assertSame($this->dataProviderGetDeleteResourceHandler(), $getDeleteResourceHandler->read());

        $postDeleteResourceHandler = $this->fileSystem->postDeleteResourceHandler($this->resourceName);
        $this->assertFileExists($postDeleteResourceHandler->getPath());
        $this->assertTrue($postDeleteResourceHandler->exists());
        $this->assertSame($this->dataProviderPostDeleteResourceHandler(), $postDeleteResourceHandler->read());

        $getEditResourceHandler = $this->fileSystem->getEditResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($getEditResourceHandler->getPath());
        $this->assertFalse($getEditResourceHandler->exists());

        $postEditResourceHandler = $this->fileSystem->postEditResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($postEditResourceHandler->getPath());
        $this->assertFalse($postEditResourceHandler->exists());

        $entity = $this->fileSystem->entity($this->resourceName);

        $templateDir = $this->fileSystem->templateDir($entity->getComponent()->toKebabCase());
        $this->assertDirectoryExists($templateDir->getPath());
        $this->assertTrue($templateDir->exists());

        $templateFile = $this->fileSystem->templateFile(
            $entity->getComponent()->toKebabCase(),
            'book-store-delete-form'
        );
        $this->assertFileExists($templateFile->getPath());
        $this->assertTrue($templateFile->exists());

        rewind($this->errorStream);
        $this->assertEmpty(stream_get_contents($this->errorStream));
    }

    public function testCallToCreateWhenProjectTypeIsNotApiWillCreateOnlyEditResourceHandler(): void
    {
        $root = vfsStream::setup('root', 0644, [
            'composer.json' => '{
                "autoload": {
                    "psr-4": {
                        "Admin\\\\App\\\\": "src/App/src/"
                    }
                }
            }',
        ]);

        $this->config     = new Config($root->url());
        $this->context    = new Context($root->url());
        $this->fileSystem = (new FileSystem($this->context))->setModuleName($this->moduleName);
        $this->import     = new Import($this->context);
        $this->module     = new Module($this->fileSystem, $this->context, $this->config);

        fwrite($this->inputStream, $this->resourceName . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'yes' . PHP_EOL);
        rewind($this->inputStream);

        $getListResourcesHandler = $this->fileSystem->getListResourcesHandler($this->resourceName);
        $this->assertFileDoesNotExist($getListResourcesHandler->getPath());
        $this->assertFalse($getListResourcesHandler->exists());

        $getViewResourceHandler = $this->fileSystem->getViewResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($getViewResourceHandler->getPath());
        $this->assertFalse($getViewResourceHandler->exists());

        $getCreateResourceHandler = $this->fileSystem->getCreateResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($getCreateResourceHandler->getPath());
        $this->assertFalse($getCreateResourceHandler->exists());

        $postCreateResourceHandler = $this->fileSystem->postCreateResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($postCreateResourceHandler->getPath());
        $this->assertFalse($postCreateResourceHandler->exists());

        $getDeleteResourceHandler = $this->fileSystem->getDeleteResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($getDeleteResourceHandler->getPath());
        $this->assertFalse($getDeleteResourceHandler->exists());

        $postDeleteResourceHandler = $this->fileSystem->postDeleteResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($postDeleteResourceHandler->getPath());
        $this->assertFalse($postDeleteResourceHandler->exists());

        $getEditResourceHandler = $this->fileSystem->getEditResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($getEditResourceHandler->getPath());
        $this->assertFalse($getEditResourceHandler->exists());

        $postEditResourceHandler = $this->fileSystem->postEditResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($postEditResourceHandler->getPath());
        $this->assertFalse($postEditResourceHandler->exists());

        $handler = new Handler($this->fileSystem, $this->context, $this->config, $this->module);
        $handler->create($this->resourceName);

        $getListResourcesHandler = $this->fileSystem->getListResourcesHandler($this->resourceName);
        $this->assertFileDoesNotExist($getListResourcesHandler->getPath());
        $this->assertFalse($getListResourcesHandler->exists());

        $getViewResourceHandler = $this->fileSystem->getViewResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($getViewResourceHandler->getPath());
        $this->assertFalse($getViewResourceHandler->exists());

        $getCreateResourceHandler = $this->fileSystem->getCreateResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($getCreateResourceHandler->getPath());
        $this->assertFalse($getCreateResourceHandler->exists());

        $postCreateResourceHandler = $this->fileSystem->postCreateResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($postCreateResourceHandler->getPath());
        $this->assertFalse($postCreateResourceHandler->exists());

        $getDeleteResourceHandler = $this->fileSystem->getDeleteResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($getDeleteResourceHandler->getPath());
        $this->assertFalse($getDeleteResourceHandler->exists());

        $postDeleteResourceHandler = $this->fileSystem->postDeleteResourceHandler($this->resourceName);
        $this->assertFileDoesNotExist($postDeleteResourceHandler->getPath());
        $this->assertFalse($postDeleteResourceHandler->exists());

        $getEditResourceHandler = $this->fileSystem->getEditResourceHandler($this->resourceName);
        $this->assertFileExists($getEditResourceHandler->getPath());
        $this->assertTrue($getEditResourceHandler->exists());
        $this->assertSame($this->dataProviderGetEditResourceHandler(), $getEditResourceHandler->read());

        $postEditResourceHandler = $this->fileSystem->postEditResourceHandler($this->resourceName);
        $this->assertFileExists($postEditResourceHandler->getPath());
        $this->assertTrue($postEditResourceHandler->exists());
        $this->assertSame($this->dataProviderPostEditResourceHandler(), $postEditResourceHandler->read());

        $entity = $this->fileSystem->entity($this->resourceName);

        $templateDir = $this->fileSystem->templateDir($entity->getComponent()->toKebabCase());
        $this->assertDirectoryExists($templateDir->getPath());
        $this->assertTrue($templateDir->exists());

        $templateFile = $this->fileSystem->templateFile(
            $entity->getComponent()->toKebabCase(),
            'book-store-edit-form'
        );
        $this->assertFileExists($templateFile->getPath());
        $this->assertTrue($templateFile->exists());

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

    private function dataProviderGetListResourceHandler(): string
    {
        $serviceInterface = $this->fileSystem->serviceInterface($this->resourceName);

        $uses = [
            sprintf('use %s;', $serviceInterface->getComponent()->getFqcn()),
            sprintf('use %s;', Import::DOT_DEPENDENCYINJECTION_ATTRIBUTE_INJECT),
            sprintf('use %s;', Import::LAMINAS_DIACTOROS_RESPONSE_HTMLRESPONSE),
            sprintf('use %s;', Import::MEZZIO_TEMPLATE_TEMPLATERENDERERINTERFACE),
            sprintf('use %s;', Import::PSR_HTTP_MESSAGE_RESPONSEINTERFACE),
            sprintf('use %s;', Import::PSR_HTTP_MESSAGE_SERVERREQUESTINTERFACE),
            sprintf('use %s;', Import::PSR_HTTP_SERVER_REQUESTHANDLERINTERFACE),
        ];
        $uses = implode(PHP_EOL, $uses);

        return <<<BODY
<?php

declare(strict_types=1);

namespace Admin\ModuleName\Handler\BookStore;

{$uses}

class GetBookStoreListHandler implements RequestHandlerInterface
{
    #[Inject(
        BookStoreServiceInterface::class,
        TemplateRendererInterface::class,
    )]
    public function __construct(
        protected BookStoreServiceInterface \$bookStoreService,
        protected TemplateRendererInterface \$template,
    ) {
    }

    public function handle(
        ServerRequestInterface \$request,
    ): ResponseInterface {
        return new HtmlResponse(
            \$this->template->render('book-store::book-store-list', [
                'pagination' => \$this->bookStoreService->getBookStores(\$request->getQueryParams()),
            ])
        );
    }
}

BODY;
    }

    private function dataProviderGetViewResourceHandler(): string
    {
        $serviceInterface = $this->fileSystem->serviceInterface($this->resourceName);

        $uses = [
            sprintf('use %s;', $this->import->getNotFoundExceptionFqcn()),
            sprintf('use %s;', $serviceInterface->getComponent()->getFqcn()),
            sprintf('use %s;', Import::DOT_DEPENDENCYINJECTION_ATTRIBUTE_INJECT),
            sprintf('use %s;', Import::DOT_FLASHMESSENGER_FLASHMESSENGERINTERFACE),
            sprintf('use %s;', Import::FIG_HTTP_MESSAGE_STATUSCODEINTERFACE),
            sprintf('use %s;', Import::LAMINAS_DIACTOROS_RESPONSE_EMPTYRESPONSE),
            sprintf('use %s;', Import::LAMINAS_DIACTOROS_RESPONSE_HTMLRESPONSE),
            sprintf('use %s;', Import::MEZZIO_TEMPLATE_TEMPLATERENDERERINTERFACE),
            sprintf('use %s;', Import::PSR_HTTP_MESSAGE_RESPONSEINTERFACE),
            sprintf('use %s;', Import::PSR_HTTP_MESSAGE_SERVERREQUESTINTERFACE),
            sprintf('use %s;', Import::PSR_HTTP_SERVER_REQUESTHANDLERINTERFACE),
        ];
        $uses = implode(PHP_EOL, $uses);

        return <<<BODY
<?php

declare(strict_types=1);

namespace Admin\ModuleName\Handler\BookStore;

{$uses}

class GetBookStoreViewHandler implements RequestHandlerInterface
{
    #[Inject(
        BookStoreServiceInterface::class,
        TemplateRendererInterface::class,
        FlashMessengerInterface::class,
    )]
    public function __construct(
        protected BookStoreServiceInterface \$bookStoreService,
        protected TemplateRendererInterface \$template,
        protected FlashMessengerInterface \$messenger,
    ) {
    }

    public function handle(
        ServerRequestInterface \$request,
    ): ResponseInterface {
        try {
            \$bookStore = \$this->bookStoreService->findBookStore(\$request->getAttribute('uuid'));
        } catch (NotFoundException \$exception) {
            \$this->messenger->addError(\$exception->getMessage());

            return new EmptyResponse(StatusCodeInterface::STATUS_NOT_FOUND);
        }

        return new HtmlResponse(
            \$this->template->render('book-store::book-store-view', [
                'bookStore' => \$bookStore,
            ])
        );
    }
}

BODY;
    }

    private function dataProviderGetCreateResourceHandler(): string
    {
        $createResourceForm = $this->fileSystem->createResourceForm($this->resourceName);

        $uses = [
            sprintf('use %s;', $createResourceForm->getComponent()->getFqcn()),
            sprintf('use %s;', Import::DOT_DEPENDENCYINJECTION_ATTRIBUTE_INJECT),
            sprintf('use %s;', Import::LAMINAS_DIACTOROS_RESPONSE_HTMLRESPONSE),
            sprintf('use %s;', Import::MEZZIO_ROUTER_ROUTERINTERFACE),
            sprintf('use %s;', Import::MEZZIO_TEMPLATE_TEMPLATERENDERERINTERFACE),
            sprintf('use %s;', Import::PSR_HTTP_MESSAGE_RESPONSEINTERFACE),
            sprintf('use %s;', Import::PSR_HTTP_MESSAGE_SERVERREQUESTINTERFACE),
            sprintf('use %s;', Import::PSR_HTTP_SERVER_REQUESTHANDLERINTERFACE),
        ];
        $uses = implode(PHP_EOL, $uses);

        return <<<BODY
<?php

declare(strict_types=1);

namespace Admin\ModuleName\Handler\BookStore;

{$uses}

class GetBookStoreCreateFormHandler implements RequestHandlerInterface
{
    #[Inject(
        RouterInterface::class,
        TemplateRendererInterface::class,
        CreateBookStoreForm::class,
    )]
    public function __construct(
        protected RouterInterface \$router,
        protected TemplateRendererInterface \$template,
        protected CreateBookStoreForm \$createBookStoreForm,
    ) {
    }

    public function handle(
        ServerRequestInterface \$request,
    ): ResponseInterface {
        \$this->createBookStoreForm
            ->setAttribute('action', \$this->router->generateUri('book-store::book-store-create'));

        return new HtmlResponse(
            \$this->template->render('book-store::book-store-create-form', [
                'form' => \$this->createBookStoreForm->prepare(),
            ])
        );
    }
}

BODY;
    }

    private function dataProviderPostCreateResourceHandler(): string
    {
        $createResourceForm = $this->fileSystem->createResourceForm($this->resourceName);
        $serviceInterface   = $this->fileSystem->serviceInterface($this->resourceName);

        $uses = [
            sprintf('use %s;', $this->import->getConflictExceptionFqcn()),
            sprintf('use %s;', $this->import->getAppMessageFqcn()),
            sprintf('use %s;', $createResourceForm->getComponent()->getFqcn()),
            sprintf('use %s;', $serviceInterface->getComponent()->getFqcn()),
            sprintf('use %s;', Import::DOT_DEPENDENCYINJECTION_ATTRIBUTE_INJECT),
            sprintf('use %s;', Import::DOT_FLASHMESSENGER_FLASHMESSENGERINTERFACE),
            sprintf('use %s;', Import::DOT_LOG_LOGGER),
            sprintf('use %s;', Import::FIG_HTTP_MESSAGE_STATUSCODEINTERFACE),
            sprintf('use %s;', Import::LAMINAS_DIACTOROS_RESPONSE_EMPTYRESPONSE),
            sprintf('use %s;', Import::LAMINAS_DIACTOROS_RESPONSE_HTMLRESPONSE),
            sprintf('use %s;', Import::MEZZIO_ROUTER_ROUTERINTERFACE),
            sprintf('use %s;', Import::MEZZIO_TEMPLATE_TEMPLATERENDERERINTERFACE),
            sprintf('use %s;', Import::PSR_HTTP_MESSAGE_RESPONSEINTERFACE),
            sprintf('use %s;', Import::PSR_HTTP_MESSAGE_SERVERREQUESTINTERFACE),
            sprintf('use %s;', Import::PSR_HTTP_SERVER_REQUESTHANDLERINTERFACE),
            sprintf('use %s;', Import::THROWABLE),
        ];
        $uses = implode(PHP_EOL, $uses);

        return <<<BODY
<?php

declare(strict_types=1);

namespace Admin\ModuleName\Handler\BookStore;

{$uses}

class PostBookStoreCreateHandler implements RequestHandlerInterface
{
    #[Inject(
        BookStoreServiceInterface::class,
        RouterInterface::class,
        TemplateRendererInterface::class,
        FlashMessengerInterface::class,
        CreateBookStoreForm::class,
        'dot-log.default_logger',
    )]
    public function __construct(
        protected BookStoreServiceInterface \$bookStoreService,
        protected RouterInterface \$router,
        protected TemplateRendererInterface \$template,
        protected FlashMessengerInterface \$messenger,
        protected CreateBookStoreForm \$createBookStoreForm,
        protected Logger \$logger,
    ) {
    }

    public function handle(
        ServerRequestInterface \$request,
    ): ResponseInterface {
        \$this->createBookStoreForm
            ->setAttribute('action', \$this->router->generateUri('book-store::book-store-create'));

        try {
            \$data = (array) \$request->getParsedBody();
            \$this->createBookStoreForm->setData(\$data);
            if (\$this->createBookStoreForm->isValid()) {
                \$data = \$this->createBookStoreForm->getData();
                \$this->bookStoreService->saveBookStore(\$data);
                \$this->messenger->addSuccess(Message::BOOK_STORE_CREATED);

                return new EmptyResponse(StatusCodeInterface::STATUS_CREATED);
            }

            return new HtmlResponse(
                \$this->template->render('book-store::book-store-create-form', [
                    'form' => \$this->createBookStoreForm->prepare(),
                ]),
                StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY
            );
        } catch (ConflictException \$exception) {
            return new HtmlResponse(
                \$this->template->render('book-store::book-store-create-form', [
                    'form'     => \$this->createBookStoreForm->prepare(),
                    'messages' => [
                        'error' => \$exception->getMessage(),
                    ],
                ]),
                StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY
            );
        } catch (Throwable \$exception) {
            \$this->logger->err('Create BookStore', [
                'error' => \$exception->getMessage(),
                'file'  => \$exception->getFile(),
                'line'  => \$exception->getLine(),
                'trace' => \$exception->getTraceAsString(),
            ]);

            return new HtmlResponse(
                \$this->template->render('book-store::book-store-create-form', [
                    'form'     => \$this->createBookStoreForm->prepare(),
                    'messages' => [
                        'error' => Message::AN_ERROR_OCCURRED,
                    ],
                ]),
                StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR
            );
        }
    }
}

BODY;
    }

    private function dataProviderGetDeleteResourceHandler(): string
    {
        $deleteResourceForm = $this->fileSystem->deleteResourceForm($this->resourceName);
        $serviceInterface   = $this->fileSystem->serviceInterface($this->resourceName);

        $uses = [
            sprintf('use %s;', $this->import->getNotFoundExceptionFqcn()),
            sprintf('use %s;', $deleteResourceForm->getComponent()->getFqcn()),
            sprintf('use %s;', $serviceInterface->getComponent()->getFqcn()),
            sprintf('use %s;', Import::DOT_DEPENDENCYINJECTION_ATTRIBUTE_INJECT),
            sprintf('use %s;', Import::DOT_FLASHMESSENGER_FLASHMESSENGERINTERFACE),
            sprintf('use %s;', Import::FIG_HTTP_MESSAGE_STATUSCODEINTERFACE),
            sprintf('use %s;', Import::LAMINAS_DIACTOROS_RESPONSE_EMPTYRESPONSE),
            sprintf('use %s;', Import::LAMINAS_DIACTOROS_RESPONSE_HTMLRESPONSE),
            sprintf('use %s;', Import::MEZZIO_ROUTER_ROUTERINTERFACE),
            sprintf('use %s;', Import::MEZZIO_TEMPLATE_TEMPLATERENDERERINTERFACE),
            sprintf('use %s;', Import::PSR_HTTP_MESSAGE_RESPONSEINTERFACE),
            sprintf('use %s;', Import::PSR_HTTP_MESSAGE_SERVERREQUESTINTERFACE),
            sprintf('use %s;', Import::PSR_HTTP_SERVER_REQUESTHANDLERINTERFACE),
        ];
        $uses = implode(PHP_EOL, $uses);

        return <<<BODY
<?php

declare(strict_types=1);

namespace Admin\ModuleName\Handler\BookStore;

{$uses}

class GetBookStoreDeleteFormHandler implements RequestHandlerInterface
{
    #[Inject(
        BookStoreServiceInterface::class,
        RouterInterface::class,
        TemplateRendererInterface::class,
        FlashMessengerInterface::class,
        DeleteBookStoreForm::class,
    )]
    public function __construct(
        protected BookStoreServiceInterface \$bookStoreService,
        protected RouterInterface \$router,
        protected TemplateRendererInterface \$template,
        protected FlashMessengerInterface \$messenger,
        protected DeleteBookStoreForm \$deleteBookStoreForm,
    ) {
    }

    public function handle(
        ServerRequestInterface \$request,
    ): ResponseInterface {
        try {
            \$bookStore = \$this->bookStoreService->findBookStore(\$request->getAttribute('uuid'));
        } catch (NotFoundException \$exception) {
            \$this->messenger->addError(\$exception->getMessage());

            return new EmptyResponse(StatusCodeInterface::STATUS_NOT_FOUND);
        }

        \$this->deleteBookStoreForm->setAttribute(
            'action',
            \$this->router->generateUri('book-store::book-store-delete', ['uuid' => \$bookStore->getUuid()->toString()])
        );

        return new HtmlResponse(
            \$this->template->render('book-store::book-store-delete-form', [
                'form' => \$this->deleteBookStoreForm->prepare(),
                'book-store' => \$bookStore,
            ])
        );
    }
}

BODY;
    }

    private function dataProviderPostDeleteResourceHandler(): string
    {
        $deleteResourceForm = $this->fileSystem->deleteResourceForm($this->resourceName);
        $serviceInterface   = $this->fileSystem->serviceInterface($this->resourceName);

        $uses = [
            sprintf('use %s;', $this->import->getNotFoundExceptionFqcn()),
            sprintf('use %s;', $this->import->getAppMessageFqcn()),
            sprintf('use %s;', $deleteResourceForm->getComponent()->getFqcn()),
            sprintf('use %s;', $serviceInterface->getComponent()->getFqcn()),
            sprintf('use %s;', Import::DOT_DEPENDENCYINJECTION_ATTRIBUTE_INJECT),
            sprintf('use %s;', Import::DOT_FLASHMESSENGER_FLASHMESSENGERINTERFACE),
            sprintf('use %s;', Import::DOT_LOG_LOGGER),
            sprintf('use %s;', Import::FIG_HTTP_MESSAGE_STATUSCODEINTERFACE),
            sprintf('use %s;', Import::LAMINAS_DIACTOROS_RESPONSE_EMPTYRESPONSE),
            sprintf('use %s;', Import::LAMINAS_DIACTOROS_RESPONSE_HTMLRESPONSE),
            sprintf('use %s;', Import::MEZZIO_ROUTER_ROUTERINTERFACE),
            sprintf('use %s;', Import::MEZZIO_TEMPLATE_TEMPLATERENDERERINTERFACE),
            sprintf('use %s;', Import::PSR_HTTP_MESSAGE_RESPONSEINTERFACE),
            sprintf('use %s;', Import::PSR_HTTP_MESSAGE_SERVERREQUESTINTERFACE),
            sprintf('use %s;', Import::PSR_HTTP_SERVER_REQUESTHANDLERINTERFACE),
            sprintf('use %s;', Import::THROWABLE),
        ];
        $uses = implode(PHP_EOL, $uses);

        return <<<BODY
<?php

declare(strict_types=1);

namespace Admin\ModuleName\Handler\BookStore;

{$uses}

class PostBookStoreDeleteHandler implements RequestHandlerInterface
{
    #[Inject(
        BookStoreServiceInterface::class,
        RouterInterface::class,
        TemplateRendererInterface::class,
        FlashMessengerInterface::class,
        DeleteBookStoreForm::class,
        'dot-log.default_logger',
    )]
    public function __construct(
        protected BookStoreServiceInterface \$bookStoreService,
        protected RouterInterface \$router,
        protected TemplateRendererInterface \$template,
        protected FlashMessengerInterface \$messenger,
        protected DeleteBookStoreForm \$deleteBookStoreForm,
        protected Logger \$logger,
    ) {
    }

    public function handle(
        ServerRequestInterface \$request,
    ): ResponseInterface {
        try {
            \$bookStore = \$this->bookStoreService->findBookStore(\$request->getAttribute('uuid'));
        } catch (NotFoundException \$exception) {
            \$this->messenger->addError(\$exception->getMessage());

            return new EmptyResponse(StatusCodeInterface::STATUS_NOT_FOUND);
        }

        \$this->deleteBookStoreForm->setAttribute(
            'action',
            \$this->router->generateUri('book-store::book-store-delete', ['uuid' => \$bookStore->getUuid()->toString()])
        );

        try {
            \$data = (array) \$request->getParsedBody();
            \$this->deleteBookStoreForm->setData(\$data);
            if (\$this->deleteBookStoreForm->isValid()) {
                \$this->bookStoreService->deleteBookStore(\$bookStore);
                \$this->messenger->addSuccess(Message::BOOK_STORE_DELETED);

                return new EmptyResponse(StatusCodeInterface::STATUS_CREATED);
            }

            return new HtmlResponse(
                \$this->template->render('book-store::book-store-delete-form', [
                    'form' => \$this->deleteBookStoreForm->prepare(),
                    'bookStore' => \$bookStore,
                ]),
                StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY
            );
        } catch (Throwable \$exception) {
            \$this->messenger->addError(Message::AN_ERROR_OCCURRED);
            \$this->logger->err('Delete BookStore', [
                'error' => \$exception->getMessage(),
                'file'  => \$exception->getFile(),
                'line'  => \$exception->getLine(),
                'trace' => \$exception->getTraceAsString(),
            ]);

            return new EmptyResponse(StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR);
        }
    }
}

BODY;
    }

    private function dataProviderGetEditResourceHandler(): string
    {
        $editResourceForm = $this->fileSystem->editResourceForm($this->resourceName);
        $serviceInterface = $this->fileSystem->serviceInterface($this->resourceName);

        $uses = [
            sprintf('use %s;', $this->import->getNotFoundExceptionFqcn()),
            sprintf('use %s;', $editResourceForm->getComponent()->getFqcn()),
            sprintf('use %s;', $serviceInterface->getComponent()->getFqcn()),
            sprintf('use %s;', Import::DOT_DEPENDENCYINJECTION_ATTRIBUTE_INJECT),
            sprintf('use %s;', Import::DOT_FLASHMESSENGER_FLASHMESSENGERINTERFACE),
            sprintf('use %s;', Import::FIG_HTTP_MESSAGE_STATUSCODEINTERFACE),
            sprintf('use %s;', Import::LAMINAS_DIACTOROS_RESPONSE_EMPTYRESPONSE),
            sprintf('use %s;', Import::LAMINAS_DIACTOROS_RESPONSE_HTMLRESPONSE),
            sprintf('use %s;', Import::MEZZIO_ROUTER_ROUTERINTERFACE),
            sprintf('use %s;', Import::MEZZIO_TEMPLATE_TEMPLATERENDERERINTERFACE),
            sprintf('use %s;', Import::PSR_HTTP_MESSAGE_RESPONSEINTERFACE),
            sprintf('use %s;', Import::PSR_HTTP_MESSAGE_SERVERREQUESTINTERFACE),
            sprintf('use %s;', Import::PSR_HTTP_SERVER_REQUESTHANDLERINTERFACE),
        ];
        $uses = implode(PHP_EOL, $uses);

        // phpcs:disable Generic.Files.LineLength.TooLong
        return <<<BODY
<?php

declare(strict_types=1);

namespace Admin\ModuleName\Handler\BookStore;

{$uses}

class GetBookStoreEditFormHandler implements RequestHandlerInterface
{
    #[Inject(
        BookStoreServiceInterface::class,
        RouterInterface::class,
        TemplateRendererInterface::class,
        FlashMessengerInterface::class,
        EditBookStoreForm::class,
    )]
    public function __construct(
        protected BookStoreServiceInterface \$bookStoreService,
        protected RouterInterface \$router,
        protected TemplateRendererInterface \$template,
        protected FlashMessengerInterface \$messenger,
        protected EditBookStoreForm \$editBookStoreForm,
    ) {
    }

    public function handle(
        ServerRequestInterface \$request,
    ): ResponseInterface {
        try {
            \$bookStore = \$this->bookStoreService->findBookStore(\$request->getAttribute('uuid'));
        } catch (NotFoundException \$exception) {
            \$this->messenger->addError(\$exception->getMessage());

            return new EmptyResponse(StatusCodeInterface::STATUS_NOT_FOUND);
        }

        \$this->editBookStoreForm
            ->setAttribute(
                'action',
                \$this->router->generateUri('book-store::book-store-edit', ['uuid' => \$bookStore->getUuid()->toString()])
            )
            ->bind(\$bookStore);

        return new HtmlResponse(
            \$this->template->render('book-store::book-store-edit-form', [
                'form' => \$this->editBookStoreForm->prepare(),
                'book-store' => \$bookStore,
            ])
        );
    }
}

BODY;
        // phpcs:enable Generic.Files.LineLength.TooLong
    }

    private function dataProviderPostEditResourceHandler(): string
    {
        $editResourceForm = $this->fileSystem->editResourceForm($this->resourceName);
        $serviceInterface = $this->fileSystem->serviceInterface($this->resourceName);

        $uses = [
            sprintf('use %s;', $this->import->getBadRequestExceptionFqcn()),
            sprintf('use %s;', $this->import->getConflictExceptionFqcn()),
            sprintf('use %s;', $this->import->getNotFoundExceptionFqcn()),
            sprintf('use %s;', $this->import->getAppMessageFqcn()),
            sprintf('use %s;', $editResourceForm->getComponent()->getFqcn()),
            sprintf('use %s;', $serviceInterface->getComponent()->getFqcn()),
            sprintf('use %s;', Import::DOT_DEPENDENCYINJECTION_ATTRIBUTE_INJECT),
            sprintf('use %s;', Import::DOT_FLASHMESSENGER_FLASHMESSENGERINTERFACE),
            sprintf('use %s;', Import::DOT_LOG_LOGGER),
            sprintf('use %s;', Import::FIG_HTTP_MESSAGE_STATUSCODEINTERFACE),
            sprintf('use %s;', Import::LAMINAS_DIACTOROS_RESPONSE_EMPTYRESPONSE),
            sprintf('use %s;', Import::LAMINAS_DIACTOROS_RESPONSE_HTMLRESPONSE),
            sprintf('use %s;', Import::MEZZIO_ROUTER_ROUTERINTERFACE),
            sprintf('use %s;', Import::MEZZIO_TEMPLATE_TEMPLATERENDERERINTERFACE),
            sprintf('use %s;', Import::PSR_HTTP_MESSAGE_RESPONSEINTERFACE),
            sprintf('use %s;', Import::PSR_HTTP_MESSAGE_SERVERREQUESTINTERFACE),
            sprintf('use %s;', Import::PSR_HTTP_SERVER_REQUESTHANDLERINTERFACE),
            sprintf('use %s;', Import::THROWABLE),
        ];
        $uses = implode(PHP_EOL, $uses);

        // phpcs:disable Generic.Files.LineLength.TooLong
        return <<<BODY
<?php

declare(strict_types=1);

namespace Admin\ModuleName\Handler\BookStore;

{$uses}

class PostBookStoreEditHandler implements RequestHandlerInterface
{
    #[Inject(
        BookStoreServiceInterface::class,
        RouterInterface::class,
        TemplateRendererInterface::class,
        FlashMessengerInterface::class,
        EditBookStoreForm::class,
        'dot-log.default_logger',
    )]
    public function __construct(
        protected BookStoreServiceInterface \$bookStoreService,
        protected RouterInterface \$router,
        protected TemplateRendererInterface \$template,
        protected FlashMessengerInterface \$messenger,
        protected EditBookStoreForm \$editBookStoreForm,
        protected Logger \$logger,
    ) {
    }

    public function handle(
        ServerRequestInterface \$request,
    ): ResponseInterface {
        try {
            \$bookStore = \$this->bookStoreService->findBookStore(\$request->getAttribute('uuid'));
        } catch (NotFoundException \$exception) {
            \$this->messenger->addError(\$exception->getMessage());

            return new EmptyResponse(StatusCodeInterface::STATUS_NOT_FOUND);
        }

        \$this->editBookStoreForm
            ->setAttribute(
                'action',
                \$this->router->generateUri('book-store::book-store-edit', ['uuid' => \$bookStore->getUuid()->toString()])
            );

        try {
            \$data = (array) \$request->getParsedBody();
            \$this->editBookStoreForm->setData(\$data);
            if (\$this->editBookStoreForm->isValid()) {
                \$data = (array) \$this->editBookStoreForm->getData();
                \$this->bookStoreService->saveBookStore(\$data, \$bookStore);
                \$this->messenger->addSuccess(Message::BOOK_STORE_UPDATED);

                return new EmptyResponse(StatusCodeInterface::STATUS_CREATED);
            }

            return new HtmlResponse(
                \$this->template->render('book-store::book-store-edit-form', [
                    'form' => \$this->editBookStoreForm->prepare(),
                    'bookStore' => \$bookStore,
                ]),
                StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY
            );
        } catch (BadRequestException | ConflictException | NotFoundException \$exception) {
            return new HtmlResponse(
                \$this->template->render('book-store::book-store-edit-form', [
                    'form' => \$this->editBookStoreForm->prepare(),
                    'bookStore' => \$bookStore,
                    'messages' => [
                        'error' => \$exception->getMessage(),
                    ],
                ]),
                StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY
            );
        } catch (Throwable \$exception) {
            \$this->messenger->addError(Message::AN_ERROR_OCCURRED);
            \$this->logger->err('Update BookStore', [
                'error' => \$exception->getMessage(),
                'file'  => \$exception->getFile(),
                'line'  => \$exception->getLine(),
                'trace' => \$exception->getTraceAsString(),
            ]);

            return new EmptyResponse(StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR);
        }
    }
}

BODY;
        // phpcs:enable Generic.Files.LineLength.TooLong
    }
}
