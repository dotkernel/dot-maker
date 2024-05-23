<?php

declare(strict_types=1);

namespace Dot\Maker\Component;

use Dot\Maker\Component;
use Dot\Maker\Config\ComponentConfigInterface;
use Exception;

class RepositoryComponent extends Component
{
    public const TYPE = 'Repository';
    public const STUB_NAME = 'repository.stub';

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
            ->checkAnnotatedRepositoryFactory($annotated)
            ->checkType($fqcn)
            ->normalizeFqcn($fqcn)
            ->initModule($this->getFqcn())
            ->normalizeName($this->getFqcn())
            ->normalizeNamespace($this->getFqcn())
            ->normalizePath($this->getFqcn())
            ->initVariable($this->getName())
            ->checkDuplicateComponent($this->getPath())
            ->setAnnotated($annotated);
    }

    public function exposePlaceholders(): array
    {
        return [
            '{REPOSITORY_FQCN}' => $this->getFqcn(),
            '{REPOSITORY_NAME}' => $this->getName(),
            '{REPOSITORY_NAMESPACE}' => $this->getNamespace(),
            '{REPOSITORY_PATH}' => $this->getPath(),
            '{REPOSITORY_PROPERTY}' => $this->getVariable()->getProperty(),
            '{REPOSITORY_VARIABLE}' => $this->getVariable()->getName(),
        ];
    }

    public function getPlaceholders(): array
    {
        $placeholders = $this->exposePlaceholders();

        if ($this->hasEntity()) {
            $placeholders = array_merge($this->getEntity()->useSelf()->exposePlaceholders(), $placeholders);
        }

        if ($this->hasFactory()) {
            $placeholders = array_merge($this->getFactory()->useSelf()->exposePlaceholders(), $placeholders);
        }

        return $placeholders;
    }
}
