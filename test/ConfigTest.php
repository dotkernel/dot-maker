<?php

declare(strict_types=1);

namespace DotTest\Maker\Unit;

use Dot\Maker\Config;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

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
            sprintf('%s/%s: key "Maker::class" not found', $fileSystem->url(), Config::CONFIG_FILE)
        );
        $config = new Config($fileSystem->url());
        $this->assertContainsOnlyInstancesOf(Config::class, [$config]);
    }
}
