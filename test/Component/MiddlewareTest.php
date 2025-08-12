<?php

declare(strict_types=1);

namespace Component;

use Dot\Maker\Component\Import;
use Dot\Maker\Config;
use Dot\Maker\Context;
use Dot\Maker\FileSystem;
use Dot\Maker\FileSystem\File;
use Dot\Maker\IO\Input;
use Dot\Maker\IO\Output;
use Dot\Maker\Type\Middleware;
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

class MiddlewareTest extends TestCase
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
        $file = $this->fileSystem->middleware($this->resourceName);
        $this->assertFileDoesNotExist($file->getPath());
        $this->assertFalse($file->exists());

        $this->expectExceptionMessage('Invalid Middleware name: "."');
        $entity = new Middleware($this->fileSystem, $this->context, $this->config, $this->module);
        $entity->create('.');

        rewind($this->errorStream);
        $this->assertEmpty(stream_get_contents($this->errorStream));
        $this->assertFalse($file->exists());
        $this->assertFileDoesNotExist($file->getPath());
    }

    public function testCallToCreateWillFailWhenAlreadyExists(): void
    {
        $file = $this->fileSystem->middleware($this->resourceName);
        $this->assertFileDoesNotExist($file->getPath());
        $this->assertFalse($file->exists());
        $file->create('...');
        $this->assertFileExists($file->getPath());
        $this->assertTrue($file->exists());

        $this->expectExceptionMessage(
            sprintf('Class "BookStoreMiddleware" already exists at %s', $file->getPath())
        );
        $entity = new Middleware($this->fileSystem, $this->context, $this->config, $this->module);
        $entity->create($this->resourceName);

        rewind($this->errorStream);
        $this->assertEmpty(stream_get_contents($this->errorStream));
    }

    public function testCallToInvokeWillNotCreateFileOnEmptyInput(): void
    {
        $file = $this->fileSystem->middleware($this->resourceName);
        $this->assertFileDoesNotExist($file->getPath());
        $this->assertFalse($file->exists());

        fwrite($this->inputStream, PHP_EOL);
        rewind($this->inputStream);

        $entity = new Middleware($this->fileSystem, $this->context, $this->config, $this->module);
        $entity();

        rewind($this->errorStream);
        $this->assertEmpty(stream_get_contents($this->errorStream));
        $this->assertFalse($file->exists());
        $this->assertFileDoesNotExist($file->getPath());
    }

    public function testCallToInvokeWillOutputErrorAndWillNotCreateFileWhenNameIsInvalid(): void
    {
        $file = $this->fileSystem->middleware($this->resourceName);
        $this->assertFalse($file->exists());
        $this->assertFileDoesNotExist($file->getPath());

        fwrite($this->inputStream, '.' . PHP_EOL);
        rewind($this->inputStream);

        $entity = new Middleware($this->fileSystem, $this->context, $this->config, $this->module);
        $entity();

        rewind($this->errorStream);
        $this->assertStringContainsString('Invalid Middleware name: "."', stream_get_contents($this->errorStream));
        $this->assertFalse($file->exists());
        $this->assertFileDoesNotExist($file->getPath());
    }

    public function testCallToInvokeWillSucceedWhenNameIsValidAndServiceDoesNotExist(): void
    {
        $file = $this->fileSystem->middleware($this->resourceName);
        $this->assertFileDoesNotExist($file->getPath());
        $this->assertFalse($file->exists());

        fwrite($this->inputStream, $this->resourceName . PHP_EOL);
        rewind($this->inputStream);

        $entity = new Middleware($this->fileSystem, $this->context, $this->config, $this->module);
        $entity();

        $this->assertFileExists($file->getPath());
        $this->assertTrue($file->exists());

        $this->assertSame($this->dataProviderWithoutService(), $file->read());
    }

    public function testCallToCreateWillSucceedWhenNameIsValidAndServiceDoesNotExist(): void
    {
        $file = $this->fileSystem->middleware($this->resourceName);
        $this->assertFileDoesNotExist($file->getPath());
        $this->assertFalse($file->exists());

        $entity = new Middleware($this->fileSystem, $this->context, $this->config, $this->module);
        $type   = $entity->create($this->resourceName);

        $this->assertFileExists($file->getPath());
        $this->assertTrue($file->exists());
        $this->assertSame($type->getPath(), $file->getPath());

        rewind($this->errorStream);
        $this->assertEmpty(stream_get_contents($this->errorStream));

        rewind($this->outputStream);
        $this->assertStringContainsString(
            sprintf('Created Middleware: %s', $type->getPath()),
            stream_get_contents($this->outputStream)
        );

        $this->assertSame($this->dataProviderWithoutService(), $file->read());
    }

    public function testCallToInvokeWillSucceedWhenNameIsValidAndServiceDoesExist(): void
    {
        $file = $this->fileSystem->middleware($this->resourceName);
        $this->assertFileDoesNotExist($file->getPath());
        $this->assertFalse($file->exists());

        $serviceInterface = $this->fileSystem->serviceInterface($this->moduleName);
        $serviceInterface->create('...');
        $this->assertFileExists($serviceInterface->getPath());
        $this->assertTrue($serviceInterface->exists());

        fwrite($this->inputStream, $this->resourceName . PHP_EOL);
        rewind($this->inputStream);

        $entity = new Middleware($this->fileSystem, $this->context, $this->config, $this->module);
        $entity();

        $this->assertFileExists($file->getPath());
        $this->assertTrue($file->exists());

        $this->assertSame($this->dataProviderWithService($serviceInterface), $file->read());
    }

    public function testCallToCreateWillSucceedWhenNameIsValidAndServiceDoesExist(): void
    {
        $file = $this->fileSystem->middleware($this->resourceName);
        $this->assertFileDoesNotExist($file->getPath());
        $this->assertFalse($file->exists());

        $serviceInterface = $this->fileSystem->serviceInterface($this->moduleName);
        $serviceInterface->create('...');
        $this->assertFileExists($serviceInterface->getPath());
        $this->assertTrue($serviceInterface->exists());

        $entity = new Middleware($this->fileSystem, $this->context, $this->config, $this->module);
        $type   = $entity->create($this->resourceName);

        $this->assertFileExists($file->getPath());
        $this->assertTrue($file->exists());
        $this->assertSame($type->getPath(), $file->getPath());

        rewind($this->errorStream);
        $this->assertEmpty(stream_get_contents($this->errorStream));

        rewind($this->outputStream);
        $this->assertStringContainsString(
            sprintf('Created Middleware: %s', $type->getPath()),
            stream_get_contents($this->outputStream)
        );

        $this->assertSame($this->dataProviderWithService($serviceInterface), $file->read());
    }

    private function dataProviderWithService(File $serviceInterface): string
    {
        $uses = [
            sprintf('use %s;', $serviceInterface->getComponent()->getFqcn()),
            sprintf('use %s;', Import::DOT_DEPENDENCYINJECTION_ATTRIBUTE_INJECT),
            sprintf('use %s;', Import::PSR_HTTP_MESSAGE_RESPONSEINTERFACE),
            sprintf('use %s;', Import::PSR_HTTP_MESSAGE_SERVERREQUESTINTERFACE),
            sprintf('use %s;', Import::PSR_HTTP_SERVER_MIDDLEWAREINTERFACE),
            sprintf('use %s;', Import::PSR_HTTP_SERVER_REQUESTHANDLERINTERFACE),
        ];
        $uses = implode(PHP_EOL, $uses);

        return <<<BODY
<?php

declare(strict_types=1);

namespace Api\ModuleName\Middleware;

{$uses}

class BookStoreMiddleware implements MiddlewareInterface
{
    #[Inject(
        ModuleNameServiceInterface::class,
    )]
    public function __construct(
        protected ModuleNameServiceInterface \$moduleNameService,
    ) {
    }

    public function process(
        ServerRequestInterface \$request,
        RequestHandlerInterface \$handler,
    ): ResponseInterface {
        // add logic here

        return \$handler->handle(\$request);
    }
}

BODY;
    }

    private function dataProviderWithoutService(): string
    {
        $uses = [
            sprintf('use %s;', Import::PSR_HTTP_MESSAGE_RESPONSEINTERFACE),
            sprintf('use %s;', Import::PSR_HTTP_MESSAGE_SERVERREQUESTINTERFACE),
            sprintf('use %s;', Import::PSR_HTTP_SERVER_MIDDLEWAREINTERFACE),
            sprintf('use %s;', Import::PSR_HTTP_SERVER_REQUESTHANDLERINTERFACE),
        ];
        $uses = implode(PHP_EOL, $uses);

        return <<<BODY
<?php

declare(strict_types=1);

namespace Api\ModuleName\Middleware;

{$uses}

class BookStoreMiddleware implements MiddlewareInterface
{
    public function process(
        ServerRequestInterface \$request,
        RequestHandlerInterface \$handler,
    ): ResponseInterface {
        // add logic here

        return \$handler->handle(\$request);
    }
}

BODY;
    }
}
