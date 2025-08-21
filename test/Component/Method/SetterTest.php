<?php

declare(strict_types=1);

namespace Component\Method;

use Dot\Maker\Component\Method\Setter;
use Dot\Maker\Component\Parameter;
use Dot\Maker\Component\ParameterInterface;
use PHPUnit\Framework\TestCase;

class SetterTest extends TestCase
{
    private string $methodName = 'getParam';

    public function testWillInstantiate(): void
    {
        $setter = new Setter($this->methodName);
        $this->assertContainsOnlyInstancesOf(Setter::class, [$setter]);
        $this->assertSame($this->methodName, $setter->getName());
        $this->assertSame($this->methodName, $setter->name);
    }

    public function testWillSetTarget(): void
    {
        $setter = new Setter($this->methodName);

        $this->expectExceptionMessage('Typed property Dot\Maker\Component\Method\Setter::$target'
            . ' must not be accessed before initialization');

        $this->assertContainsOnlyInstancesOf(ParameterInterface::class, [$setter->getTarget()]);
    }

    public function testWillRender(): void
    {
        $parameter = new Parameter('param', 'string', true, 'null');
        $this->assertSame(<<<BODY
public function setParam(?string \$param): self
    {
        \$this->param = \$param;

        return \$this;
    }
BODY, $parameter->getSetter()->render());
    }
}
