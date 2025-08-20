<?php

declare(strict_types=1);

namespace Component\Method;

use Dot\Maker\Component\Inject;
use Dot\Maker\Component\Method\Constructor;
use Dot\Maker\Component\Parameter;
use Dot\Maker\VisibilityEnum;
use PHPUnit\Framework\TestCase;

class ConstructorTest extends TestCase
{
    public function testWillInstantiate(): void
    {
        $constructor = new Constructor();
        $this->assertContainsOnlyInstancesOf(Constructor::class, [$constructor]);
        $this->assertSame('__construct', $constructor->getName());
        $this->assertSame('__construct', $constructor->name);
    }

    public function testCanNotChangeNullable(): void
    {
        $constructor = new Constructor();
        $this->assertFalse($constructor->isNullable());
        $constructor->setNullable(true);
        $this->assertFalse($constructor->isNullable());
    }

    public function testCanNotChangeReturnType(): void
    {
        $constructor = new Constructor();
        $this->assertSame('', $constructor->getReturnType());
        $constructor->setReturnType('self');
        $this->assertSame('', $constructor->getReturnType());
    }

    public function testWillRender(): void
    {
        $constructor = (new Constructor())
            ->setVisibility(VisibilityEnum::Private)
            ->setComment(<<<COMM
/**
     * This is a comment
     */
COMM)
            ->addInject(
                (new Inject())
                    ->addArgument('SomeClass::class')
                    ->addArgument('\'config.test\'')
            )
            ->addParameter(
                new Parameter('someClass', 'SomeClass')
            )
            ->addParameter(
                new Parameter('config', 'array', false, '[]')
            )
            ->setBody('        // do stuff');

        $this->assertSame($this->dataProviderRenderedMethod(), $constructor->render());
    }

    private function dataProviderRenderedMethod(): string
    {
        return <<<BODY
/**
     * This is a comment
     */
    #[Inject(
        SomeClass::class,
        'config.test',
    )]
    private function __construct(
        SomeClass \$someClass,
        array \$config = [],
    ) {
        // do stuff
    }
BODY;
    }
}
