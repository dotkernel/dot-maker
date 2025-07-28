<?php

declare(strict_types=1);

namespace Dot\Maker\Component;

use Dot\Maker\VisibilityEnum;

use function array_map;
use function count;
use function implode;
use function sprintf;
use function str_repeat;
use function str_replace;

use const PHP_EOL;

class Method implements MethodInterface
{
    protected VisibilityEnum $visibility = VisibilityEnum::Public;
    /** @var ParameterInterface[] $parameters */
    protected array $parameters = [];
    /** @var Inject[] $injects */
    protected array $injects     = [];
    protected string $body       = '';
    protected string $comment    = '';
    protected string $returnType = 'void';
    protected bool $nullable     = false;

    public function __construct(
        public readonly string $name,
    ) {
    }

    public function __toString(): string
    {
        return $this->render();
    }

    public function appendBody(string $bodyLine, int $spaces = 8): self
    {
        $this->body .= PHP_EOL . str_repeat(' ', $spaces) . $bodyLine;

        return $this;
    }

    public function addInject(Inject $inject): self
    {
        $this->injects[] = $inject;

        return $this;
    }

    public function addParameter(ParameterInterface $parameter): self
    {
        $this->parameters[] = $parameter;

        return $this;
    }

    public function commentBody(): self
    {
        $this->body = str_replace("\n", "\n// ", $this->body);

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function prependBody(string $bodyLine, int $spaces = 8): self
    {
        $this->body = PHP_EOL . str_repeat(' ', $spaces) . $bodyLine . $this->body;

        return $this;
    }

    public function render(): string
    {
        if (count($this->parameters) > 0) {
            if ($this->body !== '') {
                $method = $this->renderWithParamsWithBody();
            } else {
                $method = $this->renderWithParamsWithoutBody();
            }
        } else {
            if ($this->body !== '') {
                $method = $this->renderWithoutParamsWithBody();
            } else {
                $method = $this->renderWithoutParamsWithoutBody();
            }
        }

        $injects = $this->renderInjects();
        if ($injects !== '') {
            $method = $injects . PHP_EOL . '    ' . $method;
        }
        if ($this->comment !== '') {
            $method = $this->comment . PHP_EOL . '    ' . $method;
        }

        return $method;
    }

    public function renderWithParamsWithBody(): string
    {
        return <<<MTD
{$this->visibility->value} function $this->name(
{$this->renderParameters(8)}
    ){$this->renderSignature()} {{$this->body}
    }
MTD;
    }

    public function renderWithParamsWithoutBody(): string
    {
        return <<<MTD
{$this->visibility->value} function $this->name(
{$this->renderParameters(8)}
    ){$this->renderSignature()} {
    }
MTD;
    }

    public function renderWithoutParamsWithBody(): string
    {
        return <<<MTD
{$this->visibility->value} function $this->name(){$this->renderSignature()}
    {{$this->body}
    }
MTD;
    }

    public function renderWithoutParamsWithoutBody(): string
    {
        return <<<MTD
{$this->visibility->value} function $this->name(){$this->renderSignature()}
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

    public function renderParameters(int $spaces = 0): string
    {
        if (count($this->parameters) === 0) {
            return '';
        }

        return implode(PHP_EOL, array_map(
            fn (ParameterInterface $parameter): string => sprintf(
                '%s%s,',
                str_repeat(' ', $spaces),
                $parameter->render()
            ),
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

    public function setComment(string $comment): self
    {
        $this->comment = $comment;

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
