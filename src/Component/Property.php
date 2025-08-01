<?php

declare(strict_types=1);

namespace Dot\Maker\Component;

use Dot\Maker\VisibilityEnum;

use function str_repeat;

use const PHP_EOL;

class Property extends Parameter
{
    protected VisibilityEnum $visibility = VisibilityEnum::Protected;
    protected bool $promoted             = false;
    protected bool $readonly             = false;
    protected bool $static               = false;
    protected string $comment            = '';

    public function render(int $spaces = 0): string
    {
        $spaces = str_repeat(' ', $spaces);

        $property = $spaces;
        if ($this->comment !== '') {
            $property .= $this->comment . PHP_EOL . $spaces;
        }

        $property .= $this->visibility->value;
        if ($this->static === true) {
            $property .= ' static';
        }
        if ($this->readonly === true) {
            $property .= ' readonly';
        }
        $property .= ' ' . parent::render();

        if ($this->promoted === true) {
            return $property;
        }

        return $property . ';';
    }

    public function setComment(string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function setReadonly(bool $readonly): self
    {
        $this->readonly = $readonly;

        return $this;
    }

    public function setStatic(bool $static): self
    {
        $this->static = $static;

        return $this;
    }

    public function setVisibility(VisibilityEnum $visibility): self
    {
        $this->visibility = $visibility;

        return $this;
    }
}
