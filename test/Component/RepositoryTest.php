<?php

declare(strict_types=1);

namespace Component;

use Dot\Maker\Config;
use Dot\Maker\Context;
use Dot\Maker\FileSystem;
use Dot\Maker\IO\Input;
use Dot\Maker\IO\Output;
use Dot\Maker\Type\Module;
use Dot\Maker\Type\Repository;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

use function fclose;
use function fopen;
use function fwrite;
use function rewind;
use function sprintf;
use function stream_get_contents;

use const PHP_EOL;

class RepositoryTest extends TestCase
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
        $file = $this->fileSystem->repository($this->resourceName);
        $this->assertFileDoesNotExist($file->getPath());
        $this->assertFalse($file->exists());

        $this->expectExceptionMessage('Invalid Repository name: "."');
        $repository = new Repository($this->fileSystem, $this->context, $this->config, $this->module);
        $repository->create('.');

        rewind($this->errorStream);
        $this->assertEmpty(stream_get_contents($this->errorStream));
        $this->assertFalse($file->exists());
        $this->assertFileDoesNotExist($file->getPath());
    }

    public function testCallToCreateWillFailWhenAlreadyExists(): void
    {
        $file = $this->fileSystem->repository($this->resourceName);
        $this->assertFileDoesNotExist($file->getPath());
        $this->assertFalse($file->exists());
        $file->create('...');
        $this->assertFileExists($file->getPath());
        $this->assertTrue($file->exists());

        $this->expectExceptionMessage(
            sprintf('Class "BookStoreRepository" already exists at %s', $file->getPath())
        );
        $repository = new Repository($this->fileSystem, $this->context, $this->config, $this->module);
        $repository->create($this->resourceName);

        rewind($this->errorStream);
        $this->assertEmpty(stream_get_contents($this->errorStream));
    }

    public function testCallToInvokeWillNotCreateFileOnEmptyInput(): void
    {
        $file = $this->fileSystem->repository($this->resourceName);
        $this->assertFileDoesNotExist($file->getPath());
        $this->assertFalse($file->exists());

        fwrite($this->inputStream, PHP_EOL);
        rewind($this->inputStream);

        $repository = new Repository($this->fileSystem, $this->context, $this->config, $this->module);
        $repository();

        rewind($this->errorStream);
        $this->assertEmpty(stream_get_contents($this->errorStream));
        $this->assertFalse($file->exists());
        $this->assertFileDoesNotExist($file->getPath());
    }

    public function testCallToInvokeWillOutputErrorAndWillNotCreateFileWhenNameIsInvalid(): void
    {
        $file = $this->fileSystem->repository($this->resourceName);
        $this->assertFalse($file->exists());
        $this->assertFileDoesNotExist($file->getPath());

        fwrite($this->inputStream, '.' . PHP_EOL);
        rewind($this->inputStream);

        $repository = new Repository($this->fileSystem, $this->context, $this->config, $this->module);
        $repository();

        rewind($this->errorStream);
        $this->assertStringContainsString('Invalid Repository name: "."', stream_get_contents($this->errorStream));
        $this->assertFalse($file->exists());
        $this->assertFileDoesNotExist($file->getPath());
    }

    /**
     * @dataProvider dataProvider
     */
    public function testCallToInvokeWillSucceedWhenNameIsValid(string $expected): void
    {
        $file = $this->fileSystem->repository($this->resourceName);
        $this->assertFileDoesNotExist($file->getPath());
        $this->assertFalse($file->exists());

        fwrite($this->inputStream, $this->resourceName . PHP_EOL);
        rewind($this->inputStream);

        $repository = new Repository($this->fileSystem, $this->context, $this->config, $this->module);
        $repository();

        $this->assertFileExists($file->getPath());
        $this->assertTrue($file->exists());

        $this->assertSame($expected, $file->read());
    }

    /**
     * @dataProvider dataProvider
     */
    public function testCallToCreateWillSucceedWhenNameIsValid(string $expected): void
    {
        $file = $this->fileSystem->repository($this->resourceName);
        $this->assertFileDoesNotExist($file->getPath());
        $this->assertFalse($file->exists());

        $repository = new Repository($this->fileSystem, $this->context, $this->config, $this->module);
        $type       = $repository->create($this->resourceName);

        $this->assertFileExists($file->getPath());
        $this->assertTrue($file->exists());
        $this->assertSame($type->getPath(), $file->getPath());

        rewind($this->errorStream);
        $this->assertEmpty(stream_get_contents($this->errorStream));

        rewind($this->outputStream);
        $this->assertStringContainsString(
            sprintf('Created Repository: %s', $type->getPath()),
            stream_get_contents($this->outputStream)
        );

        $this->assertSame($expected, $type->read());
    }

    public static function dataProvider(): array
    {
        $repository = <<<BODY
<?php

declare(strict_types=1);

namespace Core\ModuleName\Repository;

use Core\App\Repository\AbstractRepository;
use Core\ModuleName\Entity\BookStore;
use Doctrine\ORM\QueryBuilder;
use Dot\DependencyInjection\Attribute\Entity;

#[Entity(name: BookStore::class)]
class BookStoreRepository extends AbstractRepository
{
    /**
     * @param array<non-empty-string, mixed> \$params
     * @param array<non-empty-string, mixed> \$filters
     */
    public function getBookStores(
        array \$params = [],
        array \$filters = [],
    ): QueryBuilder {
        \$queryBuilder = \$this
            ->getQueryBuilder()
            ->select(['bookStore'])
            ->from(BookStore::class, 'bookStore');

        // add filters

        \$queryBuilder
            ->orderBy(\$params['sort'], \$params['dir'])
            ->setFirstResult(\$params['offset'])
            ->setMaxResults(\$params['limit'])
            ->groupBy('bookStore.uuid');
        \$queryBuilder->getQuery()->useQueryCache(true);

        return \$queryBuilder;
    }
}

BODY;

        return [
            [$repository],
        ];
    }
}
