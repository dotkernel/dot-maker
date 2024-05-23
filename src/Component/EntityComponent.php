<?php

declare(strict_types=1);

namespace Dot\Maker\Component;

use Dot\Maker\Component;
use Dot\Maker\Component\Entity\Relation\RelationInterface;
use Dot\Maker\Component\Entity\Type\FieldInterface;
use Dot\Maker\Config\ComponentConfigInterface;
use Exception;

class EntityComponent extends Component
{
    public const TYPE = 'Entity';
    public const STUB_NAME = 'entity.stub';
    protected array $relations = [];
    protected array $fields = [];

    public function __construct(ComponentConfigInterface $componentConfig)
    {
        parent::__construct($componentConfig);
        $this->setType(self::TYPE);
    }

    public function addRelation(RelationInterface $relation): self
    {
        $this->relations[$relation->getName()] = $relation;

        return $this;
    }

    /**
     * @return RelationInterface[]
     */
    public function getRelations(): array
    {
        return $this->relations;
    }

    public function hasRelation(string $name): bool
    {
        return array_key_exists($name, $this->relations);
    }

    public function hasRelations(): bool
    {
        return count($this->relations) > 0;
    }

    public function setRelations(array $relations): self
    {
        $this->relations = $relations;

        return $this;
    }

    public function addField(FieldInterface $field): self
    {
        $this->fields[$field->getName()] = $field;

        return $this;
    }

    /**
     * @return FieldInterface[]
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    public function hasField(string $name): bool
    {
        return array_key_exists($name, $this->fields);
    }

    public function hasFields(): bool
    {
        return count($this->fields) > 0;
    }

    public function setFields(array $fields): self
    {
        $this->fields = $fields;

        return $this;
    }

    public function hasProperty(string $name): bool
    {
        return array_key_exists($name, $this->fields) || array_key_exists($name, $this->relations);
    }

    public function hasProperties(): bool
    {
        return $this->hasRelations() || $this->hasFields();
    }

    /**
     * @throws Exception
     */
    public function init(string $fqcn, bool $annotated = false): self
    {
        return $this
            ->checkAnnotatedEntityInjector($annotated)
            ->checkAnnotatedRepositoryFactory($annotated)
            ->checkType($fqcn)
            ->normalizeFqcn($fqcn)
            ->initModule($this->getFqcn())
            ->normalizeName($this->getFqcn())
            ->normalizeNamespace($this->getFqcn())
            ->normalizePath($this->getFqcn())
            ->initVariable($this->getName())
//            ->checkDuplicateComponent($this->getPath())
            ->setAnnotated($annotated)
            ->addRepository();
    }

    /**
     * @throws Exception
     */
    protected function addRepository(): self
    {
        return $this->setRepository(
            (new RepositoryComponent($this->getComponentConfig()))
                ->setEntity($this)
                ->init($this->getRepositoryFqcn(), $this->isAnnotated())
        );
    }

    public function exposePlaceholders(): array
    {
        return [
            '{ENTITY_ACCESSORS}' => $this->parseAccessors(),
            '{ENTITY_ARGUMENTS}' => $this->parseArguments(),
            '{ENTITY_GET_ARRAY_COPY}' => $this->parseGetArrayCopy(),
            '{ENTITY_PROPERTIES}' => $this->parseProperties(),
            '{ENTITY_PROPERTY_ASSIGNMENTS}' => $this->parsePropertyAssignments(),
            '{COMPONENT_USES}' => $this->parseUses(),
            '{COMPONENT_CONTAINER_GETS}' => $this->parseContainerGets(),
            '{COMPONENT_NAME}' => $this->getName(),
            '{ENTITY_FQCN}' => $this->getFqcn(),
            '{ENTITY_NAME}' => $this->getName(),
            '{ENTITY_NAMESPACE}' => $this->getNamespace(),
            '{ENTITY_PATH}' => $this->getPath(),
            '{ENTITY_PROPERTY}' => $this->getVariable()->getProperty(),
            '{ENTITY_VARIABLE}' => $this->getVariable()->getName(),
            '{ENTITY_TABLE_NAME}' => $this->getTableName(),
        ];
    }

