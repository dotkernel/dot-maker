<?php

declare(strict_types=1);

namespace DotTest\Maker\IO;

use Dot\Maker\ColorEnum;
use Dot\Maker\IO\Output;
use PHPUnit\Framework\TestCase;

use function fclose;
use function fopen;
use function rewind;
use function stream_get_contents;
use function stream_get_meta_data;

use const PHP_EOL;

class OutputTest extends TestCase
{
    public function testWillModifyErrorStream(): void
    {
        $oldErrorStream = Output::getErrorStream();
        $this->assertIsResource($oldErrorStream);
        $oldErrorStreamMetadata = stream_get_meta_data($oldErrorStream);
        $this->assertSame('STDIO', $oldErrorStreamMetadata['stream_type']);
        $this->assertSame('php://stderr', $oldErrorStreamMetadata['uri']);

        Output::setErrorStream(fopen('php://memory', 'w+'));

        $newErrorStream = Output::getErrorStream();
        $this->assertIsResource($newErrorStream);
        $newErrorStreamMetadata = stream_get_meta_data($newErrorStream);
        $this->assertSame('MEMORY', $newErrorStreamMetadata['stream_type']);
        $this->assertSame('php://memory', $newErrorStreamMetadata['uri']);

        $this->assertNotSame($oldErrorStream, $newErrorStream);

        fclose($oldErrorStream);
        fclose($newErrorStream);
    }

    public function testWillModifyOutputStream(): void
    {
        $oldOutputStream = Output::getOutputStream();
        $this->assertIsResource($oldOutputStream);
        $oldOutputStreamMetadata = stream_get_meta_data($oldOutputStream);
        $this->assertSame('STDIO', $oldOutputStreamMetadata['stream_type']);
        $this->assertSame('php://stdout', $oldOutputStreamMetadata['uri']);

        Output::setOutputStream(fopen('php://memory', 'w+'));

        $newOutputStream = Output::getOutputStream();
        $this->assertIsResource($newOutputStream);
        $newOutputStreamMetadata = stream_get_meta_data($newOutputStream);
        $this->assertSame('MEMORY', $newOutputStreamMetadata['stream_type']);
        $this->assertSame('php://memory', $newOutputStreamMetadata['uri']);

        $this->assertNotSame($oldOutputStream, $newOutputStream);

        fclose($oldOutputStream);
        fclose($newOutputStream);
    }

    public function testWillOutputError(): void
    {
        Output::setErrorStream(fopen('php://memory', 'w+'));

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
        Output::setOutputStream(fopen('php://memory', 'w+'));

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
        Output::setOutputStream(fopen('php://memory', 'w+'));

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
        Output::setOutputStream(fopen('php://memory', 'w+'));

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
        Output::setOutputStream(fopen('php://memory', 'w+'));

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
        Output::setOutputStream(fopen('php://memory', 'w+'));

        $stream = Output::getOutputStream();
        $this->assertIsResource($stream);

        $message = 'Test message';
        Output::writeLine($message);

        rewind($stream);
        $this->assertSame($message . PHP_EOL, stream_get_contents($stream));

        fclose($stream);
    }
}
