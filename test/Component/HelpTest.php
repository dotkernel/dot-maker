<?php

declare(strict_types=1);

namespace Component;

use Dot\Maker\ColorEnum;
use Dot\Maker\Config;
use Dot\Maker\Context;
use Dot\Maker\FileSystem;
use Dot\Maker\IO\Output;
use Dot\Maker\Type\Help;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

use function fclose;
use function fopen;
use function rewind;
use function stream_get_contents;

use const PHP_EOL;

class HelpTest extends TestCase
{
    private Config $config;
    private Context $context;
    private FileSystem $fileSystem;

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
        $this->fileSystem = new FileSystem($this->context);
    }

    public function testWillDisplayHelpMessage(): void
    {
        $stream = fopen('php://memory', 'w+');
        Output::setOutputStream($stream);

        $help = new Help($this->fileSystem, $this->context, $this->config);

        $help();
        rewind($stream);
        $actual = stream_get_contents($stream);
        fclose($stream);

        $expected =
            ColorEnum::colorize('dot-maker', ColorEnum::ForegroundBrightBlue) . PHP_EOL
            . PHP_EOL
            . 'Usage:' . PHP_EOL
            . ColorEnum::colorize('./vendor/bin/dot-maker', ColorEnum::ForegroundBrightWhite)
                . ' '
                . ColorEnum::colorize('<component>', ColorEnum::ForegroundBrightYellow)
                . PHP_EOL
            . 'OR' . PHP_EOL
            . ColorEnum::colorize('composer make', ColorEnum::ForegroundBrightWhite)
                . ' '
                . ColorEnum::colorize('<component>', ColorEnum::ForegroundBrightYellow)
                . PHP_EOL
            . PHP_EOL
            . 'Where '
                . ColorEnum::colorize('<component>', ColorEnum::ForegroundBrightYellow)
                . ' must be replaced with one of the following strings:'
                . PHP_EOL
            . '— collection' . PHP_EOL
            . '— command' . PHP_EOL
            . '— entity' . PHP_EOL
            . '— form' . PHP_EOL
            . '— handler' . PHP_EOL
            . '— input' . PHP_EOL
            . '— input-filter' . PHP_EOL
            . '— middleware' . PHP_EOL
            . '— module' . PHP_EOL
            . '— repository' . PHP_EOL
            . '— service' . PHP_EOL
            . '— service-interface' . PHP_EOL;

        $this->assertSame($expected, $actual);
    }
}