    /**
     * @throws Exception
     */
    protected function parseAccessors(): string
    {
        if (!$this->hasProperties()) {
            return '';
        }

        $accessors = [];
        foreach ($this->getRelations() as $relation) {
            if (class_exists($relation->getTargetEntity()) || interface_exists($relation->getTargetEntity())) {
                $this->addUse($relation->getTargetEntity());
            }

            $accessors[] = <<<GET

    public function {$relation->getVariable()->getGetter()}(): ?{$relation->getPhpType()}
    {
        return \$this->{$relation->getVariable()->getProperty()};
    }
GET;
            $accessors[] = <<<GET

    public function {$relation->getVariable()->getSetter()}({$relation->getPhpType()} {$relation->getVariable()->getName()}): self
    {
        \$this->{$relation->getVariable()->getProperty()} = {$relation->getVariable()->getName()};

        return \$this;
    }
GET;
        }

        foreach ($this->getFields() as $field) {
            if (class_exists($field->getPhpType()) || interface_exists($field->getPhpType())) {
                $this->addUse($field->getPhpType());
            }

            $accessors[] = <<<GET

    public function {$field->getVariable()->getGetter()}(): ?{$field->getPhpType()}
    {
        return \$this->{$field->getVariable()->getProperty()};
    }
GET;
            $accessors[] = <<<GET

    public function {$field->getVariable()->getSetter()}({$field->getPhpType()} {$field->getVariableName()}): self
    {
        \$this->{$field->getVariable()->getProperty()} = {$field->getVariableName()};

        return \$this;
    }
GET;
        }

        return PHP_EOL . implode(PHP_EOL, $accessors);
    }

    public function parseGetArrayCopy(): string
    {
        if (!$this->hasProperties()) {
            return '';
        }

        $items = [];
        foreach ($this->getRelations() as $relation) {
            // @codingStandardsIgnoreStart
            $items[] = <<<ITEM
            '{$relation->getVariable()->getProperty()}' => \$this->{$relation->getVariable()->getGetter()}()->getArrayCopy(),
ITEM;
            // @codingStandardsIgnoreEnd
        }
        foreach ($this->getFields() as $field) {
            $items[] = <<<ITEM
            '{$field->getVariable()->getProperty()}' => \$this->{$field->getVariable()->getGetter()}(),
ITEM;
        }

        return PHP_EOL . implode(PHP_EOL, $items);
    }

    protected function parseProperties(): string
    {
        if (!$this->hasProperties()) {
            return '';
        }

        $properties = [];

        foreach ($this->getRelations() as $relation) {
//            if (class_exists($relation->getTargetEntity()) || interface_exists($relation->getTargetEntity())) {
//                $this->addUse($relation->getTargetEntity());
//            }

            $properties[] = <<<PROP
    {$relation->getDefinition()}
    protected {$relation->getPhpType()} {$relation->getVariable()->getName()};

PROP;
        }

        foreach ($this->getFields() as $field) {
            $properties[] = <<<PROP
    {$field->getDefinition()}
    protected ?{$field->getPhpType()} {$field->getVariableName()};

PROP;
        }

        return PHP_EOL . implode(PHP_EOL, $properties);
    }

    public function getPlaceholders(): array
    {
        $placeholders = $this->exposePlaceholders();

        if ($this->hasRepository()) {
            $placeholders = array_merge($this->getRepository()->useSelf()->exposePlaceholders(), $placeholders);
        }

        return $placeholders;
    }

    protected function getTableName(): string
    {
        $parts = preg_split('/(?=[A-Z])/', $this->getName(), -1, PREG_SPLIT_NO_EMPTY);
        array_pop($parts);
        $parts = array_map('strtolower', $parts);

        return implode('_', $parts);
    }
}
