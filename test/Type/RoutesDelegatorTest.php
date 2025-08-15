<?php

declare(strict_types=1);

namespace DotTest\Maker\Type;

use Dot\Maker\ColorEnum;
use Dot\Maker\Config;
use Dot\Maker\Context;
use Dot\Maker\FileSystem;
use Dot\Maker\IO\Output;
use Dot\Maker\Type\RoutesDelegator;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

use function fclose;
use function fopen;
use function rewind;
use function sprintf;
use function stream_get_contents;

use const PHP_EOL;

class RoutesDelegatorTest extends TestCase
{
    private string $moduleName   = 'ModuleName';
    private string $resourceName = 'BookStore';

    /**
     * @dataProvider dataProvider
     */
    public function testCallToCreateWhenProjectTypeIsNotApiWillCreateFile(string $expected): void
    {
        $stream = fopen('php://memory', 'w+');
        Output::setOutputStream($stream);

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

        $config     = new Config($root->url());
        $context    = new Context($root->url());
        $fileSystem = (new FileSystem($context))->setModuleName($this->moduleName);

        $fileSystem->getCreateResourceHandler($this->resourceName)->create('...');
        $fileSystem->postCreateResourceHandler($this->resourceName)->create('...');
        $fileSystem->getDeleteResourceHandler($this->resourceName)->create('...');
        $fileSystem->postDeleteResourceHandler($this->resourceName)->create('...');
        $fileSystem->getEditResourceHandler($this->resourceName)->create('...');
        $fileSystem->postEditResourceHandler($this->resourceName)->create('...');
        $fileSystem->getListResourcesHandler($this->resourceName)->create('...');
        $fileSystem->getViewResourceHandler($this->resourceName)->create('...');

        $routesDelegator = new RoutesDelegator($fileSystem, $context, $config);
        $routesDelegator = $routesDelegator->create($this->resourceName);

        rewind($stream);
        $this->assertSame(
            ColorEnum::colorize(
                sprintf('Created RoutesDelegator: %s', $routesDelegator->getPath()),
                ColorEnum::ForegroundBrightGreen
            ) . PHP_EOL,
            stream_get_contents($stream)
        );
        $this->assertSame($expected, $routesDelegator->read());
        fclose($stream);
    }

    /**
     * @dataProvider dataProviderApi
     */
    public function testCallToCreateWhenProjectTypeIsApiWillCreateFile(string $expected): void
    {
        $stream = fopen('php://memory', 'w+');
        Output::setOutputStream($stream);

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

        $config     = new Config($root->url());
        $context    = new Context($root->url());
        $fileSystem = (new FileSystem($context))->setModuleName($this->moduleName);

        $fileSystem->entity($this->resourceName)->create('...');
        $fileSystem->apiDeleteResourceHandler($this->resourceName)->create('...');
        $fileSystem->apiGetResourceHandler($this->resourceName)->create('...');
        $fileSystem->apiGetCollectionHandler($this->resourceName)->create('...');
        $fileSystem->apiPatchResourceHandler($this->resourceName)->create('...');
        $fileSystem->apiPostResourceHandler($this->resourceName)->create('...');
        $fileSystem->apiPutResourceHandler($this->resourceName)->create('...');

        $routesDelegator = new RoutesDelegator($fileSystem, $context, $config);
        $routesDelegator = $routesDelegator->create($this->resourceName);

        rewind($stream);
        $this->assertSame(
            ColorEnum::colorize(
                sprintf('Created RoutesDelegator: %s', $routesDelegator->getPath()),
                ColorEnum::ForegroundBrightGreen
            ) . PHP_EOL,
            stream_get_contents($stream)
        );
        $this->assertSame($expected, $routesDelegator->read());
        fclose($stream);
    }

