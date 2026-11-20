<?php

declare(strict_types=1);

namespace DotTest\Maker\Component;

use Dot\Maker\Component\ArrayAligner;
use PHPUnit\Framework\TestCase;

class ArrayAlignerTest extends TestCase
{
    public function testAlignsKeysOfTheSameArrayIncludingNestedArrayValues(): void
    {
        $code = <<<'PHP'
        return [
            'form' => $form,
            'bookStore' => $bookStore,
            'messages' => [
                'error' => $message,
            ],
        ];
        PHP;

        $expected = <<<'PHP'
        return [
            'form'      => $form,
            'bookStore' => $bookStore,
            'messages'  => [
                'error' => $message,
            ],
        ];
        PHP;

        $this->assertSame($expected, ArrayAligner::align($code));
    }

    public function testAlignsEachNestingLevelSeparately(): void
    {
        $code = <<<'PHP'
        return [
            'driver' => [
                'orm_default' => [
                    'drivers' => [
                        'Core\Book\Entity' => 'BookEntities',
                    ],
                ],
                'BookEntities' => [
                    'class' => AttributeDriver::class,
                    'paths' => [__DIR__ . '/Entity'],
                ],
            ],
        ];
        PHP;

        $expected = <<<'PHP'
        return [
            'driver' => [
                'orm_default'  => [
                    'drivers' => [
                        'Core\Book\Entity' => 'BookEntities',
                    ],
                ],
                'BookEntities' => [
                    'class' => AttributeDriver::class,
                    'paths' => [__DIR__ . '/Entity'],
                ],
            ],
        ];
        PHP;

        $this->assertSame($expected, ArrayAligner::align($code));
    }

    public function testAlignsClassConstantKeysAndIgnoresBracketsInStrings(): void
    {
        $code = <<<'PHP'
        $config = [
            BookRepository::class => Factory::class,
            'x' => '[not an array',
        ];
        PHP;

        $expected = <<<'PHP'
        $config = [
            BookRepository::class => Factory::class,
            'x'                   => '[not an array',
        ];
        PHP;

        $this->assertSame($expected, ArrayAligner::align($code));
    }

    public function testLeavesCodeWithoutArraysUnchanged(): void
    {
        $code = "\$a = foo(['id' => 1]);\n\$b = 2;\n";

        $this->assertSame($code, ArrayAligner::align($code));
    }
}
