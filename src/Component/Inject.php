<?php

declare(strict_types=1);

namespace Dot\Maker\Component;

use function array_map;
use function count;
use function implode;
use function sprintf;
use function str_repeat;

use const PHP_EOL;

class Inject
{
    private array $namedArguments      = [];
    private array $positionalArguments = [];

    public function __construct(
        public readonly string $name = 'Inject',
    ) {
    }

    public function addArgument(string $argument, ?string $name = null): self
    {
        if ($name !== null) {
            $this->namedArguments[$name] = $argument;
        } else {
            $this->positionalArguments[] = $argument;
        }

        return $this;
    }

    public function render(int $spaces = 4): string
    {
        if (count($this->namedArguments) > 0) {
            return $this->renderWithNamedArguments();
        }
        if (count($this->positionalArguments) > 0) {
            return $this->renderWithPositionalArguments($spaces);
        }

        return sprintf('#[%s]', $this->name);
    }

    private function renderWithNamedArguments(): string
    {
        $arguments = [];

        foreach ($this->namedArguments as $name => $argument) {
            $arguments[] = sprintf('%s: %s', $name, $argument);
        }

        return sprintf('#[%s(%s)]', $this->name, implode(', ', $arguments));
    }

    private function renderWithPositionalArguments(int $spaces = 4): string
    {
        $arguments = array_map(
            fn (string $argument): string => sprintf('%s%s%s,', PHP_EOL, str_repeat(' ', $spaces + 4), $argument),
            $this->positionalArguments
        );

        $arguments[] = PHP_EOL;

        return sprintf('#[%s(%s%s)]', $this->name, implode('', $arguments), str_repeat(' ', $spaces));
    }
}
