<?php

declare(strict_types=1);

namespace Dot\Maker\Component;

use Dot\Maker\Component;
use Dot\Maker\Config\ComponentConfigInterface;
use Exception;

class HandlerComponent extends Component
{
    public const TYPE = 'Handler';
    public const STUB_NAME = 'handler.stub';

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
            ->addDependencyByFQCN('Mezzio\Hal\HalResponseFactory', 'ResponseFactory')
            ->addDependencyByFQCN('Mezzio\Hal\ResourceGenerator')
            ->addFactory();
    }

    /**
     * @throws Exception
     */
    protected function addFactory(): self
    {
        if (!$this->isAnnotated()) {
            $this->setFactory(
                (new FactoryComponent($this->getComponentConfig()))
                    ->setHandler($this)
                    ->init($this->getFactoryFqcn())
            );
        }

        return $this;
    }

    public function exposePlaceholders(): array
    {
        return [
            '{HANDLER_ANNOTATIONS}' => $this->parseAnnotations(),
            '{HANDLER_ARGUMENTS}' => $this->parseArguments(),
            '{HANDLER_PROPERTIES}' => $this->parseProperties(),
            '{HANDLER_PROPERTY_ASSIGNMENTS}' => $this->parsePropertyAssignments(),
            '{COMPONENT_USES}' => $this->parseUses(),
            '{COMPONENT_CONTAINER_GETS}' => $this->parseContainerGets(),
            '{COMPONENT_NAME}' => $this->getName(),
            '{HANDLER_FQCN}' => $this->getFqcn(),
            '{HANDLER_NAME}' => $this->getName(),
            '{HANDLER_NAMESPACE}' => $this->getNamespace(),
            '{HANDLER_PATH}' => $this->getPath(),
            '{HANDLER_PROPERTY}' => $this->getVariable()->getProperty(),
            '{HANDLER_VARIABLE}' => $this->getVariable()->getName(),
        ];
    }

    public function getPlaceholders(): array
    {
        return $this->exposePlaceholders();
    }
}
