<?php

declare(strict_types=1);

namespace DotTest\Maker\Type;

use Dot\Maker\ColorEnum;
use Dot\Maker\Config;
use Dot\Maker\Context;
use Dot\Maker\FileSystem;
use Dot\Maker\IO\Output;
use Dot\Maker\Type\ConfigProvider;
use Dot\Maker\Type\Module;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

use function fclose;
use function fopen;
use function rewind;
use function stream_get_contents;

use const PHP_EOL;

class ConfigProviderTest extends TestCase
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

    public function testWillCreateFileWhenProjectTypeIsApiAndUsesCore(): void
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

        $file = $this->fileSystem->configProvider();
        $this->assertFileDoesNotExist($file->getPath());
        $this->assertFalse($file->exists());

        $configProvider = new ConfigProvider($this->fileSystem, $this->context, $this->config, $this->module);
        $configProvider->create($this->moduleName);

        rewind($this->outputStream);
        $this->assertFileExists($file->getPath());
        $this->assertTrue($file->exists());
        $this->assertSame(
            ColorEnum::colorize(
                'Created ConfigProvider: vfs://root/src/ModuleName/src/ConfigProvider.php',
                ColorEnum::ForegroundBrightGreen
            ) . PHP_EOL,
            stream_get_contents($this->outputStream)
        );
        $this->assertSame($this->dataProviderWhenProjectTypeIsApiAndUsesCore(), $file->read());
    }

    public function testWillCreateFileWhenProjectTypeIsNotApiAndUsesCore(): void
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

        $file = $this->fileSystem->configProvider();
        $this->assertFileDoesNotExist($file->getPath());
        $this->assertFalse($file->exists());

        $configProvider = new ConfigProvider($this->fileSystem, $this->context, $this->config, $this->module);
        $configProvider->create($this->moduleName);

        rewind($this->outputStream);
        $this->assertFileExists($file->getPath());
        $this->assertTrue($file->exists());
        $this->assertSame(
            ColorEnum::colorize(
                'Created ConfigProvider: vfs://root/src/ModuleName/src/ConfigProvider.php',
                ColorEnum::ForegroundBrightGreen
            ) . PHP_EOL,
            stream_get_contents($this->outputStream)
        );
        $this->assertSame($this->dataProviderWhenProjectTypeIsNotApiAndUsesCore(), $file->read());
    }

    public function testWillCreateFileWhenProjectTypeIsApiAndDoesNotUseCore(): void
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

        $file = $this->fileSystem->configProvider();
        $this->assertFileDoesNotExist($file->getPath());
        $this->assertFalse($file->exists());

        $configProvider = new ConfigProvider($this->fileSystem, $this->context, $this->config, $this->module);
        $configProvider->create($this->moduleName);

        rewind($this->outputStream);
        $this->assertFileExists($file->getPath());
        $this->assertTrue($file->exists());
        $this->assertSame(
            ColorEnum::colorize(
                'Created ConfigProvider: vfs://root/src/ModuleName/src/ConfigProvider.php',
                ColorEnum::ForegroundBrightGreen
            ) . PHP_EOL,
            stream_get_contents($this->outputStream)
        );
        $this->assertSame($this->dataProviderWhenProjectTypeIsApiAndDoesNotUseCore(), $file->read());
    }

    public function testWillCreateFileWhenProjectTypeIsNotApiAndDoesNotUseCore(): void
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

        $file = $this->fileSystem->configProvider();
        $this->assertFileDoesNotExist($file->getPath());
        $this->assertFalse($file->exists());

        $configProvider = new ConfigProvider($this->fileSystem, $this->context, $this->config, $this->module);
        $configProvider->create($this->moduleName);

        rewind($this->outputStream);
        $this->assertFileExists($file->getPath());
        $this->assertTrue($file->exists());
        $this->assertSame(
            ColorEnum::colorize(
                'Created ConfigProvider: vfs://root/src/ModuleName/src/ConfigProvider.php',
                ColorEnum::ForegroundBrightGreen
            ) . PHP_EOL,
            stream_get_contents($this->outputStream)
        );
        $this->assertSame($this->dataProviderWhenProjectTypeIsNotApiAndDoesNotUseCore(), $file->read());
    }

    private function dataProviderWhenProjectTypeIsApiAndUsesCore(): string
    {
        // phpcs:disable Generic.Files.LineLength.TooLong
        return <<<BODY
<?php

declare(strict_types=1);

namespace Api\ModuleName;

use Api\App\ConfigProvider as AppConfigProvider;
use Mezzio\Application;
use Mezzio\Hal\Metadata\MetadataMap;

/**
 * @phpstan-import-type MetadataType from AppConfigProvider
 * @phpstan-type DependenciesType array{
 *     delegators: array<class-string, class-string[]>,
 *     factories: array<class-string, class-string>,
 *     aliases: array<class-string, class-string>,
 * }
 */
class ConfigProvider
{
    /**
     * @return array{
     *     dependencies: DependenciesType,
     *     "Mezzio\Hal\Metadata\MetadataMap": MetadataType[],
     * }
     */
    public function __invoke(): array
    {
        return [
            'dependencies'     => \$this->getDependencies(),
            MetadataMap::class => \$this->getHalConfig(),
        ];
    }

    /**
     * @return DependenciesType
     */
    private function getDependencies(): array
    {
        return [
            'delegators' => [
                Application::class => [RoutesDelegator::class],
            ],
            'factories'  => [
            ],
        ];
    }

    /**
     * @return MetadataType[]
     */
    private function getHalConfig(): array
    {
        return [];
    }
}

BODY;
        // phpcs:enable Generic.Files.LineLength.TooLong
    }

    private function dataProviderWhenProjectTypeIsNotApiAndUsesCore(): string
    {
        // phpcs:disable Generic.Files.LineLength.TooLong
        return <<<BODY
<?php

declare(strict_types=1);

namespace Admin\ModuleName;

use Dot\DependencyInjection\Factory\AttributedServiceFactory;
use Mezzio\Application;

/**
 * @phpstan-type ConfigType array{
 *      dependencies: DependenciesType,
 *      templates: TemplatesType,
 * }
 * @phpstan-type DependenciesType array{
 *      delegators: non-empty-array<class-string, array<class-string>>,
 *      factories: non-empty-array<class-string, class-string>,
 *      aliases: non-empty-array<class-string, class-string>,
 * }
 * @phpstan-type TemplatesType array{
 *      paths: non-empty-array<non-empty-string, non-empty-string[]>,
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
            'templates'    => \$this->getTemplates(),
        ];
    }

    /**
     * @return DependenciesType
     */
    private function getDependencies(): array
    {
        return [
            'delegators' => [
                Application::class => [RoutesDelegator::class],
            ],
            'factories' => [
            ],
        ];
    }

    /**
     * @return TemplatesType
     */
    private function getTemplates(): array
    {
        return [
            'paths' => [
                'module-name' => [__DIR__ . '/../templates/module-name'],
            ],
        ];
    }
}

