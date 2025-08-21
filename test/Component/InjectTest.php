<?php

declare(strict_types=1);

namespace Component;

use Dot\Maker\Component\Inject;
use PHPUnit\Framework\TestCase;

class InjectTest extends TestCase
{
    public function testWillInstantiate(): void
    {
        $inject = new Inject();
        $this->assertContainsOnlyInstancesOf(Inject::class, [$inject]);
        $this->assertSame('Inject', $inject->name);
    }

    public function testWillInstantiateCustomInjector(): void
    {
        $inject = new Inject('CustomInjector');
        $this->assertContainsOnlyInstancesOf(Inject::class, [$inject]);
        $this->assertSame('CustomInjector', $inject->name);
    }

    public function testWillAddNamedArgument(): void
    {
        $inject = new Inject();
        $this->assertCount(0, $inject->getNamedArguments());
        $this->assertCount(0, $inject->getPositionalArguments());
        $inject->addArgument('SomeClass::class', 'someClass');
        $this->assertCount(1, $inject->getNamedArguments());
        $this->assertCount(0, $inject->getPositionalArguments());
        $this->assertSame(['someClass' => 'SomeClass::class'], $inject->getNamedArguments());
    }

    public function testWillAddPositionalArgument(): void
    {
        $inject = new Inject();
        $this->assertCount(0, $inject->getNamedArguments());
        $this->assertCount(0, $inject->getPositionalArguments());
        $inject->addArgument('SomeClass::class');
        $this->assertCount(0, $inject->getNamedArguments());
        $this->assertCount(1, $inject->getPositionalArguments());
        $this->assertSame(['SomeClass::class'], $inject->getPositionalArguments());
    }

    public function testWillRenderWhenUsingNamedArguments(): void
    {
        $inject = new Inject();
        $inject->addArgument('SomeClass::class', 'someClass');
        $inject->addArgument('OtherClass::class', 'otherClass');
        $this->assertSame('#[Inject(someClass: SomeClass::class, otherClass: OtherClass::class)]', $inject->render());
    }

    public function testWillRenderWhenUsingPositionalArguments(): void
    {
        $inject = new Inject();
        $inject->addArgument('SomeClass::class');
        $inject->addArgument('OtherClass::class');
        $this->assertSame(<<<BODY
#[Inject(
        SomeClass::class,
        OtherClass::class,
    )]
BODY, $inject->render());
    }

    public function testWillRenderEmptyAttributeWhenNoArgumentsProvided(): void
    {
        $inject = new Inject();
        $this->assertSame('#[Inject]', $inject->render());
    }
}
