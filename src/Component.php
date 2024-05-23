<?php

declare(strict_types=1);

namespace Dot\Maker;

use Dot\Maker\Component\CommandComponent;
use Dot\Maker\Component\EntityComponent;
use Dot\Maker\Component\FactoryComponent;
use Dot\Maker\Component\HandlerComponent;
use Dot\Maker\Component\InterfaceComponent;
use Dot\Maker\Component\MiddlewareComponent;
use Dot\Maker\Component\RepositoryComponent;
use Dot\Maker\Component\ServiceComponent;
use Dot\Maker\Config\ComponentConfigInterface;
use Dot\Maker\Exception\DuplicateComponentException;
use Exception;
use Laminas\Code\Generator\ClassGenerator;
use Symfony\Component\Console\Style\StyleInterface;

abstract class Component implements ComponentInterface
{
    protected ClassGenerator $generator;
    protected ComponentConfigInterface $componentConfig;
    protected Module $module;
    protected Variable $variable;
    protected ?CommandComponent $command = null;
    protected ?EntityComponent $entity = null;
    protected ?FactoryComponent $factory = null;
    protected ?HandlerComponent $handler = null;
    protected ?InterfaceComponent $interface = null;
    protected ?MiddlewareComponent $middleware = null;
    protected ?RepositoryComponent $repository = null;
    protected ?ServiceComponent $service = null;
    protected ?string $body = null;
    protected ?string $fqcn = null;
    protected ?string $name = null;
    protected ?string $namespace = null;
    protected ?string $path = null;
    protected ?string $type = null;
    protected bool $annotated = false;
    protected array $dependencies = [];
    protected array $uses = [];
    protected array $types = [
        CommandComponent::TYPE,
        EntityComponent::TYPE,
        FactoryComponent::TYPE,
        HandlerComponent::TYPE,
        InterfaceComponent::TYPE,
        MiddlewareComponent::TYPE,
        RepositoryComponent::TYPE,
        ServiceComponent::TYPE,
    ];

    public function __construct(ComponentConfigInterface $componentConfig)
    {
        $this->generator = new ClassGenerator();
        $this->setComponentConfig($componentConfig);
    }

    public function addDependencies(StyleInterface $io): self
    {
        while (true) {
            $fqcn = $io->ask('Add dependency (<comment>enter FQCN or leave empty to skip</comment>)');
            if (empty($fqcn)) {
                break;
            }

            try {
                $this->addDependencyByFQCN($fqcn);
            } catch (Exception $exception) {
                $io->error($exception->getMessage());
            }
        }

        return $this;
    }

//    public function create(): self
//    {
//        file_put_contents($this->getPath(), $this->prepareBody()->getBody());
//    }

    public function exists(): bool
    {
        return file_exists($this->getPath());
    }

    /**
     * @throws Exception
     */
    public function initModule(string $fqcn): self
    {
        return $this->setModule(
            (new Module($this->getComponentConfig()))->init($fqcn)
        );
    }

    public function getComponentConfig(): ComponentConfigInterface
    {
        return $this->componentConfig;
    }

    public function setComponentConfig(ComponentConfigInterface $componentConfig): self
    {
        $this->componentConfig = $componentConfig;

        return $this;
    }

    public function getModule(): Module
    {
        return $this->module;
    }

    public function setModule(Module $module): self
    {
        $this->module = $module;

        return $this;
    }

    public function getVariable(): Variable
    {
        return $this->variable;
    }

    public function initVariable(string $name): self
    {
        return $this->setVariable(
            new Variable($name)
        );
    }

    public function setVariable(Variable $variable): self
    {
        $this->variable = $variable;

        return $this;
    }

    public function getCommand(): ?CommandComponent
    {
        return $this->command;
    }

    public function hasCommand(): bool
    {
        return $this->command instanceof CommandComponent;
    }

    public function setCommand(?CommandComponent $command): self
    {
        $this->command = $command;

        return $this;
    }