BODY;
        // phpcs:enable Generic.Files.LineLength.TooLong
    }

    private function dataProviderWhenProjectTypeIsApiAndDoesNotUseCore(): string
    {
        // phpcs:disable Generic.Files.LineLength.TooLong
        return <<<BODY
<?php

declare(strict_types=1);

namespace Api\ModuleName;

use Api\App\ConfigProvider as AppConfigProvider;
use Mezzio\Application;
use Mezzio\Hal\Metadata\MetadataMap;

/**
 * @phpstan-import-type MetadataType from AppConfigProvider
 * @phpstan-type DependenciesType array{
 *     delegators: array<class-string, class-string[]>,
 *     factories: array<class-string, class-string>,
 *     aliases: array<class-string, class-string>,
 * }
 */
class ConfigProvider
{
    /**
     * @return array{
     *     dependencies: DependenciesType,
     *     "Mezzio\Hal\Metadata\MetadataMap": MetadataType[],
     * }
     */
    public function __invoke(): array
    {
        return [
            'dependencies'     => \$this->getDependencies(),
            MetadataMap::class => \$this->getHalConfig(),
        ];
    }

    /**
     * @return DependenciesType
     */
    private function getDependencies(): array
    {
        return [
            'delegators' => [
                Application::class => [RoutesDelegator::class],
            ],
            'factories'  => [
            ],
        ];
    }

    /**
     * @return MetadataType[]
     */
    private function getHalConfig(): array
    {
        return [];
    }
}

BODY;
        // phpcs:enable Generic.Files.LineLength.TooLong
    }

    private function dataProviderWhenProjectTypeIsNotApiAndDoesNotUseCore(): string
    {
        // phpcs:disable Generic.Files.LineLength.TooLong
        return <<<BODY
<?php

declare(strict_types=1);

namespace Admin\ModuleName;

use Dot\DependencyInjection\Factory\AttributedServiceFactory;
use Mezzio\Application;

/**
 * @phpstan-type ConfigType array{
 *      dependencies: DependenciesType,
 *      templates: TemplatesType,
 * }
 * @phpstan-type DependenciesType array{
 *      delegators: non-empty-array<class-string, array<class-string>>,
 *      factories: non-empty-array<class-string, class-string>,
 *      aliases: non-empty-array<class-string, class-string>,
 * }
 * @phpstan-type TemplatesType array{
 *      paths: non-empty-array<non-empty-string, non-empty-string[]>,
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
            'templates'    => \$this->getTemplates(),
        ];
    }

    /**
     * @return DependenciesType
     */
    private function getDependencies(): array
    {
        return [
            'delegators' => [
                Application::class => [RoutesDelegator::class],
            ],
            'factories' => [
            ],
        ];
    }

    /**
     * @return TemplatesType
     */
    private function getTemplates(): array
    {
        return [
            'paths' => [
                'module-name' => [__DIR__ . '/../templates/module-name'],
            ],
        ];
    }
}

BODY;
        // phpcs:enable Generic.Files.LineLength.TooLong
    }
}
