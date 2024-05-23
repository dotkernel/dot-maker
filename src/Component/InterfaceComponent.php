<?php

declare(strict_types=1);

namespace Dot\Maker\Component;

use Dot\Maker\Component;
use Dot\Maker\Config\ComponentConfigInterface;
use Exception;

class InterfaceComponent extends Component
{
    public const TYPE = 'Interface';
    public const STUB_NAME = 'interface.stub';

    public function __construct(ComponentConfigInterface $componentConfig)
    {
        parent::__construct($componentConfig);
        $this->setType(self::TYPE);
    }

    /**
     * @throws Exception
     */
    public function init(string $fqcn): self
    {
        return $this
            ->checkType($fqcn)
            ->normalizeFqcn($fqcn)
            ->initModule($this->getFqcn())
            ->normalizeName($this->getFqcn())
            ->normalizeNamespace($this->getFqcn())
            ->normalizePath($this->getFqcn())
            ->initVariable($this->getName())
            ->checkDuplicateComponent($this->getPath());
    }

    public function exposePlaceholders(): array
    {
        return [
            '{INTERFACE_FQCN}' => $this->getFqcn(),
            '{INTERFACE_NAME}' => $this->getName(),
            '{INTERFACE_NAMESPACE}' => $this->getNamespace(),
            '{INTERFACE_PATH}' => $this->getPath(),
            '{INTERFACE_PROPERTY}' => $this->getVariable()->getProperty(),
            '{INTERFACE_VARIABLE}' => $this->getVariable()->getName(),
        ];
    }

    public function getPlaceholders(): array
    {
        return $this->exposePlaceholders();
    }
}
