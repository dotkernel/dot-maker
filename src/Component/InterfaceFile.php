<?php

declare(strict_types=1);

namespace Dot\Maker\Component;

use Dot\Maker\Component\Interface\Declaration;

use function array_map;
use function count;
use function implode;
use function sort;
use function sprintf;

use const PHP_EOL;

class InterfaceFile
{
    /** @var Declaration[] $declarations */
    private array $declarations = [];
    private array $classUses    = [];
    private array $constantUses = [];
    private array $functionUses = [];
    private array $interfaces   = [];
    private string $comment     = '';
    private bool $strictTypes   = true;

    public function __construct(
        readonly public string $namespace,
        readonly public string $interfaceName,
    ) {
    }

    public function __toString(): string
    {
        return $this->render();
    }

    public function addDeclaration(Declaration $declaration): self
    {
        $this->declarations[$declaration->name] = $declaration;

        return $this;
    }

    public function addInterface(string $interface): self
    {
        $this->interfaces[$interface] = $interface;

        return $this;
    }

    public function useClass(string $use, ?string $alias = null): self
    {
        if ($alias !== null) {
            $use = sprintf('use %s as %s;', $use, $alias);
        } else {
            $use = sprintf('use %s;', $use);
        }

        $this->classUses[$use] = $use;

        return $this;
    }

    public function useFunction(string $use): self
    {
        $use = sprintf('use function %s;', $use);

        $this->functionUses[$use] = $use;

        return $this;
    }

    public function useConstant(string $use): self
    {
        $use = sprintf('use const %s;', $use);

        $this->constantUses[$use] = $use;

        return $this;
    }

    public function render(): string
    {
        $interface = '<?php' . PHP_EOL;

        if ($this->strictTypes) {
            $interface .= PHP_EOL . 'declare(strict_types=1);' . PHP_EOL;
        }

        $interface .= PHP_EOL . 'namespace ' . $this->namespace . ';' . PHP_EOL;

        sort($this->classUses);
        if (count($this->classUses) > 0) {
            foreach ($this->classUses as $use) {
                $interface .= PHP_EOL . $use;
            }
            $interface .= PHP_EOL;
        }

        sort($this->functionUses);
        if (count($this->functionUses) > 0) {
            foreach ($this->functionUses as $use) {
                $interface .= PHP_EOL . $use;
            }
            $interface .= PHP_EOL;
        }

        sort($this->constantUses);
        if (count($this->constantUses) > 0) {
            foreach ($this->constantUses as $use) {
                $interface .= PHP_EOL . $use;
            }
            $interface .= PHP_EOL;
        }

        if ($this->comment !== '') {
            $interface .= PHP_EOL . $this->comment . PHP_EOL;
        }

        $interface .= PHP_EOL;
        $interface .= sprintf('interface %s', $this->interfaceName);
        if (count($this->interfaces) > 0) {
            $interface .= sprintf(' extends %s', implode(', ', $this->interfaces));
        }
        $interface .= PHP_EOL;
        $interface .= '{' . PHP_EOL;

        if (count($this->declarations) > 0) {
            $declarations = array_map(
                fn (Declaration $declaration): string => $declaration->render(),
                $this->declarations
            );

            $interface .= '    ' . implode(PHP_EOL . PHP_EOL . '    ', $declarations) . PHP_EOL;
        }

        $interface .= '}' . PHP_EOL;

        return $interface;
    }

    public function setComment(string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function setStrictTypes(bool $strictTypes): self
    {
        $this->strictTypes = $strictTypes;

        return $this;
    }
}
