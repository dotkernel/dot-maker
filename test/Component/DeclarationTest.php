<?php

declare(strict_types=1);

namespace Component;

use Dot\Maker\Component\Declaration;
use Dot\Maker\Component\Parameter;
use PHPUnit\Framework\TestCase;

class DeclarationTest extends TestCase
{
    private string $declarationName = 'getParam';

    public function testWillInstantiate(): void
    {
        $declaration = new Declaration($this->declarationName);
        $this->assertContainsOnlyInstancesOf(Declaration::class, [$declaration]);
        $this->assertSame($this->declarationName, $declaration->name);
    }

    public function testWillAddParameter(): void
    {
        $declaration = new Declaration($this->declarationName);
        $this->assertCount(0, $declaration->getParameters());
        $declaration->addParameter(
            new Parameter('param', 'string')
        );
        $this->assertCount(1, $declaration->getParameters());
    }

    public function testWillSetComment(): void
    {
        $comment = <<<COMM
/**
 * This is a comment
 */
COMM;

        $declaration = new Declaration($this->declarationName);
        $this->assertSame('', $declaration->getComment());
        $declaration->setComment($comment);
        $this->assertSame($comment, $declaration->getComment());
    }

    public function testWillSetNullable(): void
    {
        $declaration = new Declaration($this->declarationName);
        $this->assertFalse($declaration->isNullable());
        $declaration->setNullable(true);
        $this->assertTrue($declaration->isNullable());
    }

    public function testWillSetReturnType(): void
    {
        $declaration = new Declaration($this->declarationName);
        $this->assertSame('void', $declaration->getReturnType());
        $declaration->setReturnType('self');
        $this->assertSame('self', $declaration->getReturnType());
    }

    public function testWillRenderParameters(): void
    {
        $declaration = new Declaration($this->declarationName);
        $this->assertSame('', $declaration->renderParameters());

        $declaration->addParameter(
            new Parameter('param', 'string')
        );
        $this->assertSame('        string $param,', $declaration->renderParameters());

        $declaration->addParameter(
            new Parameter('other', 'string')
        );
        $this->assertSame(<<<BODY
        string \$param,
        string \$other,
BODY, $declaration->renderParameters());
    }

    public function testWillRenderSignature(): void
    {
        $declaration = new Declaration($this->declarationName);
        $this->assertSame(': void', $declaration->renderSignature());

        $declaration->setReturnType('');
        $this->assertSame('', $declaration->renderSignature());

        $declaration->setReturnType('self');
        $this->assertSame(': self', $declaration->renderSignature());

        $declaration->setReturnType('string');
        $declaration->setNullable(true);
        $this->assertSame(': ?string', $declaration->renderSignature());
    }

    public function testWillRender(): void
    {
        $comment = <<<COMM
/**
     * @param string \$param
     */
COMM;

        $declaration = (new Declaration($this->declarationName))
            ->setComment($comment)
            ->setNullable(true)
            ->setReturnType('self')
            ->addParameter(
                new Parameter('param', 'string')
            );
        $this->assertSame($this->dataProviderRenderedDeclaration(), (string) $declaration);
    }

    private function dataProviderRenderedDeclaration(): string
    {
        return <<<BODY
/**
     * @param string \$param
     */
    public function getParam(
        string \$param,
    ): ?self;
BODY;
    }
}
