<?php

declare(strict_types=1);

namespace DotTest\Maker;

use Dot\Maker\ColorEnum;
use Dot\Maker\IO\Output;
use PHPUnit\Framework\TestCase;

use function fclose;
use function fopen;
use function rewind;
use function stream_get_contents;

class ColorEnumTest extends TestCase
{
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

    public function testWillRenderMessageWithBackgroundColor(): void
    {
        Output::write(
            ColorEnum::colorize('Test Message', ColorEnum::BackgroundBlack)
        );

        rewind($this->outputStream);
        $this->assertSame("\033[40mTest Message\033[0m", stream_get_contents($this->outputStream));
    }

    public function testWillRenderMessageWithForegroundColor(): void
    {
        Output::write(
            ColorEnum::colorize('Test Message', ColorEnum::ForegroundBrightGreen)
        );

        rewind($this->outputStream);
        $this->assertSame("\033[92mTest Message\033[0m", stream_get_contents($this->outputStream));
    }

    public function testWillRenderMessageWithBackgroundAndForegroundColor(): void
    {
        Output::write(
            ColorEnum::colorize(
                'Test Message',
                ColorEnum::ForegroundBrightGreen,
                ColorEnum::BackgroundBlack
            )
        );

        rewind($this->outputStream);
        $this->assertSame("\033[92;40mTest Message\033[0m", stream_get_contents($this->outputStream));
    }
}
