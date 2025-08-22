<?php

declare(strict_types=1);

namespace Component;

use Dot\Maker\Component\Parameter;
use Dot\Maker\VisibilityEnum;
use PHPUnit\Framework\TestCase;

class ParameterTest extends TestCase
{
    public function testWillInstantiate(): void
    {
        $parameter = new Parameter('param', 'string');
        $this->assertContainsOnlyInstancesOf(Parameter::class, [$parameter]);
        $this->assertSame('param', $parameter->getName());
        $this->assertSame('param', $parameter->name);
        $this->assertSame('string', $parameter->getType());
        $this->assertSame('string', $parameter->type);
    }

    public function testWillGetGetter(): void
    {
        $parameter = new Parameter('param', 'string');

        $getter = $parameter->getGetter();
        $this->assertFalse($getter->isNullable());
        $this->assertSame('getParam', $getter->getName());
        $this->assertSame('string', $getter->getReturnType());
        $this->assertSame(VisibilityEnum::Public, $getter->getVisibility());
    }

    public function testWillGetSetter(): void
    {
        $parameter = new Parameter('param', 'string', true);

        $setter = $parameter->getSetter();
        $this->assertTrue($setter->isNullable());
        $this->assertSame('setParam', $setter->getName());
        $this->assertSame('self', $setter->getReturnType());
        $this->assertSame(VisibilityEnum::Public, $setter->getVisibility());
        $this->assertSame(<<<BODY
public function setParam(?string \$param): self
    {
        \$this->param = \$param;

        return \$this;
    }
BODY, $setter->render());
    }

    public function testWillSetDefault(): void
    {
        $parameter = new Parameter('param', 'string');
        $this->assertNull($parameter->getDefault());
        $parameter->setDefault('default');
        $this->assertSame('default', $parameter->getDefault());
    }

    public function testWillSetNullable(): void
    {
        $parameter = new Parameter('param', 'string');
        $this->assertFalse($parameter->isNullable());
        $parameter->setNullable(true);
        $this->assertTrue($parameter->isNullable());
    }

    public function testWillRender(): void
    {
        $parameter = new Parameter('param', 'string', true, '"test"');
        $this->assertSame('?string $param = "test"', (string) $parameter);
    }
}
