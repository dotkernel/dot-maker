<?php

declare(strict_types=1);

namespace DotTest\Maker\Type;

use Dot\Maker\ColorEnum;
use Dot\Maker\Config;
use Dot\Maker\Context;
use Dot\Maker\FileSystem;
use Dot\Maker\IO\Input;
use Dot\Maker\IO\Output;
use Dot\Maker\Message;
use Dot\Maker\Type\Module;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

use function fclose;
use function fopen;
use function fwrite;
use function rewind;
use function stream_get_contents;

use const PHP_EOL;

class ModuleTest extends TestCase
{
    private Config $config;
    private Context $context;
    private FileSystem $fileSystem;
    private string $moduleName   = 'BookStore';
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

    public function testWillCallAccessors(): void
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

        $config     = new Config($root->url());
        $context    = new Context($root->url());
        $fileSystem = (new FileSystem($context))->setModuleName($this->moduleName);

        fwrite($this->inputStream, PHP_EOL);
        rewind($this->inputStream);

        $module = new Module($fileSystem, $context, $config);
        $this->assertContainsOnlyInstancesOf(Config::class, [$module->getConfig()]);
        $this->assertContainsOnlyInstancesOf(Context::class, [$module->getContext()]);
        $this->assertContainsOnlyInstancesOf(FileSystem::class, [$module->getFileSystem()]);

        $this->assertTrue($module->isModule());
        $this->assertNull($module->getModule());
        $this->assertFalse($module->hasModule());
        $module->setModule($module);
        $this->assertContainsOnlyInstancesOf(Module::class, [$module->getModule()]);
        $this->assertTrue($module->hasModule());

