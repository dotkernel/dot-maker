<?php

declare(strict_types=1);

namespace DotTest\Maker\IO;

use Dot\Maker\IO\Input;
use Dot\Maker\IO\Output;
use PHPUnit\Framework\TestCase;

use function fclose;
use function fopen;
use function fwrite;
use function rewind;
use function stream_get_contents;
use function stream_get_meta_data;

use const PHP_EOL;

class InputTest extends TestCase
{
    public function testWillModifyInputStream(): void
    {
        $oldStream = Input::getStream();
        $this->assertIsResource($oldStream);
        $oldMetadata = stream_get_meta_data($oldStream);
        $this->assertSame('STDIO', $oldMetadata['stream_type']);
        $this->assertSame('php://stdin', $oldMetadata['uri']);

        Input::setStream(fopen('php://memory', 'w+'));

        $newStream = Input::getStream();
        $this->assertIsResource($oldStream);
        $newMetadata = stream_get_meta_data($newStream);
        $this->assertSame('MEMORY', $newMetadata['stream_type']);
        $this->assertSame('php://memory', $newMetadata['uri']);

        $this->assertNotSame($oldStream, $newStream);

        fclose($oldStream);
        fclose($newStream);
    }

    public function testPromptWillOutputMessageAndReadInput(): void
    {
        $outputStream = fopen('php://memory', 'w+');
        Output::setOutputStream($outputStream);

        $inputStream = fopen('php://memory', 'w+');
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

    /**
     * @testWith [""]
     *           ["y"]
     *           ["yes"]
     *           ["Y"]
     *           ["Yes"]
     */
    public function testConfirmWillOutputMessageAndReadPositiveAnswer(string $input): void
    {
        $outputStream = fopen('php://memory', 'w+');
        Output::setOutputStream($outputStream);

        $inputStream = fopen('php://memory', 'w+');
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

    /**
     * @testWith [""]
     *           ["n"]
     *           ["no"]
     *           ["N"]
     *           ["No"]
     */
    public function testConfirmWillOutputMessageAndReadNegativeAnswer(string $input): void
    {
        $outputStream = fopen('php://memory', 'w+');
        Output::setOutputStream($outputStream);

        $inputStream = fopen('php://memory', 'w+');
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
}
