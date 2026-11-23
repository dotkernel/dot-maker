<?php

declare(strict_types=1);

namespace Dot\Maker\Component;

use Dot\Maker\VisibilityEnum;

use function str_repeat;

/**
 * A class constant, such as "public const string NAME = 'value';".
 */
class Constant
{
    protected VisibilityEnum $visibility = VisibilityEnum::Public;
    protected string $type               = '';

    /**
     * @param string $value The value as PHP code, for example "'some:action'" for a string.
     */
    public function __construct(
        readonly public string $name,
        readonly public string $value,
    ) {
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getVisibility(): VisibilityEnum
    {
        return $this->visibility;
    }

    public function render(int $spaces = 0): string
    {
        $constant = str_repeat(' ', $spaces) . $this->visibility->value . ' const ';
        if ($this->type !== '') {
            $constant .= $this->type . ' ';
        }

        return $constant . $this->name . ' = ' . $this->value . ';';
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function setVisibility(VisibilityEnum $visibility): self
    {
        $this->visibility = $visibility;

        return $this;
    }
}
