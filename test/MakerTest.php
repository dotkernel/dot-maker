<?php

declare(strict_types=1);

namespace DotTest\Maker;

use Dot\Maker\ColorEnum;
use Dot\Maker\IO\Input;
use Dot\Maker\IO\Output;
use Dot\Maker\Maker;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

use function fclose;
use function fopen;
use function fwrite;
use function rewind;
use function stream_get_contents;

use const PHP_EOL;

class MakerTest extends TestCase
{
    /** @var resource $outputStream */
    private $outputStream;
    /** @var resource $inputStream */
    private $inputStream;
    /** @var resource $errorStream */
    private $errorStream;

    protected function setUp(): void
    {
        $this->errorStream = fopen('php://memory', 'w+');
        Output::setErrorStream($this->errorStream);

        $this->inputStream = fopen('php://memory', 'w+');
        Input::setStream($this->inputStream);

        $this->outputStream = fopen('php://memory', 'w+');
        Output::setOutputStream($this->outputStream);
    }

    protected function tearDown(): void
    {
        fclose($this->outputStream);
        fclose($this->inputStream);
        fclose($this->errorStream);
    }

    public function testWillInstantiate(): void
    {
        $maker = new Maker('');
        $this->assertContainsOnlyInstancesOf(Maker::class, [$maker]);
        $this->assertTrue($maker->isCli());
    }

    public function testCallingInvokeWillOutputErrorWhenNotInCliMode(): void
    {
        $maker = $this->getMockBuilder(Maker::class)
            ->onlyMethods(['isCli'])
            ->setConstructorArgs([''])
            ->getMock();
        $maker->method('isCli')->willReturn(false);
        $maker([]);

        rewind($this->errorStream);
        $this->assertSame(
            ColorEnum::colorize('dot-maker must be run in CLI only', ColorEnum::ForegroundBrightRed) . PHP_EOL,
            stream_get_contents($this->errorStream)
        );
    }

    public function testCallingInvokeWillOutputErrorWhenInvalidComponent(): void
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

        $maker = $this->getMockBuilder(Maker::class)
            ->onlyMethods(['isCli'])
            ->setConstructorArgs([$root->url()])
            ->getMock();
        $maker->method('isCli')->willReturn(true);
        $maker(['', 'invalid-component']);

        rewind($this->errorStream);
        rewind($this->outputStream);
        $this->assertSame(
            ColorEnum::colorize('Unknown component: "invalid-component"', ColorEnum::ForegroundBrightRed) . PHP_EOL,
            stream_get_contents($this->errorStream)
        );
        $this->assertEmpty(stream_get_contents($this->outputStream));
    }

    public function testCallingInvokeWithoutArgsWillOutputHelpText(): void
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

        $maker = $this->getMockBuilder(Maker::class)
            ->onlyMethods(['isCli'])
            ->setConstructorArgs([$root->url()])
            ->getMock();
        $maker->method('isCli')->willReturn(true);
        $maker(['', '']);

        rewind($this->errorStream);
        rewind($this->outputStream);
        $this->assertSame(
            Helper::getHelpText(),
            stream_get_contents($this->outputStream)
        );
        $this->assertEmpty(stream_get_contents($this->errorStream));
    }

    public function testCallingInvokeWithArgsModuleWillOutputDebugInfo(): void
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

        fwrite($this->inputStream, PHP_EOL);
        rewind($this->inputStream);

        $maker = $this->getMockBuilder(Maker::class)
            ->onlyMethods(['isCli'])
            ->setConstructorArgs([$root->url()])
            ->getMock();
        $maker->method('isCli')->willReturn(true);
        $maker(['', 'module']);

        rewind($this->outputStream);
        $this->assertSame(
            <<<BODY
\033[94mDetected project type: Api\033[0m
\033[94mCore architecture: No\033[0m

New module name: 
BODY,
            stream_get_contents($this->outputStream)
        );
        $this->assertEmpty(stream_get_contents($this->errorStream));
    }

    public function testCallingInvokeWithValidComponentIdentifierWillSucceed(): void
    {
        $root = vfsStream::setup('root', 0644, [
            'composer.json' => '{
                "autoload": {
                    "psr-4": {
                        "Api\\\\App\\\\": "src/App/src/"
                    }
                }
            }',
            'src'           => [
                'App' => [],
            ],
        ]);

        fwrite($this->inputStream, 'App' . PHP_EOL);
        fwrite($this->inputStream, 'Test' . PHP_EOL);
        fwrite($this->inputStream, PHP_EOL);
        rewind($this->inputStream);

        $maker = $this->getMockBuilder(Maker::class)
            ->onlyMethods(['isCli'])
            ->setConstructorArgs([$root->url()])
            ->getMock();
        $maker->method('isCli')->willReturn(true);
        $maker(['', 'command']);

        rewind($this->errorStream);
        $this->assertEmpty(stream_get_contents($this->errorStream));
    }
}
