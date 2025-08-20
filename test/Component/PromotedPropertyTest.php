<?php

declare(strict_types=1);

namespace Component;

use Dot\Maker\Component\PromotedProperty;
use Dot\Maker\VisibilityEnum;
use PHPUnit\Framework\TestCase;

class PromotedPropertyTest extends TestCase
{
    public function testWillInstantiate(): void
    {
        $promotedProperty = new PromotedProperty('property', 'string');
        $this->assertContainsOnlyInstancesOf(PromotedProperty::class, [$promotedProperty]);
    }

    public function testWillRender(): void
    {
        $comment = <<<COMM
/**
 * This is a comment
 */
COMM;

        $promotedProperty = (new PromotedProperty('property', 'string', true, '"test"'))
            ->setVisibility(VisibilityEnum::Public)
            ->setReadonly(true)
            ->setStatic(true)
            ->setComment($comment);
        $this->assertSame($this->dataProviderRenderedPromotedProperty(), $promotedProperty->render());
    }

    private function dataProviderRenderedPromotedProperty(): string
    {
        return <<<BODY
/**
 * This is a comment
 */
public static readonly ?string \$property = "test"
BODY;
    }
}
