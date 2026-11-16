<?php

declare(strict_types=1);

namespace DotTest\Maker\IO;

use Dot\Maker\ColorEnum;
use Dot\Maker\IO\Output;
use DotTest\Maker\StreamTrait;
use PHPUnit\Framework\TestCase;

use function fclose;
use function rewind;
use function stream_get_contents;

use const PHP_EOL;
use const STDERR;
use const STDOUT;

class OutputTest extends TestCase
{
    use StreamTrait;

    public function testWillModifyErrorStream(): void
    {
        $this->resetStreams();
        $this->assertSame(STDERR, Output::getErrorStream());

        $stream = $this->openMemoryStream();
        Output::setErrorStream($stream);
        $this->assertSame($stream, Output::getErrorStream());

        fclose($stream);
        $this->resetStreams();
    }

    public function testWillModifyOutputStream(): void
    {
        $this->resetStreams();
        $this->assertSame(STDOUT, Output::getOutputStream());

        $stream = $this->openMemoryStream();
        Output::setOutputStream($stream);
        $this->assertSame($stream, Output::getOutputStream());

        fclose($stream);
        $this->resetStreams();
    }

    public function testWillOutputError(): void
    {
        Output::setErrorStream($this->openMemoryStream());

        $stream = Output::getErrorStream();
        $this->assertIsResource($stream);

        $message = 'Test message';
        Output::error($message);

        rewind($stream);
        $this->assertSame(
            ColorEnum::colorize($message, ColorEnum::ForegroundBrightRed) . PHP_EOL,
            stream_get_contents($stream)
        );

        fclose($stream);
    }

    public function testWillOutputInfo(): void
    {
        Output::setOutputStream($this->openMemoryStream());

        $stream = Output::getOutputStream();
        $this->assertIsResource($stream);

        $message = 'Test message';
        Output::info($message);

        rewind($stream);
        $this->assertSame(
            ColorEnum::colorize($message, ColorEnum::ForegroundBrightBlue) . PHP_EOL,
            stream_get_contents($stream)
        );

        fclose($stream);
    }

    public function testWillOutputSuccess(): void
    {
        Output::setOutputStream($this->openMemoryStream());

        $stream = Output::getOutputStream();
        $this->assertIsResource($stream);

        $message = 'Test message';
        Output::success($message);

        rewind($stream);
        $this->assertSame(
            ColorEnum::colorize($message, ColorEnum::ForegroundBrightGreen) . PHP_EOL,
            stream_get_contents($stream)
        );

        fclose($stream);
    }

    public function testWillOutputWarning(): void
    {
        Output::setOutputStream($this->openMemoryStream());

        $stream = Output::getOutputStream();
        $this->assertIsResource($stream);

        $message = 'Test message';
        Output::warning($message);

        rewind($stream);
        $this->assertSame(
            ColorEnum::colorize($message, ColorEnum::ForegroundBrightYellow) . PHP_EOL,
            stream_get_contents($stream)
        );

        fclose($stream);
    }

    public function testWillWrite(): void
    {
        Output::setOutputStream($this->openMemoryStream());

        $stream = Output::getOutputStream();
        $this->assertIsResource($stream);

        $message = 'Test message';
        Output::write($message);

        rewind($stream);
        $this->assertSame($message, stream_get_contents($stream));

        fclose($stream);
    }

    public function testWillWriteLine(): void
    {
        Output::setOutputStream($this->openMemoryStream());

        $stream = Output::getOutputStream();
        $this->assertIsResource($stream);

        $message = 'Test message';
        Output::writeLine($message);

        rewind($stream);
        $this->assertSame($message . PHP_EOL, stream_get_contents($stream));

        fclose($stream);
    }

    public function testWillAlwaysGetErrorStream(): void
    {
        Output::setErrorStream($this->openMemoryStream());

        $stream = Output::getErrorStream();
        $this->assertIsResource($stream);
        fclose($stream);

        $stream = Output::getErrorStream();
        $this->assertIsResource($stream);
    }

    public function testWillAlwaysGetOutputStream(): void
    {
        Output::setOutputStream($this->openMemoryStream());

        $stream = Output::getOutputStream();
        $this->assertIsResource($stream);
        fclose($stream);

        $stream = Output::getOutputStream();
        $this->assertIsResource($stream);
    }

    public function testWriteFallsBackToDefaultStreamWhenStreamIsClosed(): void
    {
        $stream = $this->openMemoryStream();
        Output::setOutputStream($stream);
        fclose($stream);

        Output::write();

        $this->assertSame(STDOUT, Output::getOutputStream());
    }
}
