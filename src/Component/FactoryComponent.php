<?php

declare(strict_types=1);

namespace Dot\Maker\Component;

use Dot\Maker\Component;
use Dot\Maker\Config\ComponentConfigInterface;
use Exception;

class FactoryComponent extends Component
{
    public const TYPE = 'Factory';
    public const STUB_NAME = 'factory.stub';

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
            '{FACTORY_FQCN}' => $this->getFqcn(),
            '{FACTORY_NAME}' => $this->getName(),
            '{FACTORY_NAMESPACE}' => $this->getNamespace(),
            '{FACTORY_PATH}' => $this->getPath(),
            '{FACTORY_PROPERTY}' => $this->getVariable()->getProperty(),
            '{FACTORY_VARIABLE}' => $this->getVariable()->getName(),
        ];
    }

    public function getPlaceholders(): array
    {
        $placeholders = $this->exposePlaceholders();

        if ($this->hasCommand()) {
            $placeholders = array_merge($this->getCommand()->useSelf()->exposePlaceholders(), $placeholders);
        }

        if ($this->hasEntity()) {
            $placeholders = array_merge($this->getEntity()->useSelf()->exposePlaceholders(), $placeholders);
        }

        if ($this->hasHandler()) {
            $placeholders = array_merge($this->getHandler()->useSelf()->exposePlaceholders(), $placeholders);
        }

        if ($this->hasInterface()) {
            $placeholders = array_merge($this->getInterface()->useSelf()->exposePlaceholders(), $placeholders);
        }

        if ($this->hasMiddleware()) {
            $placeholders = array_merge($this->getMiddleware()->useSelf()->exposePlaceholders(), $placeholders);
        }

        if ($this->hasRepository()) {
            $placeholders = array_merge($this->getRepository()->useSelf()->exposePlaceholders(), $placeholders);
        }

        if ($this->hasService()) {
            $placeholders = array_merge($this->getService()->useSelf()->exposePlaceholders(), $placeholders);
        }

        return $placeholders;
    }
}