        $module->setConfig(new Config($root->url()));
        $module->setContext(new Context($root->url()));
        $module->setFileSystem((new FileSystem($context))->setModuleName($this->moduleName));
        $this->assertContainsOnlyInstancesOf(Config::class, [$module->getConfig()]);
        $this->assertContainsOnlyInstancesOf(Context::class, [$module->getContext()]);
        $this->assertContainsOnlyInstancesOf(FileSystem::class, [$module->getFileSystem()]);
    }

    public function testCallToInvokeWillEarlyReturnOnEmptyInput(): void
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

        fwrite($this->inputStream, PHP_EOL);
        rewind($this->inputStream);

        $module = new Module($this->fileSystem, $this->context, $this->config);
        $module->setModule($module);
        $module();

        rewind($this->errorStream);
        rewind($this->outputStream);
        $this->assertSame(PHP_EOL . 'New module name: ', stream_get_contents($this->outputStream));
        $this->assertEmpty(stream_get_contents($this->errorStream));
    }

    public function testCallToInvokeWillFailWhenNameIsInvalid(): void
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

        fwrite($this->inputStream, '.' . PHP_EOL);
        rewind($this->inputStream);

        $module = new Module($this->fileSystem, $this->context, $this->config);
        $module->setModule($module);
        $module();

        rewind($this->errorStream);
        $this->assertSame(
            ColorEnum::colorize('Invalid module name: "."', ColorEnum::ForegroundBrightRed) . PHP_EOL,
            stream_get_contents($this->errorStream)
        );
    }

    public function testCallToInvokeWillFailWhenModuleAlreadyExists(): void
    {
        $root = vfsStream::setup('root', 0644, [
            'composer.json' => '{
                "autoload": {
                    "psr-4": {
                        "Api\\\\App\\\\": "src/App/src/"
                    }
                }
            }',
            'src'           => [
                'App' => [],
            ],
        ]);

        $this->config     = new Config($root->url());
        $this->context    = new Context($root->url());
        $this->fileSystem = (new FileSystem($this->context))->setModuleName($this->moduleName);

        fwrite($this->inputStream, 'App' . PHP_EOL);
        rewind($this->inputStream);

        $module = new Module($this->fileSystem, $this->context, $this->config);
        $module->setModule($module);
        $module();

        rewind($this->errorStream);
        $this->assertSame(
            ColorEnum::colorize(
                'Module "App" already exists at vfs://root/src/App',
                ColorEnum::ForegroundBrightRed
            ) . PHP_EOL,
            stream_get_contents($this->errorStream)
        );
    }

    public function testCallToInvokeWillFailWhenUnableToCreateModuleDirectory(): void
    {
        $root = vfsStream::setup('root', 0000, [
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

        fwrite($this->inputStream, 'App' . PHP_EOL);
        rewind($this->inputStream);

        $module = new Module($this->fileSystem, $this->context, $this->config);
        $module->setModule($module);
        $module();

        rewind($this->errorStream);
        $this->assertSame(
            ColorEnum::colorize(
                'Could not create directory "vfs://root/src/App"',
                ColorEnum::ForegroundBrightRed
            ) . PHP_EOL,
            stream_get_contents($this->errorStream)
        );
    }

    public function testWillAddMessage(): void
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

        $module = new Module($this->fileSystem, $this->context, $this->config);
        $this->assertEmpty($module->getMessages());
        $module->addMessage(new Message('test'));
        $this->assertCount(1, $module->getMessages());
        $this->assertSame('test', $module->getMessages()[0]);
    }

    public function testInitExistingWillNotInitWhenInvalidModuleName(): void
    {
        $root = vfsStream::setup('root', 0644, [
            'composer.json' => '{
                "autoload": {
                    "psr-4": {
                        "Api\\\\App\\\\": "src/App/src/"
                    }
                }
            }',
            'src'           => [
                'App' => [],
            ],
        ]);

        $this->config     = new Config($root->url());
        $this->context    = new Context($root->url());
        $this->fileSystem = (new FileSystem($this->context))->setModuleName($this->moduleName);

        fwrite($this->inputStream, '.' . PHP_EOL);
        fwrite($this->inputStream, 'App' . PHP_EOL);
        rewind($this->inputStream);

        $module = new Module($this->fileSystem, $this->context, $this->config);
        $module->initExisting();

        rewind($this->errorStream);
        $this->assertSame(
            ColorEnum::colorize('Invalid module name: "."', ColorEnum::ForegroundBrightRed) . PHP_EOL,
            stream_get_contents($this->errorStream)
        );
    }

    public function testInitExistingWillNotInitFromInexistentModule(): void
    {
        $root = vfsStream::setup('root', 0644, [
            'composer.json' => '{
                "autoload": {
                    "psr-4": {
                        "Api\\\\App\\\\": "src/App/src/"
                    }
                }
            }',
            'src'           => [
                'App' => [],
            ],
        ]);

        $this->config     = new Config($root->url());
        $this->context    = new Context($root->url());
        $this->fileSystem = (new FileSystem($this->context))->setModuleName($this->moduleName);

        fwrite($this->inputStream, 'Test' . PHP_EOL);
        fwrite($this->inputStream, 'App' . PHP_EOL);
        rewind($this->inputStream);

        $module = new Module($this->fileSystem, $this->context, $this->config);
        $module->initExisting();

        rewind($this->errorStream);
        $this->assertSame(
            ColorEnum::colorize('Module "Test" not found', ColorEnum::ForegroundBrightRed) . PHP_EOL,
            stream_get_contents($this->errorStream)
        );
    }

    public function testInitExistingWillInitExistingModule(): void
    {
        $root = vfsStream::setup('root', 0644, [
            'composer.json' => '{
                "autoload": {
                    "psr-4": {
                        "Api\\\\App\\\\": "src/App/src/"
                    }
                }
            }',
            'src'           => [
                'App' => [],
            ],
        ]);

        $this->config     = new Config($root->url());
        $this->context    = new Context($root->url());
        $this->fileSystem = (new FileSystem($this->context))->setModuleName($this->moduleName);
        $this->assertSame('BookStore', $this->fileSystem->getModuleName());

        fwrite($this->inputStream, 'App' . PHP_EOL);
        rewind($this->inputStream);

        $module = new Module($this->fileSystem, $this->context, $this->config);
        $module->initExisting();

        rewind($this->errorStream);
        rewind($this->outputStream);
        $this->assertEmpty(stream_get_contents($this->errorStream));
        $this->assertSame(
            PHP_EOL . "Existing module name: \033[92mFound Module \"App\"\033[0m" . PHP_EOL,
            stream_get_contents($this->outputStream)
        );
        $this->assertSame('App', $this->fileSystem->getModuleName());
    }

    public function testWillIdentifyAsModule(): void
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

        $module = new Module($this->fileSystem, $this->context, $this->config);
        $this->assertTrue($module->isModule());
    }

    public function testWillNotRenderMessagesWhenNoMessageAvailable(): void
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

        $module = new Module($this->fileSystem, $this->context, $this->config);
        $module->renderMessages();

        rewind($this->outputStream);
        $this->assertEmpty(stream_get_contents($this->outputStream));
    }

    public function testWillCreateFilesWhenProjectTypeIsApiAndUsesCore(): void
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

        $entity                    = $this->fileSystem->entity($this->resourceName);
        $repository                = $this->fileSystem->repository($this->resourceName);
        $service                   = $this->fileSystem->service($this->resourceName);
        $serviceInterface          = $this->fileSystem->serviceInterface($this->resourceName);
        $command                   = $this->fileSystem->command($this->resourceName);
        $middleware                = $this->fileSystem->middleware($this->resourceName);
        $apiGetCollectionHandler   = $this->fileSystem->apiGetCollectionHandler($this->resourceName);
        $apiGetResourceHandler     = $this->fileSystem->apiGetResourceHandler($this->resourceName);
        $apiPostResourceHandler    = $this->fileSystem->apiPostResourceHandler($this->resourceName);
        $apiDeleteResourceHandler  = $this->fileSystem->apiDeleteResourceHandler($this->resourceName);
        $apiPatchResourceHandler   = $this->fileSystem->apiPatchResourceHandler($this->resourceName);
        $apiPutResourceHandler     = $this->fileSystem->apiPutResourceHandler($this->resourceName);
        $getListResourcesHandler   = $this->fileSystem->getListResourcesHandler($this->resourceName);
        $getViewResourceHandler    = $this->fileSystem->getViewResourceHandler($this->resourceName);
        $getCreateResourceHandler  = $this->fileSystem->getCreateResourceHandler($this->resourceName);
        $postCreateResourceHandler = $this->fileSystem->postCreateResourceHandler($this->resourceName);
        $getDeleteResourceHandler  = $this->fileSystem->getDeleteResourceHandler($this->resourceName);
        $postDeleteResourceHandler = $this->fileSystem->postDeleteResourceHandler($this->resourceName);
        $getEditResourceHandler    = $this->fileSystem->getEditResourceHandler($this->resourceName);
        $postEditResourceHandler   = $this->fileSystem->postEditResourceHandler($this->resourceName);
        $configProvider            = $this->fileSystem->configProvider();
        $coreConfigProvider        = $this->fileSystem->coreConfigProvider();
        $routesDelegator           = $this->fileSystem->routesDelegator();
        $openApi                   = $this->fileSystem->openApi();

        $this->assertFileDoesNotExist($entity->getPath());
        $this->assertFalse($entity->exists());
        $this->assertFileDoesNotExist($repository->getPath());
        $this->assertFalse($repository->exists());
        $this->assertFileDoesNotExist($service->getPath());
        $this->assertFalse($service->exists());
        $this->assertFileDoesNotExist($serviceInterface->getPath());
        $this->assertFalse($serviceInterface->exists());
        $this->assertFileDoesNotExist($command->getPath());
        $this->assertFalse($command->exists());
        $this->assertFileDoesNotExist($middleware->getPath());
        $this->assertFalse($middleware->exists());
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
        $this->assertFileDoesNotExist($apiPutResourceHandler->getPath());
        $this->assertFalse($apiPutResourceHandler->exists());
        $this->assertFileDoesNotExist($getListResourcesHandler->getPath());
        $this->assertFalse($getListResourcesHandler->exists());
        $this->assertFileDoesNotExist($getViewResourceHandler->getPath());
        $this->assertFalse($getViewResourceHandler->exists());
        $this->assertFileDoesNotExist($getCreateResourceHandler->getPath());
        $this->assertFalse($getCreateResourceHandler->exists());
        $this->assertFileDoesNotExist($postCreateResourceHandler->getPath());
        $this->assertFalse($postCreateResourceHandler->exists());
        $this->assertFileDoesNotExist($getDeleteResourceHandler->getPath());
        $this->assertFalse($getDeleteResourceHandler->exists());
        $this->assertFileDoesNotExist($postDeleteResourceHandler->getPath());
        $this->assertFalse($postDeleteResourceHandler->exists());
        $this->assertFileDoesNotExist($getEditResourceHandler->getPath());
        $this->assertFalse($getEditResourceHandler->exists());
        $this->assertFileDoesNotExist($postEditResourceHandler->getPath());
        $this->assertFalse($postEditResourceHandler->exists());
        $this->assertFileDoesNotExist($configProvider->getPath());
        $this->assertFalse($configProvider->exists());
        $this->assertFileDoesNotExist($coreConfigProvider->getPath());
        $this->assertFalse($coreConfigProvider->exists());
        $this->assertFileDoesNotExist($routesDelegator->getPath());
        $this->assertFalse($routesDelegator->exists());
        $this->assertFileDoesNotExist($openApi->getPath());
        $this->assertFalse($openApi->exists());

        fwrite($this->inputStream, $this->resourceName . PHP_EOL);
        fwrite($this->inputStream, 'yes' . PHP_EOL); // Create entity and repository?
        fwrite($this->inputStream, 'yes' . PHP_EOL); // Create service and service interface?
        fwrite($this->inputStream, 'yes' . PHP_EOL); // Create command?
        fwrite($this->inputStream, 'yes' . PHP_EOL); // Create middleware?
        fwrite($this->inputStream, 'yes' . PHP_EOL); // Create handler?
        fwrite($this->inputStream, 'yes' . PHP_EOL); // Allow listing resources?
        fwrite($this->inputStream, 'yes' . PHP_EOL); // Allow viewing resources?
        fwrite($this->inputStream, 'yes' . PHP_EOL); // Allow creating resources?
        fwrite($this->inputStream, 'yes' . PHP_EOL); // Allow deleting resources?
        fwrite($this->inputStream, 'yes' . PHP_EOL); // Allow editing resources?
        fwrite($this->inputStream, 'yes' . PHP_EOL); // Allow replacing resources?
        rewind($this->inputStream);

        $module = new Module($this->fileSystem, $this->context, $this->config);
        $module->setModule($module);
        $module();

        $this->assertFileExists($entity->getPath());
        $this->assertTrue($entity->exists());
        $this->assertFileExists($repository->getPath());
        $this->assertTrue($repository->exists());
        $this->assertFileExists($service->getPath());
        $this->assertTrue($service->exists());
        $this->assertFileExists($serviceInterface->getPath());
        $this->assertTrue($serviceInterface->exists());
        $this->assertFileExists($command->getPath());
        $this->assertTrue($command->exists());
        $this->assertFileExists($middleware->getPath());
        $this->assertTrue($middleware->exists());
        $this->assertFileExists($apiGetCollectionHandler->getPath());
        $this->assertTrue($apiGetCollectionHandler->exists());
        $this->assertFileExists($apiGetResourceHandler->getPath());
        $this->assertTrue($apiGetResourceHandler->exists());
        $this->assertFileExists($apiPostResourceHandler->getPath());
        $this->assertTrue($apiPostResourceHandler->exists());
        $this->assertFileExists($apiDeleteResourceHandler->getPath());
        $this->assertTrue($apiDeleteResourceHandler->exists());
        $this->assertFileExists($apiPatchResourceHandler->getPath());
        $this->assertTrue($apiPatchResourceHandler->exists());
        $this->assertFileExists($apiPutResourceHandler->getPath());
        $this->assertTrue($apiPutResourceHandler->exists());
        $this->assertFileDoesNotExist($getListResourcesHandler->getPath());
        $this->assertFalse($getListResourcesHandler->exists());
        $this->assertFileDoesNotExist($getViewResourceHandler->getPath());
        $this->assertFalse($getViewResourceHandler->exists());
        $this->assertFileDoesNotExist($getCreateResourceHandler->getPath());
        $this->assertFalse($getCreateResourceHandler->exists());
        $this->assertFileDoesNotExist($postCreateResourceHandler->getPath());
        $this->assertFalse($postCreateResourceHandler->exists());
        $this->assertFileDoesNotExist($getDeleteResourceHandler->getPath());
        $this->assertFalse($getDeleteResourceHandler->exists());
        $this->assertFileDoesNotExist($postDeleteResourceHandler->getPath());
        $this->assertFalse($postDeleteResourceHandler->exists());
        $this->assertFileDoesNotExist($getEditResourceHandler->getPath());
        $this->assertFalse($getEditResourceHandler->exists());
        $this->assertFileDoesNotExist($postEditResourceHandler->getPath());
        $this->assertFalse($postEditResourceHandler->exists());
        $this->assertFileExists($configProvider->getPath());
        $this->assertTrue($configProvider->exists());
        $this->assertFileExists($coreConfigProvider->getPath());
        $this->assertTrue($coreConfigProvider->exists());
        $this->assertFileExists($routesDelegator->getPath());
        $this->assertTrue($routesDelegator->exists());
        $this->assertFileExists($openApi->getPath());
        $this->assertTrue($openApi->exists());

        rewind($this->errorStream);
        rewind($this->outputStream);
        $this->assertEmpty(stream_get_contents($this->errorStream));
        $this->assertSame(
            $this->dataProviderWhenProjectTypeIsApiAndUsesCore(),
            stream_get_contents($this->outputStream)
        );
    }

    public function testWillCreateFilesWhenProjectTypeIsApiAndDoesNotUseCore(): void
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

        $entity                    = $this->fileSystem->entity($this->resourceName);
        $repository                = $this->fileSystem->repository($this->resourceName);
        $service                   = $this->fileSystem->service($this->resourceName);
        $serviceInterface          = $this->fileSystem->serviceInterface($this->resourceName);
        $command                   = $this->fileSystem->command($this->resourceName);
        $middleware                = $this->fileSystem->middleware($this->resourceName);
        $apiGetCollectionHandler   = $this->fileSystem->apiGetCollectionHandler($this->resourceName);
        $apiGetResourceHandler     = $this->fileSystem->apiGetResourceHandler($this->resourceName);
        $apiPostResourceHandler    = $this->fileSystem->apiPostResourceHandler($this->resourceName);
        $apiDeleteResourceHandler  = $this->fileSystem->apiDeleteResourceHandler($this->resourceName);
        $apiPatchResourceHandler   = $this->fileSystem->apiPatchResourceHandler($this->resourceName);
        $apiPutResourceHandler     = $this->fileSystem->apiPutResourceHandler($this->resourceName);
        $getListResourcesHandler   = $this->fileSystem->getListResourcesHandler($this->resourceName);
        $getViewResourceHandler    = $this->fileSystem->getViewResourceHandler($this->resourceName);
        $getCreateResourceHandler  = $this->fileSystem->getCreateResourceHandler($this->resourceName);
        $postCreateResourceHandler = $this->fileSystem->postCreateResourceHandler($this->resourceName);
        $getDeleteResourceHandler  = $this->fileSystem->getDeleteResourceHandler($this->resourceName);
        $postDeleteResourceHandler = $this->fileSystem->postDeleteResourceHandler($this->resourceName);
        $getEditResourceHandler    = $this->fileSystem->getEditResourceHandler($this->resourceName);
        $postEditResourceHandler   = $this->fileSystem->postEditResourceHandler($this->resourceName);
        $configProvider            = $this->fileSystem->configProvider();
        $coreConfigProvider        = $this->fileSystem->coreConfigProvider();
        $routesDelegator           = $this->fileSystem->routesDelegator();
        $openApi                   = $this->fileSystem->openApi();

        $this->assertFileDoesNotExist($entity->getPath());
        $this->assertFalse($entity->exists());
        $this->assertFileDoesNotExist($repository->getPath());
        $this->assertFalse($repository->exists());
        $this->assertFileDoesNotExist($service->getPath());
        $this->assertFalse($service->exists());
        $this->assertFileDoesNotExist($serviceInterface->getPath());
        $this->assertFalse($serviceInterface->exists());
        $this->assertFileDoesNotExist($command->getPath());
        $this->assertFalse($command->exists());
        $this->assertFileDoesNotExist($middleware->getPath());
        $this->assertFalse($middleware->exists());
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
        $this->assertFileDoesNotExist($apiPutResourceHandler->getPath());
        $this->assertFalse($apiPutResourceHandler->exists());
        $this->assertFileDoesNotExist($getListResourcesHandler->getPath());
        $this->assertFalse($getListResourcesHandler->exists());
        $this->assertFileDoesNotExist($getViewResourceHandler->getPath());
        $this->assertFalse($getViewResourceHandler->exists());
        $this->assertFileDoesNotExist($getCreateResourceHandler->getPath());
        $this->assertFalse($getCreateResourceHandler->exists());
        $this->assertFileDoesNotExist($postCreateResourceHandler->getPath());
        $this->assertFalse($postCreateResourceHandler->exists());
        $this->assertFileDoesNotExist($getDeleteResourceHandler->getPath());
        $this->assertFalse($getDeleteResourceHandler->exists());
        $this->assertFileDoesNotExist($postDeleteResourceHandler->getPath());
        $this->assertFalse($postDeleteResourceHandler->exists());
        $this->assertFileDoesNotExist($getEditResourceHandler->getPath());
        $this->assertFalse($getEditResourceHandler->exists());
        $this->assertFileDoesNotExist($postEditResourceHandler->getPath());
        $this->assertFalse($postEditResourceHandler->exists());
        $this->assertFileDoesNotExist($configProvider->getPath());
        $this->assertFalse($configProvider->exists());
        $this->assertFileDoesNotExist($coreConfigProvider->getPath());
        $this->assertFalse($coreConfigProvider->exists());
        $this->assertFileDoesNotExist($routesDelegator->getPath());
        $this->assertFalse($routesDelegator->exists());
        $this->assertFileDoesNotExist($openApi->getPath());
        $this->assertFalse($openApi->exists());

        fwrite($this->inputStream, $this->resourceName . PHP_EOL);
        fwrite($this->inputStream, 'yes' . PHP_EOL); // Create entity and repository?
        fwrite($this->inputStream, 'yes' . PHP_EOL); // Create service and service interface?
        fwrite($this->inputStream, 'yes' . PHP_EOL); // Create command?
        fwrite($this->inputStream, 'yes' . PHP_EOL); // Create middleware?
        fwrite($this->inputStream, 'yes' . PHP_EOL); // Create handler?
        fwrite($this->inputStream, 'yes' . PHP_EOL); // Allow listing resources?
        fwrite($this->inputStream, 'yes' . PHP_EOL); // Allow viewing resources?
        fwrite($this->inputStream, 'yes' . PHP_EOL); // Allow creating resources?
        fwrite($this->inputStream, 'yes' . PHP_EOL); // Allow deleting resources?
        fwrite($this->inputStream, 'yes' . PHP_EOL); // Allow editing resources?
        fwrite($this->inputStream, 'yes' . PHP_EOL); // Allow replacing resources?
        rewind($this->inputStream);

        $module = new Module($this->fileSystem, $this->context, $this->config);
        $module->setModule($module);
        $module();

        $this->assertFileExists($entity->getPath());
        $this->assertTrue($entity->exists());
        $this->assertFileExists($repository->getPath());
        $this->assertTrue($repository->exists());
        $this->assertFileExists($service->getPath());
        $this->assertTrue($service->exists());
        $this->assertFileExists($serviceInterface->getPath());
        $this->assertTrue($serviceInterface->exists());
        $this->assertFileExists($command->getPath());
        $this->assertTrue($command->exists());
        $this->assertFileExists($middleware->getPath());
        $this->assertTrue($middleware->exists());
        $this->assertFileExists($apiGetCollectionHandler->getPath());
        $this->assertTrue($apiGetCollectionHandler->exists());
        $this->assertFileExists($apiGetResourceHandler->getPath());
        $this->assertTrue($apiGetResourceHandler->exists());
        $this->assertFileExists($apiPostResourceHandler->getPath());
        $this->assertTrue($apiPostResourceHandler->exists());
        $this->assertFileExists($apiDeleteResourceHandler->getPath());
        $this->assertTrue($apiDeleteResourceHandler->exists());
        $this->assertFileExists($apiPatchResourceHandler->getPath());
        $this->assertTrue($apiPatchResourceHandler->exists());
        $this->assertFileExists($apiPutResourceHandler->getPath());
        $this->assertTrue($apiPutResourceHandler->exists());
        $this->assertFileDoesNotExist($getListResourcesHandler->getPath());
        $this->assertFalse($getListResourcesHandler->exists());
        $this->assertFileDoesNotExist($getViewResourceHandler->getPath());
        $this->assertFalse($getViewResourceHandler->exists());
        $this->assertFileDoesNotExist($getCreateResourceHandler->getPath());
        $this->assertFalse($getCreateResourceHandler->exists());
        $this->assertFileDoesNotExist($postCreateResourceHandler->getPath());
        $this->assertFalse($postCreateResourceHandler->exists());
        $this->assertFileDoesNotExist($getDeleteResourceHandler->getPath());
        $this->assertFalse($getDeleteResourceHandler->exists());
        $this->assertFileDoesNotExist($postDeleteResourceHandler->getPath());
        $this->assertFalse($postDeleteResourceHandler->exists());
        $this->assertFileDoesNotExist($getEditResourceHandler->getPath());
        $this->assertFalse($getEditResourceHandler->exists());
        $this->assertFileDoesNotExist($postEditResourceHandler->getPath());
        $this->assertFalse($postEditResourceHandler->exists());
        $this->assertFileExists($configProvider->getPath());
        $this->assertTrue($configProvider->exists());
        $this->assertFileDoesNotExist($coreConfigProvider->getPath());
        $this->assertFalse($coreConfigProvider->exists());
        $this->assertFileExists($routesDelegator->getPath());
        $this->assertTrue($routesDelegator->exists());
        $this->assertFileExists($openApi->getPath());
        $this->assertTrue($openApi->exists());

        rewind($this->errorStream);
        rewind($this->outputStream);
        $this->assertEmpty(stream_get_contents($this->errorStream));
        $this->assertSame(
            $this->dataProviderWhenProjectTypeIsApiAndDoesNotUseCore(),
            stream_get_contents($this->outputStream)
        );
    }

    public function testWillCreateFilesWhenProjectTypeIsNotApiAndUsesCore(): void
    {
        $root = vfsStream::setup('root', 0644, [
            'composer.json' => '{
                "autoload": {
                    "psr-4": {
                        "Admin\\\\App\\\\": "src/Admin/src/",
                        "Core\\\\App\\\\": "src/Core/src/App/src/"
                    }
                }
            }',
        ]);

        $this->config     = new Config($root->url());
        $this->context    = new Context($root->url());
        $this->fileSystem = (new FileSystem($this->context))->setModuleName($this->moduleName);

        $entity                    = $this->fileSystem->entity($this->resourceName);
        $repository                = $this->fileSystem->repository($this->resourceName);
        $service                   = $this->fileSystem->service($this->resourceName);
        $serviceInterface          = $this->fileSystem->serviceInterface($this->resourceName);
        $command                   = $this->fileSystem->command($this->resourceName);
        $middleware                = $this->fileSystem->middleware($this->resourceName);
        $apiGetCollectionHandler   = $this->fileSystem->apiGetCollectionHandler($this->resourceName);
        $apiGetResourceHandler     = $this->fileSystem->apiGetResourceHandler($this->resourceName);
        $apiPostResourceHandler    = $this->fileSystem->apiPostResourceHandler($this->resourceName);
        $apiDeleteResourceHandler  = $this->fileSystem->apiDeleteResourceHandler($this->resourceName);
        $apiPatchResourceHandler   = $this->fileSystem->apiPatchResourceHandler($this->resourceName);
        $apiPutResourceHandler     = $this->fileSystem->apiPutResourceHandler($this->resourceName);
        $getListResourcesHandler   = $this->fileSystem->getListResourcesHandler($this->resourceName);
        $getViewResourceHandler    = $this->fileSystem->getViewResourceHandler($this->resourceName);
        $getCreateResourceHandler  = $this->fileSystem->getCreateResourceHandler($this->resourceName);
        $postCreateResourceHandler = $this->fileSystem->postCreateResourceHandler($this->resourceName);
        $getDeleteResourceHandler  = $this->fileSystem->getDeleteResourceHandler($this->resourceName);
        $postDeleteResourceHandler = $this->fileSystem->postDeleteResourceHandler($this->resourceName);
        $getEditResourceHandler    = $this->fileSystem->getEditResourceHandler($this->resourceName);
        $postEditResourceHandler   = $this->fileSystem->postEditResourceHandler($this->resourceName);
        $configProvider            = $this->fileSystem->configProvider();
        $coreConfigProvider        = $this->fileSystem->coreConfigProvider();
        $routesDelegator           = $this->fileSystem->routesDelegator();
        $openApi                   = $this->fileSystem->openApi();

        $this->assertFileDoesNotExist($entity->getPath());
        $this->assertFalse($entity->exists());
        $this->assertFileDoesNotExist($repository->getPath());
        $this->assertFalse($repository->exists());
        $this->assertFileDoesNotExist($service->getPath());
        $this->assertFalse($service->exists());
        $this->assertFileDoesNotExist($serviceInterface->getPath());
        $this->assertFalse($serviceInterface->exists());
        $this->assertFileDoesNotExist($command->getPath());
        $this->assertFalse($command->exists());
        $this->assertFileDoesNotExist($middleware->getPath());
        $this->assertFalse($middleware->exists());
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
        $this->assertFileDoesNotExist($apiPutResourceHandler->getPath());
        $this->assertFalse($apiPutResourceHandler->exists());
        $this->assertFileDoesNotExist($getListResourcesHandler->getPath());
        $this->assertFalse($getListResourcesHandler->exists());
        $this->assertFileDoesNotExist($getViewResourceHandler->getPath());
        $this->assertFalse($getViewResourceHandler->exists());
        $this->assertFileDoesNotExist($getCreateResourceHandler->getPath());
        $this->assertFalse($getCreateResourceHandler->exists());
        $this->assertFileDoesNotExist($postCreateResourceHandler->getPath());
        $this->assertFalse($postCreateResourceHandler->exists());
        $this->assertFileDoesNotExist($getDeleteResourceHandler->getPath());
        $this->assertFalse($getDeleteResourceHandler->exists());
        $this->assertFileDoesNotExist($postDeleteResourceHandler->getPath());
        $this->assertFalse($postDeleteResourceHandler->exists());
        $this->assertFileDoesNotExist($getEditResourceHandler->getPath());
        $this->assertFalse($getEditResourceHandler->exists());
        $this->assertFileDoesNotExist($postEditResourceHandler->getPath());
        $this->assertFalse($postEditResourceHandler->exists());
        $this->assertFileDoesNotExist($configProvider->getPath());
        $this->assertFalse($configProvider->exists());
        $this->assertFileDoesNotExist($coreConfigProvider->getPath());
        $this->assertFalse($coreConfigProvider->exists());
        $this->assertFileDoesNotExist($routesDelegator->getPath());
        $this->assertFalse($routesDelegator->exists());
        $this->assertFileDoesNotExist($openApi->getPath());
        $this->assertFalse($openApi->exists());

        fwrite($this->inputStream, $this->resourceName . PHP_EOL);
        fwrite($this->inputStream, 'yes' . PHP_EOL); // Create entity and repository?
        fwrite($this->inputStream, 'yes' . PHP_EOL); // Create service and service interface?
        fwrite($this->inputStream, 'yes' . PHP_EOL); // Create command?
        fwrite($this->inputStream, 'yes' . PHP_EOL); // Create middleware?
        fwrite($this->inputStream, 'yes' . PHP_EOL); // Create handler?
        fwrite($this->inputStream, 'yes' . PHP_EOL); // Allow listing resources?
        fwrite($this->inputStream, 'yes' . PHP_EOL); // Allow viewing resources?
        fwrite($this->inputStream, 'yes' . PHP_EOL); // Allow creating resources?
        fwrite($this->inputStream, 'yes' . PHP_EOL); // Allow deleting resources?
        fwrite($this->inputStream, 'yes' . PHP_EOL); // Allow editing resources?
        rewind($this->inputStream);

        $module = new Module($this->fileSystem, $this->context, $this->config);
        $module->setModule($module);
        $module();

        $this->assertFileExists($entity->getPath());
        $this->assertTrue($entity->exists());
        $this->assertFileExists($repository->getPath());
        $this->assertTrue($repository->exists());
        $this->assertFileExists($service->getPath());
        $this->assertTrue($service->exists());
        $this->assertFileExists($serviceInterface->getPath());
        $this->assertTrue($serviceInterface->exists());
        $this->assertFileExists($command->getPath());
        $this->assertTrue($command->exists());
        $this->assertFileExists($middleware->getPath());
        $this->assertTrue($middleware->exists());
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
        $this->assertFileDoesNotExist($apiPutResourceHandler->getPath());
        $this->assertFalse($apiPutResourceHandler->exists());
        $this->assertFileExists($getListResourcesHandler->getPath());
        $this->assertTrue($getListResourcesHandler->exists());
        $this->assertFileExists($getViewResourceHandler->getPath());
        $this->assertTrue($getViewResourceHandler->exists());
        $this->assertFileExists($getCreateResourceHandler->getPath());
        $this->assertTrue($getCreateResourceHandler->exists());
        $this->assertFileExists($postCreateResourceHandler->getPath());
        $this->assertTrue($postCreateResourceHandler->exists());
        $this->assertFileExists($getDeleteResourceHandler->getPath());
        $this->assertTrue($getDeleteResourceHandler->exists());
        $this->assertFileExists($postDeleteResourceHandler->getPath());
        $this->assertTrue($postDeleteResourceHandler->exists());
        $this->assertFileExists($getEditResourceHandler->getPath());
        $this->assertTrue($getEditResourceHandler->exists());
        $this->assertFileExists($postEditResourceHandler->getPath());
        $this->assertTrue($postEditResourceHandler->exists());
        $this->assertFileExists($configProvider->getPath());
        $this->assertTrue($configProvider->exists());
        $this->assertFileExists($coreConfigProvider->getPath());
        $this->assertTrue($coreConfigProvider->exists());
        $this->assertFileExists($routesDelegator->getPath());
        $this->assertTrue($routesDelegator->exists());
        $this->assertFileDoesNotExist($openApi->getPath());
        $this->assertFalse($openApi->exists());

        rewind($this->errorStream);
        rewind($this->outputStream);
        $this->assertEmpty(stream_get_contents($this->errorStream));
        $this->assertSame(
            $this->dataProviderWhenProjectTypeIsNotApiAndUsesCore(),
            stream_get_contents($this->outputStream)
        );
    }

    public function testWillCreateFilesWhenProjectTypeIsNootApiAndDoesNotUseCore(): void
    {
        $root = vfsStream::setup('root', 0644, [
            'composer.json' => '{
                "autoload": {
                    "psr-4": {
                        "Admin\\\\App\\\\": "src/Admin/src/"
                    }
                }
            }',
        ]);

        $this->config     = new Config($root->url());
        $this->context    = new Context($root->url());
        $this->fileSystem = (new FileSystem($this->context))->setModuleName($this->moduleName);

        $entity                    = $this->fileSystem->entity($this->resourceName);
        $repository                = $this->fileSystem->repository($this->resourceName);
        $service                   = $this->fileSystem->service($this->resourceName);
        $serviceInterface          = $this->fileSystem->serviceInterface($this->resourceName);
        $command                   = $this->fileSystem->command($this->resourceName);
        $middleware                = $this->fileSystem->middleware($this->resourceName);
        $apiGetCollectionHandler   = $this->fileSystem->apiGetCollectionHandler($this->resourceName);
        $apiGetResourceHandler     = $this->fileSystem->apiGetResourceHandler($this->resourceName);
        $apiPostResourceHandler    = $this->fileSystem->apiPostResourceHandler($this->resourceName);
        $apiDeleteResourceHandler  = $this->fileSystem->apiDeleteResourceHandler($this->resourceName);
        $apiPatchResourceHandler   = $this->fileSystem->apiPatchResourceHandler($this->resourceName);
        $apiPutResourceHandler     = $this->fileSystem->apiPutResourceHandler($this->resourceName);
        $getListResourcesHandler   = $this->fileSystem->getListResourcesHandler($this->resourceName);
        $getViewResourceHandler    = $this->fileSystem->getViewResourceHandler($this->resourceName);
        $getCreateResourceHandler  = $this->fileSystem->getCreateResourceHandler($this->resourceName);
        $postCreateResourceHandler = $this->fileSystem->postCreateResourceHandler($this->resourceName);
        $getDeleteResourceHandler  = $this->fileSystem->getDeleteResourceHandler($this->resourceName);
        $postDeleteResourceHandler = $this->fileSystem->postDeleteResourceHandler($this->resourceName);
        $getEditResourceHandler    = $this->fileSystem->getEditResourceHandler($this->resourceName);
        $postEditResourceHandler   = $this->fileSystem->postEditResourceHandler($this->resourceName);
        $configProvider            = $this->fileSystem->configProvider();
        $coreConfigProvider        = $this->fileSystem->coreConfigProvider();
        $routesDelegator           = $this->fileSystem->routesDelegator();
        $openApi                   = $this->fileSystem->openApi();

        $this->assertFileDoesNotExist($entity->getPath());
        $this->assertFalse($entity->exists());
        $this->assertFileDoesNotExist($repository->getPath());
        $this->assertFalse($repository->exists());
        $this->assertFileDoesNotExist($service->getPath());
        $this->assertFalse($service->exists());
        $this->assertFileDoesNotExist($serviceInterface->getPath());
        $this->assertFalse($serviceInterface->exists());
        $this->assertFileDoesNotExist($command->getPath());
        $this->assertFalse($command->exists());
        $this->assertFileDoesNotExist($middleware->getPath());
        $this->assertFalse($middleware->exists());
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
        $this->assertFileDoesNotExist($apiPutResourceHandler->getPath());
        $this->assertFalse($apiPutResourceHandler->exists());
        $this->assertFileDoesNotExist($getListResourcesHandler->getPath());
        $this->assertFalse($getListResourcesHandler->exists());
        $this->assertFileDoesNotExist($getViewResourceHandler->getPath());
        $this->assertFalse($getViewResourceHandler->exists());
        $this->assertFileDoesNotExist($getCreateResourceHandler->getPath());
        $this->assertFalse($getCreateResourceHandler->exists());
        $this->assertFileDoesNotExist($postCreateResourceHandler->getPath());
        $this->assertFalse($postCreateResourceHandler->exists());
        $this->assertFileDoesNotExist($getDeleteResourceHandler->getPath());
        $this->assertFalse($getDeleteResourceHandler->exists());
        $this->assertFileDoesNotExist($postDeleteResourceHandler->getPath());
        $this->assertFalse($postDeleteResourceHandler->exists());
        $this->assertFileDoesNotExist($getEditResourceHandler->getPath());
        $this->assertFalse($getEditResourceHandler->exists());
        $this->assertFileDoesNotExist($postEditResourceHandler->getPath());
        $this->assertFalse($postEditResourceHandler->exists());
        $this->assertFileDoesNotExist($configProvider->getPath());
        $this->assertFalse($configProvider->exists());
        $this->assertFileDoesNotExist($coreConfigProvider->getPath());
        $this->assertFalse($coreConfigProvider->exists());
        $this->assertFileDoesNotExist($routesDelegator->getPath());
        $this->assertFalse($routesDelegator->exists());
        $this->assertFileDoesNotExist($openApi->getPath());
        $this->assertFalse($openApi->exists());

        fwrite($this->inputStream, $this->resourceName . PHP_EOL);
        fwrite($this->inputStream, 'yes' . PHP_EOL); // Create entity and repository?
        fwrite($this->inputStream, 'yes' . PHP_EOL); // Create service and service interface?
        fwrite($this->inputStream, 'yes' . PHP_EOL); // Create command?
        fwrite($this->inputStream, 'yes' . PHP_EOL); // Create middleware?
        fwrite($this->inputStream, 'yes' . PHP_EOL); // Create handler?
        fwrite($this->inputStream, 'yes' . PHP_EOL); // Allow listing resources?
        fwrite($this->inputStream, 'yes' . PHP_EOL); // Allow viewing resources?
        fwrite($this->inputStream, 'yes' . PHP_EOL); // Allow creating resources?
        fwrite($this->inputStream, 'yes' . PHP_EOL); // Allow deleting resources?
        fwrite($this->inputStream, 'yes' . PHP_EOL); // Allow editing resources?
        rewind($this->inputStream);

        $module = new Module($this->fileSystem, $this->context, $this->config);
        $module->setModule($module);
        $module();

        $this->assertFileExists($entity->getPath());
        $this->assertTrue($entity->exists());
        $this->assertFileExists($repository->getPath());
        $this->assertTrue($repository->exists());
        $this->assertFileExists($service->getPath());
        $this->assertTrue($service->exists());
        $this->assertFileExists($serviceInterface->getPath());
        $this->assertTrue($serviceInterface->exists());
        $this->assertFileExists($command->getPath());
        $this->assertTrue($command->exists());
        $this->assertFileExists($middleware->getPath());
        $this->assertTrue($middleware->exists());
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
        $this->assertFileDoesNotExist($apiPutResourceHandler->getPath());
        $this->assertFalse($apiPutResourceHandler->exists());
        $this->assertFileExists($getListResourcesHandler->getPath());
        $this->assertTrue($getListResourcesHandler->exists());
        $this->assertFileExists($getViewResourceHandler->getPath());
        $this->assertTrue($getViewResourceHandler->exists());
        $this->assertFileExists($getCreateResourceHandler->getPath());
        $this->assertTrue($getCreateResourceHandler->exists());
        $this->assertFileExists($postCreateResourceHandler->getPath());
        $this->assertTrue($postCreateResourceHandler->exists());
        $this->assertFileExists($getDeleteResourceHandler->getPath());
        $this->assertTrue($getDeleteResourceHandler->exists());
        $this->assertFileExists($postDeleteResourceHandler->getPath());
        $this->assertTrue($postDeleteResourceHandler->exists());
        $this->assertFileExists($getEditResourceHandler->getPath());
        $this->assertTrue($getEditResourceHandler->exists());
        $this->assertFileExists($postEditResourceHandler->getPath());
        $this->assertTrue($postEditResourceHandler->exists());
        $this->assertFileExists($configProvider->getPath());
        $this->assertTrue($configProvider->exists());
        $this->assertFileDoesNotExist($coreConfigProvider->getPath());
        $this->assertFalse($coreConfigProvider->exists());
        $this->assertFileExists($routesDelegator->getPath());
        $this->assertTrue($routesDelegator->exists());
        $this->assertFileDoesNotExist($openApi->getPath());
        $this->assertFalse($openApi->exists());

        rewind($this->errorStream);
        rewind($this->outputStream);
        $this->assertEmpty(stream_get_contents($this->errorStream));
        $this->assertSame(
            $this->dataProviderWhenProjectTypeIsNotApiAndDoesNotUseCore(),
            stream_get_contents($this->outputStream)
        );
    }

    private function dataProviderWhenProjectTypeIsApiAndUsesCore(): string
    {
        // phpcs:disable Generic.Files.LineLength.TooLong
        return <<<BODY

New module name: \033[92mCreated directory: vfs://root/src/BookStore\033[0m

Create entity and repository? [Y(es)/n(o)]: \033[92mCreated Entity: vfs://root/src/Core/src/BookStore/src/Entity/BookStore.php\033[0m
\033[92mCreated Repository: vfs://root/src/Core/src/BookStore/src/Repository/BookStoreRepository.php\033[0m

Create service and service interface? [Y(es)/n(o)]: \033[92mCreated Service: vfs://root/src/BookStore/src/Service/BookStoreService.php\033[0m
\033[92mCreated ServiceInterface: vfs://root/src/BookStore/src/Service/BookStoreServiceInterface.php\033[0m

Create command? [Y(es)/n(o)]: \033[92mCreated Command: vfs://root/src/BookStore/src/Command/BookStoreCommand.php\033[0m

Create middleware? [Y(es)/n(o)]: \033[92mCreated Middleware: vfs://root/src/BookStore/src/Middleware/BookStoreMiddleware.php\033[0m

Create handler? [Y(es)/n(o)]: 
Allow listing BookStores? [Y(es)/n(o)]: \033[92mCreated Collection: vfs://root/src/BookStore/src/Collection/BookStoreCollection.php\033[0m
\033[92mCreated Handler: vfs://root/src/BookStore/src/Handler/BookStore/GetBookStoreCollectionHandler.php\033[0m

Allow viewing BookStores? [Y(es)/n(o)]: \033[92mCreated Handler: vfs://root/src/BookStore/src/Handler/BookStore/GetBookStoreResourceHandler.php\033[0m

Allow creating BookStores? [Y(es)/n(o)]: \033[92mCreated Handler: vfs://root/src/BookStore/src/Handler/BookStore/PostBookStoreResourceHandler.php\033[0m
\033[92mCreated InputFilter: vfs://root/src/BookStore/src/InputFilter/CreateBookStoreInputFilter.php\033[0m

Allow deleting BookStores? [Y(es)/n(o)]: \033[92mCreated Handler: vfs://root/src/BookStore/src/Handler/BookStore/DeleteBookStoreResourceHandler.php\033[0m

Allow editing BookStores? [Y(es)/n(o)]: \033[92mCreated Handler: vfs://root/src/BookStore/src/Handler/BookStore/PatchBookStoreResourceHandler.php\033[0m
\033[92mCreated InputFilter: vfs://root/src/BookStore/src/InputFilter/EditBookStoreInputFilter.php\033[0m

Allow replacing BookStores? [Y(es)/n(o)]: \033[92mCreated Handler: vfs://root/src/BookStore/src/Handler/BookStore/PutBookStoreResourceHandler.php\033[0m
\033[92mCreated InputFilter: vfs://root/src/BookStore/src/InputFilter/ReplaceBookStoreInputFilter.php\033[0m

\033[92mCreated RoutesDelegator: vfs://root/src/BookStore/src/RoutesDelegator.php\033[0m

\033[92mCreated OpenAPI: vfs://root/src/BookStore/src/OpenAPI.php\033[0m
\033[92mCreated ConfigProvider: vfs://root/src/BookStore/src/ConfigProvider.php\033[0m
\033[92mCreated Core ConfigProvider: vfs://root/src/Core/src/BookStore/src/ConfigProvider.php\033[0m

\033[93mNext steps:\033[0m
\033[93m===========\033[0m
- add to \033[97mconfig/config.php\033[0m:
\033[93m  Api\BookStore\ConfigProvider::class,\033[0m
- add to \033[97mconfig/config.php\033[0m:
\033[93m  Core\BookStore\ConfigProvider::class,\033[0m
- add to \033[97mconfig/autoload/cli.global.php\033[0m under \033[97mdot_cli\033[0m.\033[97mcommands\033[0m:
\033[93m  Api\BookStore\Command\BookStoreCommand::getDefaultName() => Api\BookStore\Command\BookStoreCommand::class,\033[0m
- add to \033[97mconfig/pipeline.php\033[0m:
\033[93m  \$app->pipe(Api\BookStore\Middleware\BookStoreMiddleware::class);\033[0m
- add to \033[97mconfig/autoload/authorization.global.php\033[0m
  the routes registered in \033[97mvfs://root/src/BookStore/src/RoutesDelegator.php\033[0m
- add to \033[97mcomposer.json\033[0m under \033[97mautoload\033[0m.\033[97mpsr-4\033[0m:
\033[93m  "Api\\\\BookStore\\\\": "src/BookStore/src/"\033[0m
- add to \033[97mcomposer.json\033[0m under \033[97mautoload\033[0m.\033[97mpsr-4\033[0m:
\033[93m  "Core\\\\BookStore\\\\": "src/Core/src/BookStore/src/"\033[0m
- dump Composer autoloader by executing this command:
\033[93m  composer dump\033[0m
- generate Doctrine migration:
\033[93m  php ./vendor/bin/doctrine-migrations diff\033[0m
- \033[91mRun through each new file, verify their content and start adding logic to them.\033[0m

BODY;
        // phpcs:enable Generic.Files.LineLength.TooLong
    }

    private function dataProviderWhenProjectTypeIsApiAndDoesNotUseCore(): string
    {
        // phpcs:disable Generic.Files.LineLength.TooLong
        return <<<BODY

New module name: \033[92mCreated directory: vfs://root/src/BookStore\033[0m

Create entity and repository? [Y(es)/n(o)]: \033[92mCreated Entity: vfs://root/src/BookStore/src/Entity/BookStore.php\033[0m
\033[92mCreated Repository: vfs://root/src/BookStore/src/Repository/BookStoreRepository.php\033[0m

Create service and service interface? [Y(es)/n(o)]: \033[92mCreated Service: vfs://root/src/BookStore/src/Service/BookStoreService.php\033[0m
\033[92mCreated ServiceInterface: vfs://root/src/BookStore/src/Service/BookStoreServiceInterface.php\033[0m

Create command? [Y(es)/n(o)]: \033[92mCreated Command: vfs://root/src/BookStore/src/Command/BookStoreCommand.php\033[0m

Create middleware? [Y(es)/n(o)]: \033[92mCreated Middleware: vfs://root/src/BookStore/src/Middleware/BookStoreMiddleware.php\033[0m

Create handler? [Y(es)/n(o)]: 
Allow listing BookStores? [Y(es)/n(o)]: \033[92mCreated Collection: vfs://root/src/BookStore/src/Collection/BookStoreCollection.php\033[0m
\033[92mCreated Handler: vfs://root/src/BookStore/src/Handler/BookStore/GetBookStoreCollectionHandler.php\033[0m

Allow viewing BookStores? [Y(es)/n(o)]: \033[92mCreated Handler: vfs://root/src/BookStore/src/Handler/BookStore/GetBookStoreResourceHandler.php\033[0m

Allow creating BookStores? [Y(es)/n(o)]: \033[92mCreated Handler: vfs://root/src/BookStore/src/Handler/BookStore/PostBookStoreResourceHandler.php\033[0m
\033[92mCreated InputFilter: vfs://root/src/BookStore/src/InputFilter/CreateBookStoreInputFilter.php\033[0m

Allow deleting BookStores? [Y(es)/n(o)]: \033[92mCreated Handler: vfs://root/src/BookStore/src/Handler/BookStore/DeleteBookStoreResourceHandler.php\033[0m

Allow editing BookStores? [Y(es)/n(o)]: \033[92mCreated Handler: vfs://root/src/BookStore/src/Handler/BookStore/PatchBookStoreResourceHandler.php\033[0m
\033[92mCreated InputFilter: vfs://root/src/BookStore/src/InputFilter/EditBookStoreInputFilter.php\033[0m

Allow replacing BookStores? [Y(es)/n(o)]: \033[92mCreated Handler: vfs://root/src/BookStore/src/Handler/BookStore/PutBookStoreResourceHandler.php\033[0m
\033[92mCreated InputFilter: vfs://root/src/BookStore/src/InputFilter/ReplaceBookStoreInputFilter.php\033[0m

\033[92mCreated RoutesDelegator: vfs://root/src/BookStore/src/RoutesDelegator.php\033[0m

\033[92mCreated OpenAPI: vfs://root/src/BookStore/src/OpenAPI.php\033[0m
\033[92mCreated ConfigProvider: vfs://root/src/BookStore/src/ConfigProvider.php\033[0m

\033[93mNext steps:\033[0m
\033[93m===========\033[0m
- add to \033[97mconfig/config.php\033[0m:
\033[93m  Api\BookStore\ConfigProvider::class,\033[0m
- add to \033[97mconfig/autoload/cli.global.php\033[0m under \033[97mdot_cli\033[0m.\033[97mcommands\033[0m:
\033[93m  Api\BookStore\Command\BookStoreCommand::getDefaultName() => Api\BookStore\Command\BookStoreCommand::class,\033[0m
- add to \033[97mconfig/pipeline.php\033[0m:
\033[93m  \$app->pipe(Api\BookStore\Middleware\BookStoreMiddleware::class);\033[0m
- add to \033[97mconfig/autoload/authorization.global.php\033[0m
  the routes registered in \033[97mvfs://root/src/BookStore/src/RoutesDelegator.php\033[0m
- add to \033[97mcomposer.json\033[0m under \033[97mautoload\033[0m.\033[97mpsr-4\033[0m:
\033[93m  "Api\\\\BookStore\\\\": "src/BookStore/src/"\033[0m
- dump Composer autoloader by executing this command:
\033[93m  composer dump\033[0m
- generate Doctrine migration:
\033[93m  php ./vendor/bin/doctrine-migrations diff\033[0m
- \033[91mRun through each new file, verify their content and start adding logic to them.\033[0m

BODY;
        // phpcs:enable Generic.Files.LineLength.TooLong
    }

    private function dataProviderWhenProjectTypeIsNotApiAndUsesCore(): string
    {
        // phpcs:disable Generic.Files.LineLength.TooLong
        return <<<BODY

New module name: \033[92mCreated directory: vfs://root/src/BookStore\033[0m

Create entity and repository? [Y(es)/n(o)]: \033[92mCreated Entity: vfs://root/src/Core/src/BookStore/src/Entity/BookStore.php\033[0m
\033[92mCreated Repository: vfs://root/src/Core/src/BookStore/src/Repository/BookStoreRepository.php\033[0m

Create service and service interface? [Y(es)/n(o)]: \033[92mCreated Service: vfs://root/src/BookStore/src/Service/BookStoreService.php\033[0m
\033[92mCreated ServiceInterface: vfs://root/src/BookStore/src/Service/BookStoreServiceInterface.php\033[0m

Create command? [Y(es)/n(o)]: \033[92mCreated Command: vfs://root/src/BookStore/src/Command/BookStoreCommand.php\033[0m

Create middleware? [Y(es)/n(o)]: \033[92mCreated Middleware: vfs://root/src/BookStore/src/Middleware/BookStoreMiddleware.php\033[0m

Create handler? [Y(es)/n(o)]: 
Allow listing BookStores? [Y(es)/n(o)]: \033[92mCreated Handler: vfs://root/src/BookStore/src/Handler/BookStore/GetBookStoreListHandler.php\033[0m
\033[92mCreated template file: vfs://root/src/BookStore/templates/book-store/book-store-list.html.twig\033[0m

Allow viewing BookStores? [Y(es)/n(o)]: \033[92mCreated Handler: vfs://root/src/BookStore/src/Handler/BookStore/GetBookStoreViewHandler.php\033[0m
\033[92mCreated template file: vfs://root/src/BookStore/templates/book-store/book-store-view.html.twig\033[0m

Allow creating BookStores? [Y(es)/n(o)]: \033[92mCreated Handler: vfs://root/src/BookStore/src/Handler/BookStore/GetBookStoreCreateFormHandler.php\033[0m
\033[92mCreated Handler: vfs://root/src/BookStore/src/Handler/BookStore/PostBookStoreCreateHandler.php\033[0m
\033[92mCreated Form: vfs://root/src/BookStore/src/Form/CreateBookStoreForm.php\033[0m
\033[92mCreated InputFilter: vfs://root/src/BookStore/src/InputFilter/CreateBookStoreInputFilter.php\033[0m
\033[92mCreated template file: vfs://root/src/BookStore/templates/book-store/book-store-create-form.html.twig\033[0m

Allow deleting BookStores? [Y(es)/n(o)]: \033[92mCreated Handler: vfs://root/src/BookStore/src/Handler/BookStore/GetBookStoreDeleteFormHandler.php\033[0m
\033[92mCreated Handler: vfs://root/src/BookStore/src/Handler/BookStore/PostBookStoreDeleteHandler.php\033[0m
\033[92mCreated Form: vfs://root/src/BookStore/src/Form/DeleteBookStoreForm.php\033[0m
\033[92mCreated InputFilter: vfs://root/src/BookStore/src/InputFilter/DeleteBookStoreInputFilter.php\033[0m
\033[92mCreated Input: vfs://root/src/BookStore/src/InputFilter/Input/ConfirmDeleteBookStoreInput.php\033[0m
\033[92mCreated template file: vfs://root/src/BookStore/templates/book-store/book-store-delete-form.html.twig\033[0m

Allow editing BookStores? [Y(es)/n(o)]: \033[92mCreated Handler: vfs://root/src/BookStore/src/Handler/BookStore/GetBookStoreEditFormHandler.php\033[0m
\033[92mCreated Handler: vfs://root/src/BookStore/src/Handler/BookStore/PostBookStoreEditHandler.php\033[0m
\033[92mCreated Form: vfs://root/src/BookStore/src/Form/EditBookStoreForm.php\033[0m
\033[92mCreated InputFilter: vfs://root/src/BookStore/src/InputFilter/EditBookStoreInputFilter.php\033[0m
\033[92mCreated template file: vfs://root/src/BookStore/templates/book-store/book-store-edit-form.html.twig\033[0m

\033[92mCreated RoutesDelegator: vfs://root/src/BookStore/src/RoutesDelegator.php\033[0m

\033[92mCreated ConfigProvider: vfs://root/src/BookStore/src/ConfigProvider.php\033[0m
\033[92mCreated Core ConfigProvider: vfs://root/src/Core/src/BookStore/src/ConfigProvider.php\033[0m

\033[93mNext steps:\033[0m
\033[93m===========\033[0m
- add to \033[97mconfig/config.php\033[0m:
\033[93m  Admin\BookStore\ConfigProvider::class,\033[0m
- add to \033[97mconfig/config.php\033[0m:
\033[93m  Core\BookStore\ConfigProvider::class,\033[0m
- add to \033[97mconfig/autoload/cli.global.php\033[0m under \033[97mdot_cli\033[0m.\033[97mcommands\033[0m:
\033[93m  Admin\BookStore\Command\BookStoreCommand::getDefaultName() => Admin\BookStore\Command\BookStoreCommand::class,\033[0m
- add to \033[97mconfig/pipeline.php\033[0m:
\033[93m  \$app->pipe(Admin\BookStore\Middleware\BookStoreMiddleware::class);\033[0m
- add to \033[97mconfig/autoload/authorization-guards.global.php\033[0m
  the routes registered in \033[97mvfs://root/src/BookStore/src/RoutesDelegator.php\033[0m
- add to \033[97mcomposer.json\033[0m under \033[97mautoload\033[0m.\033[97mpsr-4\033[0m:
\033[93m  "Admin\\\\BookStore\\\\": "src/BookStore/src/"\033[0m
- add to \033[97mcomposer.json\033[0m under \033[97mautoload\033[0m.\033[97mpsr-4\033[0m:
\033[93m  "Core\\\\BookStore\\\\": "src/Core/src/BookStore/src/"\033[0m
- dump Composer autoloader by executing this command:
\033[93m  composer dump\033[0m
- generate Doctrine migration:
\033[93m  php ./vendor/bin/doctrine-migrations diff\033[0m
- \033[91mRun through each new file, verify their content and start adding logic to them.\033[0m

BODY;
        // phpcs:enable Generic.Files.LineLength.TooLong
    }

    private function dataProviderWhenProjectTypeIsNotApiAndDoesNotUseCore(): string
    {
        // phpcs:disable Generic.Files.LineLength.TooLong
        return <<<BODY

New module name: \033[92mCreated directory: vfs://root/src/BookStore\033[0m

Create entity and repository? [Y(es)/n(o)]: \033[92mCreated Entity: vfs://root/src/BookStore/src/Entity/BookStore.php\033[0m
\033[92mCreated Repository: vfs://root/src/BookStore/src/Repository/BookStoreRepository.php\033[0m

Create service and service interface? [Y(es)/n(o)]: \033[92mCreated Service: vfs://root/src/BookStore/src/Service/BookStoreService.php\033[0m
\033[92mCreated ServiceInterface: vfs://root/src/BookStore/src/Service/BookStoreServiceInterface.php\033[0m

Create command? [Y(es)/n(o)]: \033[92mCreated Command: vfs://root/src/BookStore/src/Command/BookStoreCommand.php\033[0m

Create middleware? [Y(es)/n(o)]: \033[92mCreated Middleware: vfs://root/src/BookStore/src/Middleware/BookStoreMiddleware.php\033[0m

Create handler? [Y(es)/n(o)]: 
Allow listing BookStores? [Y(es)/n(o)]: \033[92mCreated Handler: vfs://root/src/BookStore/src/Handler/BookStore/GetBookStoreListHandler.php\033[0m
\033[92mCreated template file: vfs://root/src/BookStore/templates/book-store/book-store-list.html.twig\033[0m

Allow viewing BookStores? [Y(es)/n(o)]: \033[92mCreated Handler: vfs://root/src/BookStore/src/Handler/BookStore/GetBookStoreViewHandler.php\033[0m
\033[92mCreated template file: vfs://root/src/BookStore/templates/book-store/book-store-view.html.twig\033[0m

Allow creating BookStores? [Y(es)/n(o)]: \033[92mCreated Handler: vfs://root/src/BookStore/src/Handler/BookStore/GetBookStoreCreateFormHandler.php\033[0m
\033[92mCreated Handler: vfs://root/src/BookStore/src/Handler/BookStore/PostBookStoreCreateHandler.php\033[0m
\033[92mCreated Form: vfs://root/src/BookStore/src/Form/CreateBookStoreForm.php\033[0m
\033[92mCreated InputFilter: vfs://root/src/BookStore/src/InputFilter/CreateBookStoreInputFilter.php\033[0m
\033[92mCreated template file: vfs://root/src/BookStore/templates/book-store/book-store-create-form.html.twig\033[0m

Allow deleting BookStores? [Y(es)/n(o)]: \033[92mCreated Handler: vfs://root/src/BookStore/src/Handler/BookStore/GetBookStoreDeleteFormHandler.php\033[0m
\033[92mCreated Handler: vfs://root/src/BookStore/src/Handler/BookStore/PostBookStoreDeleteHandler.php\033[0m
\033[92mCreated Form: vfs://root/src/BookStore/src/Form/DeleteBookStoreForm.php\033[0m
\033[92mCreated InputFilter: vfs://root/src/BookStore/src/InputFilter/DeleteBookStoreInputFilter.php\033[0m
\033[92mCreated Input: vfs://root/src/BookStore/src/InputFilter/Input/ConfirmDeleteBookStoreInput.php\033[0m
\033[92mCreated template file: vfs://root/src/BookStore/templates/book-store/book-store-delete-form.html.twig\033[0m

Allow editing BookStores? [Y(es)/n(o)]: \033[92mCreated Handler: vfs://root/src/BookStore/src/Handler/BookStore/GetBookStoreEditFormHandler.php\033[0m
\033[92mCreated Handler: vfs://root/src/BookStore/src/Handler/BookStore/PostBookStoreEditHandler.php\033[0m
\033[92mCreated Form: vfs://root/src/BookStore/src/Form/EditBookStoreForm.php\033[0m
\033[92mCreated InputFilter: vfs://root/src/BookStore/src/InputFilter/EditBookStoreInputFilter.php\033[0m
\033[92mCreated template file: vfs://root/src/BookStore/templates/book-store/book-store-edit-form.html.twig\033[0m

\033[92mCreated RoutesDelegator: vfs://root/src/BookStore/src/RoutesDelegator.php\033[0m

\033[92mCreated ConfigProvider: vfs://root/src/BookStore/src/ConfigProvider.php\033[0m

\033[93mNext steps:\033[0m
\033[93m===========\033[0m
- add to \033[97mconfig/config.php\033[0m:
\033[93m  Admin\BookStore\ConfigProvider::class,\033[0m
- add to \033[97mconfig/autoload/cli.global.php\033[0m under \033[97mdot_cli\033[0m.\033[97mcommands\033[0m:
\033[93m  Admin\BookStore\Command\BookStoreCommand::getDefaultName() => Admin\BookStore\Command\BookStoreCommand::class,\033[0m
- add to \033[97mconfig/pipeline.php\033[0m:
\033[93m  \$app->pipe(Admin\BookStore\Middleware\BookStoreMiddleware::class);\033[0m
- add to \033[97mconfig/autoload/authorization-guards.global.php\033[0m
  the routes registered in \033[97mvfs://root/src/BookStore/src/RoutesDelegator.php\033[0m
- add to \033[97mcomposer.json\033[0m under \033[97mautoload\033[0m.\033[97mpsr-4\033[0m:
\033[93m  "Admin\\\\BookStore\\\\": "src/BookStore/src/"\033[0m
- dump Composer autoloader by executing this command:
\033[93m  composer dump\033[0m
- generate Doctrine migration:
\033[93m  php ./vendor/bin/doctrine-migrations diff\033[0m
- \033[91mRun through each new file, verify their content and start adding logic to them.\033[0m

BODY;
        // phpcs:enable Generic.Files.LineLength.TooLong
    }
}
