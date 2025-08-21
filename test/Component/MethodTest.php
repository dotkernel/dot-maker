<?php

declare(strict_types=1);

namespace Component;

use Dot\Maker\Component\Inject;
use Dot\Maker\Component\Method;
use Dot\Maker\Component\Parameter;
use Dot\Maker\VisibilityEnum;
use PHPUnit\Framework\TestCase;

use const PHP_EOL;

class MethodTest extends TestCase
{
    private string $methodName = 'someMethod';

    public function testWillInstantiate(): void
    {
        $method = new Method($this->methodName);
        $this->assertContainsOnlyInstancesOf(Method::class, [$method]);
        $this->assertSame($this->methodName, $method->getName());
        $this->assertSame($this->methodName, $method->name);
    }

    public function testWillSetBody(): void
    {
        $method = new Method($this->methodName);
        $method->setBody('        // do stuff');
        $this->assertSame(PHP_EOL . '        // do stuff', $method->getBody());
        $method->prependBody('// prepend comment');
        $this->assertSame(<<<BODY

        // prepend comment
        // do stuff
BODY, $method->getBody());
        $method->appendBody('// append comment');
        $this->assertSame(<<<BODY

        // prepend comment
        // do stuff
        // append comment
BODY, $method->getBody());
    }

    public function testWillAddInject(): void
    {
        $method = new Method($this->methodName);
        $this->assertCount(0, $method->getInjects());
        $method->addInject(new Inject());
        $this->assertCount(1, $method->getInjects());
    }

    public function testWillAddParameter(): void
    {
        $method = new Method($this->methodName);
        $this->assertCount(0, $method->getParameters());
        $method->addParameter(new Parameter('param', 'string'));
        $this->assertCount(1, $method->getParameters());
    }

    public function testWillCommentBody(): void
    {
        $method = new Method($this->methodName);
        $method->setBody('return true;');
        $method->commentBody();
        $this->assertSame(PHP_EOL . '// return true;', $method->getBody());
    }

    public function testWillAddComment(): void
    {
        $comment = <<<COMM
/**
     * Some comment
     */
COMM;

        $method = new Method($this->methodName);
        $this->assertSame('', $method->getComment());
        $method->setComment($comment);
        $this->assertSame($comment, $method->getComment());
    }

    public function testWillMakeNullable(): void
    {
        $method = new Method($this->methodName);
        $this->assertFalse($method->isNullable());
        $method->setNullable(true);
        $this->assertTrue($method->isNullable());
    }

    public function testWillSetReturnType(): void
    {
        $method = new Method($this->methodName);
        $this->assertSame('void', $method->getReturnType());
        $method->setReturnType('bool');
        $this->assertSame('bool', $method->getReturnType());
    }

    public function testWillSetVisibility(): void
    {
        $method = new Method($this->methodName);
        $this->assertSame(VisibilityEnum::Public, $method->getVisibility());
        $method->setVisibility(VisibilityEnum::Protected);
        $this->assertSame(VisibilityEnum::Protected, $method->getVisibility());
        $method->setVisibility(VisibilityEnum::Private);
        $this->assertSame(VisibilityEnum::Private, $method->getVisibility());
    }

    public function testWillRender(): void
    {
        $method = (new Method($this->methodName))
            ->setVisibility(VisibilityEnum::Private)
            ->setReturnType('self')
            ->setNullable(false)
            ->setComment(<<<COMM
/**
     * @param string \$param
     */
COMM)
            ->addParameter(new Parameter('param', 'string', true, 'null'))
            ->setBody('        return $this;');
        $this->assertSame($this->dataProviderRenderedMethod(), $method->render());
    }

    private function dataProviderRenderedMethod(): string
    {
        return <<<BODY
/**
     * @param string \$param
     */
    private function someMethod(
        ?string \$param = null,
    ): self {
        return \$this;
    }
BODY;
    }
}
