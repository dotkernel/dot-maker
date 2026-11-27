<?php

declare(strict_types=1);

namespace DotTest\Maker\IO;

use Dot\Maker\IO\Input;
use Dot\Maker\IO\Output;
use DotTest\Maker\StreamTrait;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

use function fclose;
use function fwrite;
use function rewind;
use function stream_get_contents;

use const PHP_EOL;
use const STDIN;

class InputTest extends TestCase
{
    use StreamTrait;

    public function testWillModifyInputStream(): void
    {
        $this->resetStreams();
        $this->assertSame(STDIN, Input::getStream());

        $stream = $this->openMemoryStream();
        Input::setStream($stream);
        $this->assertSame($stream, Input::getStream());

        fclose($stream);
        $this->resetStreams();
    }

    public function testPromptWillOutputMessageAndReadInput(): void
    {
        $outputStream = $this->openMemoryStream();
        Output::setOutputStream($outputStream);

        $inputStream = $this->openMemoryStream();
        fwrite($inputStream, 'Test' . PHP_EOL);
        rewind($inputStream);
        Input::setStream($inputStream);

        $message = Input::prompt('Message: ');
        rewind($outputStream);
        $this->assertSame('Test', $message);
        $this->assertSame(PHP_EOL . 'Message: ', stream_get_contents($outputStream));

        fclose($inputStream);
        fclose($outputStream);
    }

    #[TestWith([''])]
    #[TestWith(['y'])]
    #[TestWith(['yes'])]
    #[TestWith(['Y'])]
    #[TestWith(['Yes'])]
    public function testConfirmWillOutputMessageAndReadPositiveAnswer(string $input): void
    {
        $outputStream = $this->openMemoryStream();
        Output::setOutputStream($outputStream);

        $inputStream = $this->openMemoryStream();
        fwrite($inputStream, $input . PHP_EOL);
        rewind($inputStream);
        Input::setStream($inputStream);

        $confirmation = Input::confirm('Confirm?');
        rewind($outputStream);
        $this->assertTrue($confirmation);
        $this->assertSame(PHP_EOL . 'Confirm? [Y(es)/n(o)]: ', stream_get_contents($outputStream));

        fclose($inputStream);
        fclose($outputStream);
    }

    #[TestWith([''])]
    #[TestWith(['n'])]
    #[TestWith(['no'])]
    #[TestWith(['N'])]
    #[TestWith(['No'])]
    public function testConfirmWillOutputMessageAndReadNegativeAnswer(string $input): void
    {
        $outputStream = $this->openMemoryStream();
        Output::setOutputStream($outputStream);

        $inputStream = $this->openMemoryStream();
        fwrite($inputStream, $input . PHP_EOL);
        rewind($inputStream);
        Input::setStream($inputStream);

        $confirmation = Input::confirm('Confirm?', 'no');
        rewind($outputStream);
        $this->assertFalse($confirmation);
        $this->assertSame(PHP_EOL . 'Confirm? [y(es)/N(o)]: ', stream_get_contents($outputStream));

        fclose($inputStream);
        fclose($outputStream);
    }

    public function testWillAlwaysGetInputStream(): void
    {
        Input::setStream($this->openMemoryStream());

        $stream = Input::getStream();
        $this->assertIsResource($stream);
        fclose($stream);

        $stream = Input::getStream();
        $this->assertIsResource($stream);
    }

    public function testPromptReturnsEmptyStringAtEndOfInput(): void
    {
        Output::setOutputStream($this->openMemoryStream());
        Input::setStream($this->openMemoryStream());

        $this->assertSame('', Input::prompt('Name: '));
        $this->assertTrue(Input::isExhausted());
    }

    public function testIsExhaustedIsFalseWhileInputRemains(): void
    {
        Output::setOutputStream($this->openMemoryStream());
        $stream = $this->openMemoryStream();
        fwrite($stream, 'Book' . PHP_EOL . PHP_EOL);
        rewind($stream);
        Input::setStream($stream);

        $this->assertSame('Book', Input::prompt('Name: '));
        $this->assertFalse(Input::isExhausted());
    }
}
