<?php

declare(strict_types=1);

namespace Dot\Maker\Component;

use Dot\Maker\Component;
use Dot\Maker\VisibilityEnum;

use function count;
use function implode;
use function sprintf;
use function str_repeat;

use const PHP_EOL;

class Constructor
{
    private VisibilityEnum $visibility;
    private array $injects = [];
    /** @var Property[] $parameters */
    private array $parameters = [];
    /** @var Property[] $promotedProperties */
    private array $promotedProperties = [];

    private string $body = '';

    public function __construct()
    {
        $this->visibility = VisibilityEnum::Public;
    }

    public function addBody(string $body): self
    {
        $this->body = PHP_EOL . $body;

        return $this;
    }

    public function addBodyLine(string $bodyLine, int $spaces = 8): self
    {
        $this->body .= PHP_EOL . str_repeat(' ', $spaces) . $bodyLine;

        return $this;
    }

    public function addInject(string $inject): self
    {
        $this->injects[$inject] = $inject;

        return $this;
    }

    public function addParameter(
        Component $component,
        VisibilityEnum $visibility = VisibilityEnum::Protected,
        bool $nullable = false,
        mixed $default = null,
    ): self {
        $this->parameters[$component->getFqcn()] = new Property($component, $visibility, $nullable, $default);

        return $this;
    }

    public function addPromotedProperty(
        Component $component,
        VisibilityEnum $visibility = VisibilityEnum::Protected,
        bool $nullable = false,
        mixed $default = null,
    ): self {
        $this->promotedProperties[$component->getFqcn()] =
            new Property($component, $visibility, $nullable, $default, true);

        return $this;
    }

    public function render(): string
    {
        $injects = $this->renderInjects();
        if ($injects !== '') {
            $injects = <<<INJ
$injects
    
INJ;
        }

        $params = $this->renderParameters();
        if ($params !== '') {
            $params = PHP_EOL . $params . PHP_EOL . '    ';
            $body   = <<<BDY
 {{$this->body}
    }
BDY;
        } else {
            $body = <<<BDY

    {{$this->body}
    }
BDY;
        }

        return <<<CTR
$injects{$this->visibility->value} function __construct($params){$body}
CTR;
    }

    public function renderInjects(): string
    {
        if (count($this->injects) === 0) {
            return '';
        }

        $injects = ['#[Inject('];
        foreach ($this->injects as $inject) {
            $injects[] = sprintf('        %s,', $inject);
        }
        $injects[] = '    )]';

        return implode(PHP_EOL, $injects);
    }

    public function renderParameters(): string
    {
        if (count($this->parameters) === 0 && count($this->promotedProperties) === 0) {
            return '';
        }

        $parameters = [];
        foreach ($this->promotedProperties as $property) {
            $parameters[] = sprintf('        %s', $property->render());
        }
        foreach ($this->parameters as $parameter) {
            $parameters[] = sprintf('        %s', $parameter->render());
        }

        return implode(PHP_EOL, $parameters);
    }
}
