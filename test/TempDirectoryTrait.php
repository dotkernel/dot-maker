<?php

declare(strict_types=1);

namespace DotTest\Maker;

use FilesystemIterator;
use PHPUnit\Framework\Attributes\After;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

use function is_dir;
use function mkdir;
use function rmdir;
use function sprintf;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;

/**
 * Real directories for tests that run external processes (bin/dot-maker, phpcs), removed after each test.
 */
trait TempDirectoryTrait
{
    /** @var list<string> $tempDirectories */
    private array $tempDirectories = [];

    protected function createTempDirectory(): string
    {
        $path = sprintf('%s/dot-maker-test-%s', sys_get_temp_dir(), uniqid());
        mkdir($path, 0777, true);
        $this->tempDirectories[] = $path;

        return $path;
    }

    #[After]
    public function removeTempDirectories(): void
    {
        foreach ($this->tempDirectories as $path) {
            if (! is_dir($path)) {
                continue;
            }
            $items = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST
            );
            /** @var SplFileInfo $item */
            foreach ($items as $item) {
                $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
            }
            rmdir($path);
        }
        $this->tempDirectories = [];
    }
}
