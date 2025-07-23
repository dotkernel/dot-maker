<?php

declare(strict_types=1);

namespace Dot\Maker\Component;

use Dot\Maker\VisibilityEnum;

use function array_map;
use function count;
use function implode;
use function sprintf;
use function str_repeat;

use const PHP_EOL;

class Method
{
    protected VisibilityEnum $visibility = VisibilityEnum::Public;
    protected string $body               = '';
    protected string $returnType         = 'void';
    protected bool $nullable             = false;
    /** @var ParameterInterface[] $parameters */
    protected array $parameters = [];
    /** @var Inject[] $injects */
    protected array $injects = [];

    public function __construct(
        public readonly string $name,
    ) {
    }

    public function __toString(): string
    {
        return $this->render();
    }

    public function addBodyLine(string $bodyLine, int $spaces = 8): self
    {
        $this->body .= PHP_EOL . str_repeat(' ', $spaces) . $bodyLine;

        return $this;
    }

    public function addInject(Inject $inject): self
    {
        $this->injects[] = $inject;

        return $this;
    }

    public function addParameter(Parameter $parameter): self
    {
        $this->parameters[] = $parameter;

        return $this;
    }

    public function render(): string
    {
        $signature = $this->renderSignature();

        if (count($this->parameters) > 0) {
            if ($this->body !== '') {
                $method = $this->renderWithParamsWithBody($signature);
            } else {
                $method = $this->renderWithParamsWithoutBody($signature);
            }
        } else {
            if ($this->body !== '') {
                $method = $this->renderWithoutParamsWithBody($signature);
            } else {
                $method = $this->renderWithoutParamsWithoutBody($signature);
            }
        }

        $injects = $this->renderInjects();
        if ($injects !== '') {
            return $injects . PHP_EOL . '    ' . $method;
        }

        return $method;
    }

    private function renderWithParamsWithBody(string $signature): string
    {
        return <<<MTD
{$this->visibility->value} function $this->name(
{$this->renderParameters(8)}
    )$signature {{$this->body}
    }
MTD;
    }

    private function renderWithParamsWithoutBody(string $signature): string
    {
        return <<<MTD
{$this->visibility->value} function $this->name(
{$this->renderParameters(8)}
    )$signature {
    }
MTD;
    }

    private function renderWithoutParamsWithBody(string $signature): string
    {
        return <<<MTD
{$this->visibility->value} function $this->name()$signature
    {{$this->body}
    }
MTD;
    }

    private function renderWithoutParamsWithoutBody(string $signature): string
    {
        return <<<MTD
{$this->visibility->value} function $this->name()$signature
    {
    }
MTD;
    }

    public function renderInjects(): string
    {
        if (count($this->injects) === 0) {
            return '';
        }

        $injects = array_map(fn (Inject $inject): string => $inject->render(), $this->injects);

        return implode(PHP_EOL, $injects);
    }

    protected function renderParameters(int $spaces = 0): string
    {
        if (count($this->parameters) === 0) {
            return '';
        }

        return implode(PHP_EOL, array_map(
            fn (Parameter $parameter) => sprintf('%s%s,', str_repeat(' ', $spaces), $parameter->render()),
            $this->parameters
        ));
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

    public function setBody(string $body): self
    {
        $this->body = PHP_EOL . $body;

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

    public function setVisibility(VisibilityEnum $visibility): self
    {
        $this->visibility = $visibility;

        return $this;
    }
}
