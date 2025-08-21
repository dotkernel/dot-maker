<?php

declare(strict_types=1);

namespace DotTest\Maker\FileSystem;

use Dot\Maker\Component;
use Dot\Maker\FileSystem\Directory;
use Dot\Maker\FileSystem\File;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamContent;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;

use function sprintf;

/**
 * @method vfsStreamContent getChild(string $name)
 */
class FileTest extends TestCase
{
    private vfsStreamDirectory $root;

    protected function setUp(): void
    {
        $this->root = vfsStream::setup('root', 0644, [
            'src' => [
                'App' => [
                    'src' => [
                        'Existing.php' => '...',
                    ],
                ],
            ],
        ]);
    }

    public function testWillInstantiate(): void
    {
        $file = new File(
            new Directory('Test', $this->root->url()),
            'Test',
            'Test'
        );

        $this->assertContainsOnlyInstancesOf(File::class, [$file]);
    }

    public function testFailingToCreateWillThrowAnError(): void
    {
        $file = $this->getMockBuilder(File::class)
            ->onlyMethods(['write'])
            ->setConstructorArgs([
                new Directory('src', $this->root->getChild('src')->getChild('App')->url()),
                'Api\\App',
                'ConfigProvider',
            ])
            ->getMock();
        $file->method('write')->willReturn(false);

        $this->assertFileDoesNotExist($file->getPath());
        $this->assertFalse($file->exists());

        $this->expectExceptionMessage(sprintf('Could not create file "%s"', $file->getPath()));
        $file->create('...');
        $this->assertFileDoesNotExist($file->getPath());
        $this->assertFalse($file->exists());
    }

    public function testWillCreate(): void
    {
        $file = new File(
            new Directory('src', $this->root->getChild('src')->getChild('App')->url()),
            'Api\\App',
            'ConfigProvider'
        );
        $this->assertFileDoesNotExist($file->getPath());
        $this->assertFalse($file->exists());

        $file->create('...');
        $this->assertFileExists($file->getPath());
        $this->assertTrue($file->exists());
    }

    public function testWillThrowAnErrorIfCannotCreateParentDirectory(): void
    {
        $file = new File(
            new Directory('Nonexistent', $this->root->chmod(0000)->url()),
            'Test',
            'Test'
        );

        $this->expectExceptionMessage(
            sprintf('Could not create parent directory "%s"', $file->getParentDirectory()->getPath())
        );
        $file->ensureParentDirectoryExists();
    }

    public function testWillDetectExistingFile(): void
    {
        $file = new File(
            new Directory('src', $this->root->getChild('src')->getChild('App')->url()),
            'Api\\App',
            'Existing'
        );
        $this->assertTrue($file->exists());
    }

    public function testWillGetParentDirectory(): void
    {
        $file = new File(
            new Directory('src', $this->root->url()),
            'Test',
            'Test'
        );
        $this->assertSame($this->root->getChild('src')->url(), $file->getParentDirectory()->getPath());
    }

    public function testWillGetComponent(): void
    {
        $file = new File(
            new Directory('src', $this->root->url()),
            'Test',
            'Test'
        );
        $this->assertContainsOnlyInstancesOf(Component::class, [$file->getComponent()]);
    }

    public function testWillGetName(): void
    {
        $file = new File(
            new Directory('src', $this->root->url()),
            'Namespace',
            'Test'
        );
        $this->assertSame('Test.php', $file->getName());
    }

    public function testWillGetPath(): void
    {
        $file = new File(
            new Directory('src', $this->root->getChild('src')->getChild('App')->url()),
            'Api\\App',
            'Existing'
        );
        $this->assertSame(
            $this->root->getChild('src')->getChild('App')->getChild('src')->getChild('Existing.php')->url(),
            $file->getPath()
        );
    }
}
