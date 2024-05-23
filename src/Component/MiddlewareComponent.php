<?php

declare(strict_types=1);

namespace Dot\Maker\Component;

use Dot\Maker\Component;
use Dot\Maker\Config\ComponentConfigInterface;
use Exception;

class MiddlewareComponent extends Component
{
    public const TYPE = 'Middleware';
    public const STUB_NAME = 'middleware.stub';

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
            ->checkAnnotatedServiceInjector()
            ->checkAnnotatedServiceFactory()
            ->checkType($fqcn)
            ->normalizeFqcn($fqcn)
            ->initModule($this->getFqcn())
            ->normalizeName($this->getFqcn())
            ->normalizeNamespace($this->getFqcn())
            ->normalizePath($this->getFqcn())
            ->initVariable($this->getName())
            ->checkDuplicateComponent($this->getPath())
            ->setAnnotated($annotated)
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
                    ->setMiddleware($this)
                    ->init($this->getFactoryFqcn())
            );
        }

        return $this;
    }

    public function exposePlaceholders(): array
    {
        return [
            '{MIDDLEWARE_ANNOTATIONS}' => $this->parseAnnotations(),
            '{MIDDLEWARE_ARGUMENTS}' => $this->parseArguments(),
            '{MIDDLEWARE_PROPERTIES}' => $this->parseProperties(),
            '{MIDDLEWARE_PROPERTY_ASSIGNMENTS}' => $this->parsePropertyAssignments(),
            '{COMPONENT_USES}' => $this->parseUses(),
            '{COMPONENT_CONTAINER_GETS}' => $this->parseContainerGets(),
            '{COMPONENT_NAME}' => $this->getName(),
            '{MIDDLEWARE_FQCN}' => $this->getFqcn(),
            '{MIDDLEWARE_NAME}' => $this->getName(),
            '{MIDDLEWARE_NAMESPACE}' => $this->getNamespace(),
            '{MIDDLEWARE_PATH}' => $this->getPath(),
            '{MIDDLEWARE_PROPERTY}' => $this->getVariable()->getProperty(),
            '{MIDDLEWARE_VARIABLE}' => $this->getVariable()->getName(),
        ];
    }

    public function getPlaceholders(): array
    {
        return $this->exposePlaceholders();
    }
}
