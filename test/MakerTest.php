<?php

declare(strict_types=1);

namespace DotTest\Maker;

use Dot\Maker\ColorEnum;
use Dot\Maker\IO\Output;
use Dot\Maker\Maker;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

use function fclose;
use function fopen;
use function rewind;
use function stream_get_contents;

use const PHP_EOL;

class MakerTest extends TestCase
{
    public function testWillInstantiate(): void
    {
        $maker = new Maker('');
        $this->assertContainsOnlyInstancesOf(Maker::class, [$maker]);
    }

    public function testInvokeWillOutputErrorWhenNotInCliMode(): void
    {
        $errorStream = fopen('php://memory', 'w+');
        Output::setErrorStream($errorStream);

        $maker = $this->getMockBuilder(Maker::class)
            ->onlyMethods(['isCli'])
            ->setConstructorArgs([''])
            ->getMock();
        $maker->method('isCli')->willReturn(false);
        $maker([]);

        rewind($errorStream);
        $this->assertSame(
            ColorEnum::colorize('dot-maker must be run in CLI only', ColorEnum::ForegroundBrightRed) . PHP_EOL,
            stream_get_contents($errorStream)
        );

        fclose($errorStream);
    }

    public function testInvokeWillOutputErrorWhenInvalidComponent(): void
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

        $errorStream = fopen('php://memory', 'w+');
        Output::setErrorStream($errorStream);

        $outputStream = fopen('php://memory', 'w+');
        Output::setOutputStream($outputStream);

        $maker = $this->getMockBuilder(Maker::class)
            ->onlyMethods(['isCli'])
            ->setConstructorArgs([$root->url()])
            ->getMock();
        $maker->method('isCli')->willReturn(true);
        $maker(['', 'invalid-component']);

        rewind($errorStream);
        rewind($outputStream);
        $this->assertSame(
            ColorEnum::colorize('Unknown component: "invalid-component"', ColorEnum::ForegroundBrightRed) . PHP_EOL,
            stream_get_contents($errorStream)
        );
        $this->assertEmpty(stream_get_contents($outputStream));

        fclose($errorStream);
        fclose($outputStream);
    }

    public function testInvokeWithoutArgsWillOutputHelpText(): void
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

        $errorStream = fopen('php://memory', 'w+');
        Output::setErrorStream($errorStream);

        $outputStream = fopen('php://memory', 'w+');
        Output::setOutputStream($outputStream);

        $maker = $this->getMockBuilder(Maker::class)
            ->onlyMethods(['isCli'])
            ->setConstructorArgs([$root->url()])
            ->getMock();
        $maker->method('isCli')->willReturn(true);
        $maker(['', '']);

        rewind($errorStream);
        rewind($outputStream);
        $this->assertSame(
            Helper::getHelpText(),
            stream_get_contents($outputStream)
        );
        $this->assertEmpty(stream_get_contents($errorStream));

        fclose($errorStream);
        fclose($outputStream);
    }
}
