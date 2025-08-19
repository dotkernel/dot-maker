<?php

declare(strict_types=1);

namespace DotTest\Maker\Type;

use Dot\Maker\ColorEnum;
use Dot\Maker\Config;
use Dot\Maker\Context;
use Dot\Maker\FileSystem;
use Dot\Maker\IO\Output;
use Dot\Maker\Type\ConfigProvider;
use Dot\Maker\Type\CoreConfigProvider;
use Dot\Maker\Type\Module;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

use function fclose;
use function fopen;
use function rewind;
use function stream_get_contents;

use const PHP_EOL;

class CoreConfigProviderTest extends TestCase
{
    private Config $config;
    private Context $context;
    private FileSystem $fileSystem;
    private Module $module;
    private string $moduleName = 'ModuleName';

    /** @var resource $outputStream */
    private $outputStream;

    protected function setUp(): void
    {
        $this->outputStream = fopen('php://memory', 'w+');
        Output::setOutputStream($this->outputStream);
    }

    protected function tearDown(): void
    {
        fclose($this->outputStream);
    }

    public function testWillThrowErrorAndNotCreateFileWhenProjectTypeIsApiAndCoreIsNotUsed(): void
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

        $this->expectExceptionMessage(
            'Core ConfigProvider cannot be created in projects that do not use the Core architecture'
        );