    public function getEntity(): ?EntityComponent
    {
        return $this->entity;
    }

    public function hasEntity(): bool
    {
        return $this->entity instanceof EntityComponent;
    }

    public function setEntity(?EntityComponent $entity): self
    {
        $this->entity = $entity;

        return $this;
    }

    public function getFactory(): ?FactoryComponent
    {
        return $this->factory;
    }

    public function hasFactory(): bool
    {
        return $this->factory instanceof FactoryComponent;
    }

    public function setFactory(?FactoryComponent $factory): self
    {
        $this->factory = $factory;

        return $this;
    }

    public function getHandler(): ?HandlerComponent
    {
        return $this->handler;
    }

    public function hasHandler(): bool
    {
        return $this->handler instanceof HandlerComponent;
    }

    public function setHandler(?HandlerComponent $handler): self
    {
        $this->handler = $handler;

        return $this;
    }

    public function getInterface(): ?InterfaceComponent
    {
        return $this->interface;
    }

    public function hasInterface(): bool
    {
        return $this->interface instanceof InterfaceComponent;
    }

    public function setInterface(?InterfaceComponent $interface): self
    {
        $this->interface = $interface;

        return $this;
    }

    public function getMiddleware(): ?MiddlewareComponent
    {
        return $this->middleware;
    }

    public function hasMiddleware(): bool
    {
        return $this->middleware instanceof MiddlewareComponent;
    }

    public function setMiddleware(?MiddlewareComponent $middleware): self
    {
        $this->middleware = $middleware;

        return $this;
    }

    public function getRepository(): ?RepositoryComponent
    {
        return $this->repository;
    }

    public function hasRepository(): bool
    {
        return $this->repository instanceof RepositoryComponent;
    }

    public function setRepository(?RepositoryComponent $repository): self
    {
        $this->repository = $repository;

        return $this;
    }

    public function getService(): ?ServiceComponent
    {
        return $this->service;
    }

    public function hasService(): bool
    {
        return $this->service instanceof ServiceComponent;
    }

