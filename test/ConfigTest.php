<?php

declare(strict_types=1);

namespace Dot\test;

use Dot\Maker\Config;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    private string $configPath;

    protected function setup(): void
    {
        $fileSystem = vfsStream::setup('root', 0644, [
            'config' => [
                'autoload' => [
                    'maker.php' => <<<CFG
<?php

declare(strict_types=1);

use Dot\Maker\Maker;

return [
    Maker::class => [
        'stub_directory' => getcwd() . '/src/App/resources/stubs',
    ],
];
CFG,
                ],
            ],
        ]);

        $this->configPath = $fileSystem->url() . '/config/autoload/maker.php';
    }

    public function testWillInstantiate(): void
    {
        $config = new Config('test', $this->configPath);
        $this->assertInstanceOf(Config::class, $config);
    }
}
