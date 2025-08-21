<?php

declare(strict_types=1);

namespace Component\Method;

use Dot\Maker\Component\Method\Getter;
use Dot\Maker\Component\Parameter;
use Dot\Maker\Component\ParameterInterface;
use PHPUnit\Framework\TestCase;

class GetterTest extends TestCase
{
    private string $methodName = 'getParam';

    public function testWillInstantiate(): void
    {
        $getter = new Getter($this->methodName);
        $this->assertContainsOnlyInstancesOf(Getter::class, [$getter]);
        $this->assertSame($this->methodName, $getter->getName());
        $this->assertSame($this->methodName, $getter->name);
    }

    public function testWillSetTarget(): void
    {
        $getter = new Getter($this->methodName);

        $this->expectExceptionMessage('Typed property Dot\Maker\Component\Method\Getter::$target'
            . ' must not be accessed before initialization');

        $this->assertContainsOnlyInstancesOf(ParameterInterface::class, [$getter->getTarget()]);
    }

    public function testWillRender(): void
    {
        $parameter = new Parameter('param', 'string', true, 'null');
        $this->assertSame(<<<BODY
public function getParam(): ?string
    {
        return \$this->param;
    }
BODY, $parameter->getGetter()->render());
    }
}