    public function setService(?ServiceComponent $service): self
    {
        $this->service = $service;

        return $this;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(?string $body): self
    {
        $this->body = $body;

        return $this;
    }

    public function getFqcn(): ?string
    {
        return $this->fqcn;
    }

    public function setFqcn(?string $fqcn): self
    {
        $this->fqcn = $fqcn;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getNamespace(): ?string
    {
        return $this->namespace;
    }

    public function setNamespace(?string $namespace): self
    {
        $this->namespace = $namespace;

        return $this;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function getRealPath(): ?string
    {
        return realpath($this->path);
    }

    public function setPath(?string $path): self
    {
        $this->path = $path;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    protected function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function isAnnotated(): bool
    {
        return $this->annotated;
    }

    public function setAnnotated(bool $annotated): self
    {
        $this->annotated = $annotated;

        return $this;
    }

    public function addDependency(Dependency $dependency): self
    {
        $this->dependencies[$dependency->getFqcn()] = $dependency;

        return $this;
    }

    /**
     * @throws Exception
     */
    public function addDependencyByFQCN(string $fqcn, string $alias = null): self
    {
        if (!array_key_exists($fqcn, $this->dependencies)) {
            $this->dependencies[$fqcn] = (new Dependency($this->getComponentConfig()))->init($fqcn, $alias);
        }

        return $this;
    }

    public function removeDependency(string $fqcn): self
    {
        unset($this->dependencies[$fqcn]);

        return $this;
    }

    protected function sortDependencies(): self
    {
        $arrays = [];
        asort($this->dependencies);
        foreach ($this->getDependencies() as $dependency) {
            if ($dependency->isArray()) {
                $arrays[$dependency->getFqcn()] = $dependency;
                $this->removeDependency($dependency->getFqcn());
            }
        }

        return $this->setDependencies(
            array_merge($this->dependencies, $arrays)
        );
    }

    /**
     * @return Dependency[]
     */
    public function getDependencies(): array
    {
        return $this->dependencies;
    }

    public function hasDependencies(): bool
    {
        return count($this->getDependencies()) > 0;
    }

    public function setDependencies(array $dependencies): self
    {
        $this->dependencies = $dependencies;

        return $this;
    }

    public function addUse(string $fqcn): self
    {
        $this->uses[$fqcn] = sprintf('use %s;', $fqcn);

        return $this;
    }

    public function getUses(): array
    {
        return $this->uses;
    }

    public function hasUses(): bool
    {
        return count($this->uses) > 0;
    }

    public function useSelf(): self
    {
        return $this->addUse($this->getFqcn());
    }

    /**
     * @throws DuplicateComponentException
     */
    protected function checkDuplicateComponent(string $path): self
    {
        if (file_exists($path)) {
            throw new DuplicateComponentException(
                sprintf('Component [%s] already exists.', $this->getFqcn())
            );
        }

        return $this;
    }

    /**
     * @throws Exception
     */
    protected function checkAnnotatedEntityInjector(bool $annotated = false): self
    {
        if ($annotated) {
            if (!$this->getComponentConfig()->hasAnnotatedEntityInjector()) {
                throw new Exception(
                    'In order to create annotated components, you must configure annotated_entity_injector'
                );
            }
        }

        return $this;
    }

    /**
     * @throws Exception
     */
    protected function checkAnnotatedServiceInjector(bool $annotated = false): self
    {
        if ($annotated) {
            if (!$this->getComponentConfig()->hasAnnotatedServiceInjector()) {
                throw new Exception(
                    'In order to create annotated components, you must configure annotated_service_injector'
                );
            }
        }

        return $this;
    }

    /**
     * @throws Exception
     */
    protected function checkAnnotatedServiceFactory(bool $annotated = false): self
    {
        if ($annotated) {
            if (!$this->getComponentConfig()->hasAnnotatedServiceFactory()) {
                throw new Exception(
                    'In order to create annotated components, you must configure annotated_service_factory'
                );
            }
        }

        return $this;
    }

    /**
     * @throws Exception
     */
    protected function checkAnnotatedRepositoryFactory(bool $annotated = false): self
    {
        if ($annotated) {
            if (!$this->getComponentConfig()->hasAnnotatedRepositoryFactory()) {
                throw new Exception(
                    'In order to create annotated components, you must configure annotated_repository_factory'
                );
            }
        }

        return $this;
    }

    /**
     * @throws Exception
     */
    protected function checkType(string $fqcn): self
    {
        if ($this->getType() === InterfaceComponent::TYPE) {
            return $this;
        }

        $type = $this->extractType($fqcn);
        if ($type === $this->getType()) {
            return $this;
        }

        throw new Exception(
            sprintf('Detected type (%s) does not match the provided FQCN: %s', $this->getType(), $fqcn)
        );
    }

    protected function extractFqcn(string $fqcn, string $componentType): string
    {
        while (true) {
            $retry = false;
            foreach ($this->types as $type) {
                if ($type === $componentType) {
                    continue;
                }
                if (str_ends_with($fqcn, $type)) {
                    $fqcn = substr_replace($fqcn, '', -strlen($type), strlen($type));
                    $retry = true;
                }
            }
            if ($retry === false) {
                break;
            }
        }

        if (!str_starts_with($fqcn, '\\')) {
            $fqcn = sprintf('\\%s', $fqcn);
        }

        if (!str_ends_with($fqcn, $componentType)) {
            $fqcn = sprintf('%s%s', $fqcn, $componentType);
        }

        return $fqcn;
    }

    protected function extractName(string $fqcn): string
    {
        $parts = explode('\\', $fqcn);
        $parts = array_filter($parts);

        return array_pop($parts);
    }

    protected function extractNamespace(string $fqcn): string
    {
        $parts = explode('\\', $fqcn);
        $parts = array_filter($parts);
        array_pop($parts);

        return implode('\\', $parts);
    }

    protected function extractPath(string $fqcn): string
    {
        return sprintf(
            '%s/%s.php',
            $this->getModule()->getPath(),
            str_replace($this->getModule()->getFqmn(), '', $fqcn)
        );
    }

    protected function extractType(string $fqcn): ?string
    {
        foreach ($this->types as $type) {
            $pattern = sprintf('\\%s\\', $type);
            if (substr_count($fqcn, $pattern) > 0) {
                return $type;
            }
        }

        return null;
    }

    public function getConfigProviderFQCN(): string
    {
        return sprintf('%sConfigProvider', $this->getModule()->getFqmn());
    }

    public function getConfigProviderPath(): string
    {
        return sprintf('%s/ConfigProvider.php', $this->getModule()->getPath());
    }

    public function getConfigProviderRealPath(): string
    {
        return realpath($this->getConfigProviderPath());
    }

    protected function getFactoryFqcn(): string
    {
        return sprintf(
            '%s%s',
            $this->replaceTypes($this->getFqcn(), FactoryComponent::TYPE),
            FactoryComponent::TYPE
        );
    }

    protected function getInterfaceFqcn(): string
    {
        return sprintf('%s%s', $this->getFqcn(), InterfaceComponent::TYPE);
    }

    protected function getRepositoryFqcn(): string
    {
        $fqcn = $this->getFqcn();
        $fqcn = substr_replace($fqcn, '', -strlen(EntityComponent::TYPE), strlen(EntityComponent::TYPE));

        return sprintf(
            '%s%s',
            $this->replaceTypes($fqcn, RepositoryComponent::TYPE),
            RepositoryComponent::TYPE
        );
    }

    protected function isTypeOf(string $fqcn, string $type): bool
    {
        return $this->extractType($fqcn) === $type;
    }

    protected function parseAnnotations(): string
    {
        if (!$this->isAnnotated() || !$this->hasDependencies()) {
            return '';
        }

        $annotations = [];
        foreach ($this->getDependencies() as $dependency) {
            if ($dependency->isArray()) {
                $annotations[] = sprintf('     *     "%s",', $dependency->getOriginalName());
            } else {
                $annotations[] = sprintf('     *     %s::class,', $dependency->getName());
            }
        }
        $annotations = implode(PHP_EOL, $annotations);

        return <<<MSG

    /**
     * @Inject({
{$annotations}
     * })
     */
MSG;
    }

    protected function parseArguments(): string
    {
        if (!$this->hasDependencies()) {
            return '';
        }

        $arguments = [];
        foreach ($this->getDependencies() as $dependency) {
            $arguments[] = sprintf(
                PHP_EOL . '        %s %s',
                $dependency->getType(),
                $dependency->getVariable()->getName()
            );
        }

        return implode(',', $arguments);
    }

    protected function parseContainerGets(): string
    {
        if (!$this->hasDependencies()) {
            return '';
        }

        $pad8 = str_repeat(' ', 8);
        $pad12 = str_repeat(' ', 12);

        $gets = [];
        foreach ($this->getDependencies() as $dependency) {
            if ($dependency->isArray()) {
                $parts = explode('.', $dependency->getOriginalName());
                $parts = array_filter($parts);
                if (count($parts) > 1) {
                    $name = array_shift($parts);
                    $tree = implode("']['", $parts);
                    $gets[] = sprintf('%s$container->get(\'%s\')[\'%s\'] ?? [],', $pad12, $name, $tree);
                } else {
                    $gets[] = sprintf('%s$container->get(\'%s\'),', $pad12, $dependency->getName());
                }
            } else {
                $gets[] = sprintf('%s$container->get(%s::class),', $pad12, $dependency->getName());
            }
        }

        return PHP_EOL . implode(PHP_EOL, $gets) . PHP_EOL . $pad8;
    }

    protected function parseProperties(): string
    {
        if (!$this->hasDependencies()) {
            return '';
        }

        $properties = [];
        foreach ($this->getDependencies() as $dependency) {
            $properties[] = sprintf(
                '    protected %s %s;',
                $dependency->getType(),
                $dependency->getVariable()->getName()
            );
        }

        return PHP_EOL . implode(PHP_EOL, $properties);
    }

    protected function parsePropertyAssignments(): string
    {
        if (!$this->hasDependencies()) {
            return '';
        }

        $pad8 = str_repeat(' ', 8);

        $assignments = [];
        foreach ($this->getDependencies() as $dependency) {
            $assignments[] = sprintf(
                '%s$this->%s = %s;',
                $pad8,
                $dependency->getVariable()->getProperty(),
                $dependency->getVariable()->getName()
            );
        }

        return PHP_EOL . implode(PHP_EOL, $assignments);
    }

    protected function parseUses(): string
    {
        foreach ($this->getDependencies() as $dependency) {
            if ($dependency->isArray()) {
                continue;
            }
            $this->addUse($dependency->getFqcn());
        }

        if ($this->isAnnotated() && $this->hasDependencies()) {
            $this->addUse($this->getComponentConfig()->getAnnotatedServiceInjector());
        }

        if (!$this->hasUses()) {
            return '';
        }

        $uses = $this->getUses();
        asort($uses);

        return PHP_EOL . implode(PHP_EOL, $uses);
    }

    protected function prepareBody(string $code): string
    {
        $code = '<?php' . PHP_EOL . PHP_EOL . 'declare(strict_types=1);' . PHP_EOL . PHP_EOL . $code;
        $code = str_replace(') :', '):', $code);

        foreach ($this->generator->getUses() as $use) {
            $code = str_replace('\\' . $use, $this->fqcnToClassName($use), $code);
        }

        preg_match_all('/.*(^use [A-Z].*;$).*/im', $code, $uses);
        sort($uses[1]);
        exit(implode(PHP_EOL, $uses[0]));
        $code = str_replace(implode(PHP_EOL, $uses[0]), implode(PHP_EOL, $uses[1]), $code);

        return $code;
    }

    protected function fqcnToClassName(string $fqcn): string
    {
        $parts = explode('\\', $fqcn);

        return end($parts);
    }

//    protected function prepareBody(): self
//    {
//        $this->sortDependencies();
//        $placeholders = $this->getPlaceholders();
//
//        return $this->setBody(
//            str_replace(
//                array_keys($placeholders),
//                array_values($placeholders),
//                (new Stub($this->getComponentConfig()))->init(static::STUB_NAME)->getBody()
//            )
//        );
//    }

    protected function replaceTypes(string $fqcn, string $type = ''): string
    {
        $types = array_map(function ($type) {
            return sprintf('\\%s\\', $type);
        }, $this->types);

        return str_replace($types, sprintf('\\%s\\', $type), $fqcn);
    }

    public function normalizeFqcn(string $fqcn): self
    {
        $fqcn = $this->extractFqcn($fqcn, static::TYPE);

        return $this->setFqcn($fqcn);
    }

    protected function normalizeName(string $fqcn): self
    {
        $name = $this->extractName($fqcn);

        return $this->setName($name);
    }

    protected function normalizeNamespace(string $fqcn): self
    {
        $namespace = $this->extractNamespace($fqcn);

        return $this->setNamespace($namespace);
    }

    public function normalizePath(string $fqcn): self
    {
        $path = $this->extractPath($fqcn);

        return $this->setPath($path);
    }

    protected function findPlaceholders(string $path)
    {
        $body = file_get_contents($path);
        preg_match('/(\{[a-z_]+\})/i', $body, $matches);

        return $matches;
    }

    abstract public function exposePlaceholders(): array;
    abstract public function getPlaceholders(): array;
}
