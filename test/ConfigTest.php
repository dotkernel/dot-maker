<?php

declare(strict_types=1);

namespace DotTest\Maker;

use Dot\Maker\Config;
use Dot\Maker\Maker;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

use function sprintf;

class ConfigTest extends TestCase
{
    public function testWillInstantiateWithoutConfigFile(): void
    {
        $config = new Config('/invalid/path');
        $this->assertContainsOnlyInstancesOf(Config::class, [$config]);
    }

    public function testConfigFileWithoutMakerKeyWillThrowError(): void
    {
        $fileSystem = vfsStream::setup('root', 0644, [
            'config' => [
                'autoload' => [
                    'maker.local.php' => <<<CFG
<?php

declare(strict_types=1);

return [];
CFG,
                ],
            ],
        ]);

        $this->expectExceptionMessage(
            sprintf('%s/%s: key "%s" not found', $fileSystem->url(), Config::CONFIG_FILE, Maker::class)
        );
        $config = new Config($fileSystem->url());
        $this->assertContainsOnlyInstancesOf(Config::class, [$config]);
    }

    public function testConfigFileReturningNonArrayWillThrowError(): void
    {
        $fileSystem = vfsStream::setup('root', 0644, [
            'config' => [
                'autoload' => [
                    'maker.local.php' => '<?php return 1;',
                ],
            ],
        ]);

        $this->expectExceptionMessage(
            sprintf('%s/%s: key "%s" not found', $fileSystem->url(), Config::CONFIG_FILE, Maker::class)
        );
        new Config($fileSystem->url());
    }
}
