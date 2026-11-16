<?php

declare(strict_types=1);

namespace DotTest\Maker;

use PHPUnit\Framework\TestCase;

use function dirname;
use function escapeshellarg;
use function file_put_contents;
use function mkdir;
use function proc_close;
use function proc_open;
use function sprintf;
use function stream_get_contents;
use function var_export;

use const PHP_BINARY;

/**
 * Runs bin/dot-maker as a separate process, because the exit code only exists outside PHPUnit.
 */
class BinTest extends TestCase
{
    use TempDirectoryTrait;

    public function testUnknownComponentExitsWithFailure(): void
    {
        [$exitCode, $stderr] = $this->runBin('nonsense');

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('Unknown component: "nonsense"', $stderr);
    }

    public function testHelpExitsWithSuccess(): void
    {
        [$exitCode] = $this->runBin('');

        $this->assertSame(0, $exitCode);
    }

    /**
     * @return array{int, string}
     */
    private function runBin(string $component): array
    {
        $repository = dirname(__DIR__);
        $project    = $this->createTempDirectory();
        mkdir($project . '/vendor');
        file_put_contents(
            $project . '/vendor/autoload.php',
            sprintf('<?php require %s;', var_export($repository . '/vendor/autoload.php', true))
        );
        file_put_contents($project . '/composer.json', '{"autoload":{"psr-4":{"Api\\\\Book\\\\":"src/Book/src/"}}}');

        $command = sprintf(
            '%s %s %s',
            escapeshellarg(PHP_BINARY),
            escapeshellarg($repository . '/bin/dot-maker'),
            escapeshellarg($component)
        );
        $process = proc_open($command, [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, $project);
        $this->assertIsResource($process);

        stream_get_contents($pipes[1]);
        $stderr = (string) stream_get_contents($pipes[2]);

        return [proc_close($process), $stderr];
    }
}
