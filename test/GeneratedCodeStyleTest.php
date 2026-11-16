<?php

declare(strict_types=1);

namespace DotTest\Maker;

use Dot\Maker\IO\Input;
use Dot\Maker\IO\Output;
use Dot\Maker\Maker;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function dirname;
use function escapeshellarg;
use function exec;
use function file_put_contents;
use function fwrite;
use function implode;
use function json_encode;
use function mkdir;
use function rewind;
use function sprintf;

use const JSON_THROW_ON_ERROR;
use const PHP_BINARY;
use const PHP_EOL;

/**
 * Generates a complete module and checks it with the Laminas Coding Standard that Dotkernel projects use.
 */
class GeneratedCodeStyleTest extends TestCase
{
    use StreamTrait;
    use TempDirectoryTrait;

    #[DataProvider('projectProvider')]
    public function testGeneratedModulePassesLaminasCodingStandard(string $projectType, bool $withCore): void
    {
        $project = $this->createTempDirectory();
        mkdir($project . '/src', 0777, true);
        $psr4 = [$projectType . '\\App\\' => 'src/App/src/'];
        if ($withCore) {
            mkdir($project . '/src/Core/src', 0777, true);
            $psr4['Core\\App\\'] = 'src/Core/src/App/src/';
        }
        $composer = json_encode(['autoload' => ['psr-4' => $psr4]], JSON_THROW_ON_ERROR);
        file_put_contents($project . '/composer.json', $composer);

        Output::setOutputStream($this->openMemoryStream());
        Output::setErrorStream($this->openMemoryStream());
        $input = $this->openMemoryStream();
        // Module name, then every confirmation answered with its default (yes) at the end of input.
        fwrite($input, 'Bookcase' . PHP_EOL);
        rewind($input);
        Input::setStream($input);

        $this->assertSame(Output::SUCCESS, (new Maker($project))(['dot-maker', 'module']));

        $repository = dirname(__DIR__);
        $command    = sprintf(
            '%s %s --standard=LaminasCodingStandard --no-cache -q --report=emacs %s 2>&1',
            escapeshellarg(PHP_BINARY),
            escapeshellarg($repository . '/vendor/bin/phpcs'),
            escapeshellarg($project . '/src')
        );
        exec($command, $report, $exitCode);

        $this->assertSame(0, $exitCode, implode(PHP_EOL, $report));
    }

    /**
     * @return array<string, array{string, bool}>
     */
    public static function projectProvider(): array
    {
        return [
            'Api with Core'      => ['Api', true],
            'Api without Core'   => ['Api', false],
            'Admin with Core'    => ['Admin', true],
            'Admin without Core' => ['Admin', false],
        ];
    }
}
