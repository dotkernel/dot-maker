<?php

declare(strict_types=1);

namespace DotTest\Maker;

use Dot\Maker\IO\Input;
use Dot\Maker\IO\Output;
use Dot\Maker\Maker;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;

use function fwrite;
use function preg_replace;
use function rewind;
use function stream_get_contents;

use const PHP_EOL;

/**
 * End-to-end runs of Maker for the 3.0 fixes: end of input, next steps outside "make module",
 * and refusing unsupported components before asking for a module.
 */
class MakerRegressionTest extends TestCase
{
    use StreamTrait;

    private vfsStreamDirectory $root;
    /** @var resource $outputStream */
    private $outputStream;
    /** @var resource $errorStream */
    private $errorStream;

    protected function setUp(): void
    {
        $this->root = vfsStream::setup('root', 0755, [
            'composer.json' => '{"autoload":{"psr-4":{"Api\\\\Book\\\\":"src/Book/src/"}}}',
            'src'           => [
                'Book' => [
                    'src' => [],
                ],
            ],
        ]);

        $this->outputStream = $this->openMemoryStream();
        $this->errorStream  = $this->openMemoryStream();
        Output::setOutputStream($this->outputStream);
        Output::setErrorStream($this->errorStream);
    }

    public function testEndOfInputAtModulePromptFailsInsteadOfLooping(): void
    {
        Input::setStream($this->openMemoryStream());

        $result = (new Maker($this->root->url()))(['dot-maker', 'command']);

        $this->assertSame(Output::FAILURE, $result);
        $this->assertStringContainsString('No module name given before the end of input.', $this->stderr());
    }

    public function testStandaloneCommandsPrintNextStepsForEachCommand(): void
    {
        $this->answer('Book', 'Import', 'Export', '');

        $result = (new Maker($this->root->url()))(['dot-maker', 'command']);

        $this->assertSame(Output::SUCCESS, $result);
        $output = $this->stdout();
        $this->assertStringContainsString('Next steps:', $output);
        $this->assertStringContainsString('Api\Book\Command\ImportCommand::class', $output);
        $this->assertStringContainsString('Api\Book\Command\ExportCommand::class', $output);
    }

    public function testFormInApiIsRefusedBeforeAskingForAModule(): void
    {
        $this->answer('Book');

        $result = (new Maker($this->root->url()))(['dot-maker', 'form']);

        $this->assertSame(Output::FAILURE, $result);
        $this->assertStringContainsString('Cannot create Forms in an API', $this->stderr());
        $this->assertStringNotContainsString('Existing module name', $this->stdout());
    }

    private function answer(string ...$lines): void
    {
        $stream = $this->openMemoryStream();
        foreach ($lines as $line) {
            fwrite($stream, $line . PHP_EOL);
        }
        rewind($stream);
        Input::setStream($stream);
    }

    private function stdout(): string
    {
        return $this->read($this->outputStream);
    }

    private function stderr(): string
    {
        return $this->read($this->errorStream);
    }

    /**
     * @param resource $stream
     */
    private function read($stream): string
    {
        rewind($stream);

        return (string) preg_replace('/\e\[\d+(;\d+)?m/', '', (string) stream_get_contents($stream));
    }
}
