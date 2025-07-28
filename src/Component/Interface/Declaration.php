<?php

declare(strict_types=1);

namespace Dot\Maker\Component\Interface;

use Dot\Maker\Component\ParameterInterface;
use Dot\Maker\VisibilityEnum;

use function array_map;
use function count;
use function implode;
use function sprintf;

use const PHP_EOL;

class Declaration
{
    protected VisibilityEnum $visibility = VisibilityEnum::Public;
    /** @var ParameterInterface[] $parameters */
    protected array $parameters  = [];
    protected bool $nullable     = false;
    protected string $returnType = 'void';

    public function __construct(
        readonly public string $name,
    ) {
    }

    public function __toString(): string
    {
        return $this->render();
    }

    public function addParameter(ParameterInterface $parameter): self
    {
        $this->parameters[] = $parameter;

        return $this;
    }

    public function render(): string
    {
        return count($this->parameters) > 0 ? $this->renderWithParams() : $this->renderWithoutParams();
    }

    public function renderParameters(): string
    {
        if (count($this->parameters) === 0) {
            return '';
        }

        return implode(PHP_EOL, array_map(
            fn (ParameterInterface $parameter): string => sprintf('        %s,', $parameter->render()),
            $this->parameters
        ));
    }

    private function renderWithParams(): string
    {
        return <<<DEC
{$this->visibility->value} function $this->name(
{$this->renderParameters()}
    ){$this->renderSignature()};
DEC;
    }

    private function renderWithoutParams(): string
    {
        return <<<DEC
{$this->visibility->value} function $this->name(){$this->renderSignature()};
DEC;
    }

    public function renderSignature(): string
    {
        if ($this->returnType === '') {
            return '';
        }

        if ($this->returnType === 'void') {
            return ': void';
        }

        if ($this->nullable === true) {
            return sprintf(': ?%s', $this->returnType);
        }

        return sprintf(': %s', $this->returnType);
    }

    public function setVisibility(VisibilityEnum $visibility): self
    {
        $this->visibility = $visibility;

        return $this;
    }

    public function setNullable(bool $nullable): self
    {
        $this->nullable = $nullable;

        return $this;
    }

    public function setReturnType(string $returnType): self
    {
        $this->returnType = $returnType;

        return $this;
    }
}