        $coreConfigProvider = new CoreConfigProvider($this->fileSystem, $this->context, $this->config, $this->module);
        $coreConfigProvider->create($this->moduleName);
    }

    public function testWillThrowErrorAndNotCreateFileWhenProjectTypeIsNotApiAndCoreIsNotUsed(): void
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
        $this->module     = new Module($this->fileSystem, $this->context, $this->config);

        $this->expectExceptionMessage(
            'Core ConfigProvider cannot be created in projects that do not use the Core architecture'
        );

        $coreConfigProvider = new CoreConfigProvider($this->fileSystem, $this->context, $this->config, $this->module);
        $coreConfigProvider->create($this->moduleName);
    }

    public function testWillCreateFileWhenProjectTypeIsApi(): void
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

        $file = $this->fileSystem->coreConfigProvider();
        $this->assertFileDoesNotExist($file->getPath());
        $this->assertFalse($file->exists());

        $coreConfigProvider = new CoreConfigProvider($this->fileSystem, $this->context, $this->config, $this->module);
        $coreConfigProvider->create($this->moduleName);

        rewind($this->outputStream);
        $this->assertFileExists($file->getPath());
        $this->assertTrue($file->exists());
        $this->assertSame(
            ColorEnum::colorize(
                'Created Core ConfigProvider: vfs://root/src/Core/src/ModuleName/src/ConfigProvider.php',
                ColorEnum::ForegroundBrightGreen
            ) . PHP_EOL,
            stream_get_contents($this->outputStream)
        );
        $this->assertSame($this->dataProviderWhenProjectTypeIsApi(), $file->read());
    }

    public function testWillCreateFileWhenProjectTypeIsNotApi(): void
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
        $this->module     = new Module($this->fileSystem, $this->context, $this->config);

        $file = $this->fileSystem->coreConfigProvider();
        $this->assertFileDoesNotExist($file->getPath());
        $this->assertFalse($file->exists());

        $coreConfigProvider = new CoreConfigProvider($this->fileSystem, $this->context, $this->config, $this->module);
        $coreConfigProvider->create($this->moduleName);

        rewind($this->outputStream);
        $this->assertFileExists($file->getPath());
        $this->assertTrue($file->exists());
        $this->assertSame(
            ColorEnum::colorize(
                'Created Core ConfigProvider: vfs://root/src/Core/src/ModuleName/src/ConfigProvider.php',
                ColorEnum::ForegroundBrightGreen
            ) . PHP_EOL,
            stream_get_contents($this->outputStream)
        );
        $this->assertSame($this->dataProviderWhenProjectTypeIsNotApi(), $file->read());
    }

    private function dataProviderWhenProjectTypeIsApi(): string
    {
        // phpcs:disable Generic.Files.LineLength.TooLong
        return <<<BODY
<?php

declare(strict_types=1);

namespace Core\ModuleName;

use Core\ModuleName\Repository\ModuleNameRepository;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Dot\DependencyInjection\Factory\AttributedRepositoryFactory;

/**
 * @phpstan-type ConfigType array{
 *      dependencies: DependenciesType,
 *      doctrine: DoctrineConfigType,
 * }
 * @phpstan-type DoctrineConfigType array{
 *      driver: array{
 *          orm_default: array{
 *              drivers: array<non-empty-string, non-empty-string>,
 *          },
 *          ModuleNameEntities: array{
 *              class: class-string<MappingDriver>,
 *              cache: non-empty-string,
 *              paths: non-empty-string[],
 *          },
 *      },
 * }
 * @phpstan-type DependenciesType array{
 *       factories: array<class-string, class-string>,
 * }
 */
class ConfigProvider
{
    /**
     * @return ConfigType
     */
    public function __invoke(): array
    {
        return [
            'dependencies' => \$this->getDependencies(),
            'doctrine'     => \$this->getDoctrineConfig(),
        ];
    }

    /**
     * @return DependenciesType
     */
    private function getDependencies(): array
    {
        return [
            'factories' => [
                ModuleNameRepository::class => AttributedRepositoryFactory::class,
            ],
        ];
    }

    /**
     * @return DoctrineConfigType
     */
    private function getDoctrineConfig(): array
    {
        return [
            'driver' => [
                'orm_default' => [
                    'drivers' => [
                        'Core\ModuleName\Entity' => 'ModuleNameEntities',
                    ],
                ],
                'ModuleNameEntities' => [
                    'class' => AttributeDriver::class,
                    'cache' => 'array',
                    'paths' => [__DIR__ . '/Entity'],
                ],
            ],
        ];
    }
}

BODY;
        // phpcs:enable Generic.Files.LineLength.TooLong
    }

    private function dataProviderWhenProjectTypeIsNotApi(): string
    {
        // phpcs:disable Generic.Files.LineLength.TooLong
        return <<<BODY
<?php

declare(strict_types=1);

namespace Core\ModuleName;

use Core\ModuleName\Repository\ModuleNameRepository;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Dot\DependencyInjection\Factory\AttributedRepositoryFactory;

/**
 * @phpstan-type ConfigType array{
 *      dependencies: DependenciesType,
 *      doctrine: DoctrineConfigType,
 * }
 * @phpstan-type DoctrineConfigType array{
 *      driver: array{
 *          orm_default: array{
 *              drivers: array<non-empty-string, non-empty-string>,
 *          },
 *          ModuleNameEntities: array{
 *              class: class-string<MappingDriver>,
 *              cache: non-empty-string,
 *              paths: non-empty-string[],
 *          },
 *      },
 * }
 * @phpstan-type DependenciesType array{
 *       factories: array<class-string, class-string>,
 * }
 */
class ConfigProvider
{
    /**
     * @return ConfigType
     */
    public function __invoke(): array
    {
        return [
            'dependencies' => \$this->getDependencies(),
            'doctrine'     => \$this->getDoctrineConfig(),
        ];
    }

    /**
     * @return DependenciesType
     */
    private function getDependencies(): array
    {
        return [
            'factories' => [
                ModuleNameRepository::class => AttributedRepositoryFactory::class,
            ],
        ];
    }

    /**
     * @return DoctrineConfigType
     */
    private function getDoctrineConfig(): array
    {
        return [
            'driver' => [
                'orm_default' => [
                    'drivers' => [
                        'Core\ModuleName\Entity' => 'ModuleNameEntities',
                    ],
                ],
                'ModuleNameEntities' => [
                    'class' => AttributeDriver::class,
                    'cache' => 'array',
                    'paths' => [__DIR__ . '/Entity'],
                ],
            ],
        ];
    }
}

BODY;
        // phpcs:enable Generic.Files.LineLength.TooLong
    }
}
