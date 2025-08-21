<?php

declare(strict_types=1);

namespace Component;

use Dot\Maker\Component\Property;
use Dot\Maker\VisibilityEnum;
use PHPUnit\Framework\TestCase;

class PropertyTest extends TestCase
{
    public function testWillInstantiate(): void
    {
        $property = new Property('property', 'string');
        $this->assertContainsOnlyInstancesOf(Property::class, [$property]);
    }

    public function testWillSetVisibility(): void
    {
        $property = new Property('property', 'string');
        $this->assertSame(VisibilityEnum::Protected, $property->getVisibility());
        $property->setVisibility(VisibilityEnum::Public);
        $this->assertSame(VisibilityEnum::Public, $property->getVisibility());
        $property->setVisibility(VisibilityEnum::Private);
        $this->assertSame(VisibilityEnum::Private, $property->getVisibility());
    }

    public function testWillSetReadonly(): void
    {
        $property = new Property('property', 'string');
        $this->assertFalse($property->isReadonly());
        $property->setReadonly(true);
        $this->assertTrue($property->isReadonly());
    }

    public function testWillSetStatic(): void
    {
        $property = new Property('property', 'string');
        $this->assertFalse($property->isStatic());
        $property->setStatic(true);
        $this->assertTrue($property->isStatic());
    }

    public function testWillSetComment(): void
    {
        $comment = <<<COMM
/**
 * This is a comment
 */
COMM;

        $property = new Property('property', 'string');
        $this->assertSame('', $property->getComment());
        $property->setComment($comment);
        $this->assertSame($comment, $property->getComment());
    }

    public function testWillRender(): void
    {
        $comment = <<<COMM
/**
 * This is a comment
 */
COMM;

        $property = (new Property('property', 'string', true, '"test"'))
            ->setVisibility(VisibilityEnum::Public)
            ->setReadonly(true)
            ->setStatic(true)
            ->setComment($comment);
        $this->assertSame($this->dataProviderRenderedProperty(), $property->render());
    }

    private function dataProviderRenderedProperty(): string
    {
        return <<<BODY
/**
 * This is a comment
 */
public static readonly ?string \$property = "test";
BODY;
    }
}