    public static function dataProvider(): array
    {
        // phpcs:disable Generic.Files.LineLength.TooLong
        $expected = <<<BODY
<?php

declare(strict_types=1);

namespace Admin\ModuleName;

use Admin\ModuleName\Handler\BookStore\GetBookStoreCreateFormHandler;
use Admin\ModuleName\Handler\BookStore\GetBookStoreDeleteFormHandler;
use Admin\ModuleName\Handler\BookStore\GetBookStoreEditFormHandler;
use Admin\ModuleName\Handler\BookStore\GetBookStoreListHandler;
use Admin\ModuleName\Handler\BookStore\GetBookStoreViewHandler;
use Admin\ModuleName\Handler\BookStore\PostBookStoreCreateHandler;
use Admin\ModuleName\Handler\BookStore\PostBookStoreDeleteHandler;
use Admin\ModuleName\Handler\BookStore\PostBookStoreEditHandler;
use Core\App\ConfigProvider;
use Dot\Router\RouteCollectorInterface;
use Mezzio\Application;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class RoutesDelegator
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(
        ContainerInterface \$container,
        string \$serviceName,
        callable \$callback,
    ): Application {
        \$uuid = ConfigProvider::REGEXP_UUID;

        /** @var RouteCollectorInterface \$routeCollector */
        \$routeCollector = \$container->get(RouteCollectorInterface::class);

        \$routeCollector
            ->get('/create-book-store', GetBookStoreCreateFormHandler::class, 'book-store::create-book-store-form')
            ->post('/create-book-store', PostBookStoreCreateHandler::class, 'book-store::create-book-store')
            ->get('/delete-book-store/' . \$uuid, GetBookStoreDeleteFormHandler::class, 'book-store::delete-book-store-form')
            ->post('/delete-book-store/' . \$uuid, PostBookStoreDeleteHandler::class, 'book-store::delete-book-store')
            ->get('/edit-book-store/' . \$uuid, GetBookStoreEditFormHandler::class, 'book-store::edit-book-store-form')
            ->post('/edit-book-store/' . \$uuid, PostBookStoreEditHandler::class, 'book-store::edit-book-store')
            ->get('/list-book-store', GetBookStoreListHandler::class, 'book-store::list-book-store')
            ->get('/view-book-store/' . \$uuid, GetBookStoreViewHandler::class, 'book-store::view-book-store-form');

        return \$callback();
    }
}

BODY;
        // phpcs:enable Generic.Files.LineLength.TooLong

        return [
            [$expected],
        ];
    }

    public static function dataProviderApi(): array
    {
        // phpcs:disable Generic.Files.LineLength.TooLong
        $expected = <<<BODY
<?php

declare(strict_types=1);

namespace Api\ModuleName;

use Api\ModuleName\Handler\BookStore\DeleteBookStoreResourceHandler;
use Api\ModuleName\Handler\BookStore\GetBookStoreCollectionHandler;
use Api\ModuleName\Handler\BookStore\GetBookStoreResourceHandler;
use Api\ModuleName\Handler\BookStore\PatchBookStoreResourceHandler;
use Api\ModuleName\Handler\BookStore\PostBookStoreResourceHandler;
use Api\ModuleName\Handler\BookStore\PutBookStoreResourceHandler;
use Core\App\ConfigProvider;
use Dot\Router\RouteCollectorInterface;
use Mezzio\Application;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class RoutesDelegator
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(
        ContainerInterface \$container,
        string \$serviceName,
        callable \$callback,
    ): Application {
        \$uuid = ConfigProvider::REGEXP_UUID;

        /** @var RouteCollectorInterface \$routeCollector */
        \$routeCollector = \$container->get(RouteCollectorInterface::class);

        \$routeCollector
            ->delete('/book-store/' . \$uuid, DeleteBookStoreResourceHandler::class, 'book-store::delete-book-store')
            ->get('/book-store/' . \$uuid, GetBookStoreResourceHandler::class, 'book-store::view-book-store')
            ->get('/book-store', GetBookStoreCollectionHandler::class, 'book-store::list-book-store')
            ->patch('/book-store/' . \$uuid, PatchBookStoreResourceHandler::class, 'book-store::update-book-store')
            ->post('/book-store', PostBookStoreResourceHandler::class, 'book-store::create-book-store')
            ->put('/book-store/' . \$uuid, PutBookStoreResourceHandler::class, 'book-store::replace-book-store');

        return \$callback();
    }
}

BODY;
        // phpcs:enable Generic.Files.LineLength.TooLong

        return [
            [$expected],
        ];
    }
}
