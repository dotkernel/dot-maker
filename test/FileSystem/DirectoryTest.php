<?php

declare(strict_types=1);

namespace DotTest\Maker\FileSystem;

use Dot\Maker\Component;
use Dot\Maker\FileSystem\Directory;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;

class DirectoryTest extends TestCase
{
    private vfsStreamDirectory $root;

    protected function setUp(): void
    {
        $this->root = vfsStream::setup('root', 0644, [
            'Existing' => [],
        ]);
    }

    public function testWillInstantiate(): void
    {
        $directory = new Directory('any', $this->root->url());

        $this->assertContainsOnlyInstancesOf(Directory::class, [$directory]);
    }

    public function testWillCreate(): void
    {
        $this->assertFalse($this->root->hasChild('New'));
        $directory = new Directory('New', $this->root->url());
        $directory->create();
        $this->assertTrue($this->root->hasChild('New'));
    }

    public function testWillDetectExistingDirectory(): void
    {
        $directory = new Directory('Existing', $this->root->url());
        $this->assertTrue($directory->exists());
    }

    public function testWillGetComponent(): void
    {
        $directory = new Directory('Existing', $this->root->url());
        $this->assertContainsOnlyInstancesOf(Component::class, [$directory->getComponent()]);
    }

    public function testWillGetName(): void
    {
        $directory = new Directory('Existing', $this->root->url());
        $this->assertSame('Existing', $directory->getName());
    }

    public function testWillGetPath(): void
    {
        $directory = new Directory('Existing', $this->root->url());
        $this->assertSame($this->root->getChild('Existing')->url(), $directory->getPath());
    }

    public function testWillGetParentDirectory(): void
    {
        $directory = new Directory('Existing', $this->root->url());
        $this->assertSame($this->root->url(), $directory->getParentDirectory());
    }
}
