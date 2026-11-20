<?php

declare(strict_types=1);

namespace DotTest\Maker\Type;

use Dot\Maker\Config;
use Dot\Maker\Context;
use Dot\Maker\Exception\RuntimeException;
use Dot\Maker\FileSystem;
use Dot\Maker\Message;
use Dot\Maker\Type\Command;
use Dot\Maker\Type\TypeEnum;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class AbstractTypeTest extends TestCase
{
    private Command $type;

    protected function setUp(): void
    {
        $root = vfsStream::setup('root', 0644, [
            'composer.json' => '{"autoload":{"psr-4":{"Api\\\\App\\\\":"src/App/src/"}}}',
        ]);

        $context    = new Context($root->url());
        $this->type = new Command(new FileSystem($context), $context, new Config($root->url()));
    }

    #[DataProvider('validNameProvider')]
    public function testValidNames(string $name): void
    {
        $this->assertTrue($this->type->isValid($name));
    }

    #[DataProvider('invalidNameProvider')]
    public function testInvalidNames(string $name): void
    {
        $this->assertFalse($this->type->isValid($name));
    }

    /**
     * @return array<string, array{string}>
     */
    public static function validNameProvider(): array
    {
        return [
            'simple'                 => ['Book'],
            'with digits'            => ['Book2'],
            'soft keyword'           => ['Enum'],
            'only a suffix'          => ['Entity'],
            'suffix of a valid name' => ['BookEntity'],
        ];
    }

    /**
     * @return array<string, array{string}>
     */
    public static function invalidNameProvider(): array
    {
        return [
            'empty'                     => [''],
            'starts with a digit'       => ['1Book'],
            'punctuation'               => ['Book.Store'],
            'keyword class'             => ['Class'],
            'keyword list'              => ['List'],
            'keyword function'          => ['Function'],
            'keyword match'             => ['Match'],
            'keyword readonly'          => ['Readonly'],
            'reserved type int'         => ['Int'],
            'reserved type self'        => ['Self'],
            'reserved type null'        => ['Null'],
            'keyword before suffix'     => ['ListEntity'],
            'digit start before suffix' => ['1Command'],
        ];
    }

    public function testAddMessageWithoutModuleThrows(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No module selected.');

        $this->type->addMessage(new Message('test'));
    }

    public function testComponentRejectsTypesThatCannotCreateFiles(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Dot\Maker\Type\Module cannot create files.');

        $this->type->component(TypeEnum::Module);
    }
}
