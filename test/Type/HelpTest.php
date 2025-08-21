<?php

declare(strict_types=1);

namespace DotTest\Maker\Type;

use Dot\Maker\Config;
use Dot\Maker\Context;
use Dot\Maker\FileSystem;
use Dot\Maker\IO\Output;
use Dot\Maker\Type\Help;
use DotTest\Maker\Helper;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

use function fclose;
use function fopen;
use function rewind;
use function stream_get_contents;

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

        $this->assertSame(Helper::getHelpText(), $actual);
    }
}
