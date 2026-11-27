<?php

declare(strict_types=1);

namespace DotTest\Maker;

use Dot\Maker\Context;
use Dot\Maker\Exception\RuntimeException;
use Dot\Maker\FileSystem;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

class FileSystemTest extends TestCase
{
    private FileSystem $fileSystem;

    protected function setUp(): void
    {
        $root = vfsStream::setup('root', 0644, [
            'composer.json' => '{"autoload":{"psr-4":{"Api\\\\App\\\\":"src/App/src/"}}}',
        ]);

        $this->fileSystem = new FileSystem(new Context($root->url()));
    }

    public function testGetModuleNameBeforeSetModuleNameThrows(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No module selected.');

        $this->fileSystem->getModuleName();
    }

    public function testNameEqualToSuffixKeepsTheName(): void
    {
        $this->fileSystem->setModuleName('Book');

        $this->assertSame('Entity', $this->fileSystem->entity('Entity')->getComponent()->getClassName());
        $this->assertSame('Book', $this->fileSystem->entity('BookEntity')->getComponent()->getClassName());
        $this->assertSame('CommandCommand', $this->fileSystem->command('Command')->getComponent()->getClassName());
        $this->assertSame('BookCommand', $this->fileSystem->command('BookCommand')->getComponent()->getClassName());
    }

    public function testServiceNamesStripTheirSuffixes(): void
    {
        $this->fileSystem->setModuleName('Book');

        $this->assertSame('BookService', $this->fileSystem->service('BookService')->getComponent()->getClassName());
        $this->assertSame('ServiceService', $this->fileSystem->service('Service')->getComponent()->getClassName());
        $this->assertSame(
            'BookServiceInterface',
            $this->fileSystem->serviceInterface('BookServiceInterface')->getComponent()->getClassName()
        );
    }
}
