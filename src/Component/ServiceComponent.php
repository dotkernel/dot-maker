<?php

declare(strict_types=1);

namespace Dot\Maker\Component;

use Dot\Maker\Component;
use Dot\Maker\Config\ComponentConfigInterface;
use Exception;

class ServiceComponent extends Component
{
    public const TYPE = 'Service';
    public const STUB_NAME = 'service.stub';

    public function __construct(ComponentConfigInterface $componentConfig)
    {
        parent::__construct($componentConfig);
        $this->setType(self::TYPE);
    }

    /**
     * @throws Exception
     */
    public function init(string $fqcn, bool $annotated = false): self
    {
        return $this
            ->checkAnnotatedServiceInjector($annotated)
            ->checkAnnotatedServiceFactory($annotated)
            ->checkType($fqcn)
            ->normalizeFqcn($fqcn)
            ->initModule($this->getFqcn())
            ->normalizeName($this->getFqcn())
            ->normalizeNamespace($this->getFqcn())
            ->normalizePath($this->getFqcn())
            ->initVariable($this->getName())
            ->checkDuplicateComponent($this->getPath())
            ->setAnnotated($annotated)
            ->addInterface()
            ->addFactory();
    }

    /**
     * @throws Exception
     */
    protected function addInterface(): self
    {
        return $this->setInterface(
            (new InterfaceComponent($this->getComponentConfig()))
                ->setService($this)
                ->init($this->getInterfaceFqcn())
        );
    }

    /**
     * @throws Exception
     */
    protected function addFactory(): self
    {
        if (!$this->isAnnotated()) {
            $this->setFactory(
                (new FactoryComponent($this->getComponentConfig()))
                    ->setService($this)
                    ->setInterface($this->getInterface())
                    ->init($this->getFactoryFqcn())
            );
        }

        return $this;
    }

    public function exposePlaceholders(): array
    {
        return [
            '{SERVICE_ANNOTATIONS}' => $this->parseAnnotations(),
            '{SERVICE_ARGUMENTS}' => $this->parseArguments(),
            '{SERVICE_PROPERTIES}' => $this->parseProperties(),
            '{SERVICE_PROPERTY_ASSIGNMENTS}' => $this->parsePropertyAssignments(),
            '{COMPONENT_USES}' => $this->parseUses(),
            '{COMPONENT_CONTAINER_GETS}' => $this->parseContainerGets(),
            '{COMPONENT_NAME}' => $this->getName(),
            '{SERVICE_FQCN}' => $this->getFqcn(),
            '{SERVICE_NAME}' => $this->getName(),
            '{SERVICE_NAMESPACE}' => $this->getNamespace(),
            '{SERVICE_PATH}' => $this->getPath(),
            '{SERVICE_PROPERTY}' => $this->getVariable()->getProperty(),
            '{SERVICE_VARIABLE}' => $this->getVariable()->getName(),
        ];
    }

    public function getPlaceholders(): array
    {
        $placeholders = $this->exposePlaceholders();

        if ($this->hasInterface()) {
            $placeholders = array_merge($this->getInterface()->exposePlaceholders(), $placeholders);
        }

        return $placeholders;
    }
}
