<?php

declare(strict_types=1);

namespace DotTest\Maker\Component;

use Dot\Maker\Component\Constant;
use Dot\Maker\VisibilityEnum;
use PHPUnit\Framework\TestCase;

class ConstantTest extends TestCase
{
    public function testAccessorsWillGetAndSetValues(): void
    {
        $constant = new Constant('NAME', "'value'");

        $this->assertSame('NAME', $constant->name);
        $this->assertSame("'value'", $constant->value);
        $this->assertSame('', $constant->getType());
        $this->assertSame(VisibilityEnum::Public, $constant->getVisibility());

        $constant->setType('string')->setVisibility(VisibilityEnum::Private);

        $this->assertSame('string', $constant->getType());
        $this->assertSame(VisibilityEnum::Private, $constant->getVisibility());
    }

    public function testWillRenderWithoutType(): void
    {
        $this->assertSame('public const NAME = 1;', (new Constant('NAME', '1'))->render());
    }

    public function testWillRenderWithTypeVisibilityAndIndentation(): void
    {
        $constant = (new Constant('NAME', "'some:action'"))
            ->setType('string')
            ->setVisibility(VisibilityEnum::Protected);

        $this->assertSame("    protected const string NAME = 'some:action';", $constant->render(4));
    }
}
