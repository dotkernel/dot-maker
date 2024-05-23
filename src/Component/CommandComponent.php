<?php

declare(strict_types=1);

namespace Dot\Maker\Component;

use Dot\Maker\Component;
use Dot\Maker\Config\ComponentConfigInterface;
use Dot\Maker\Exception\DuplicateComponentException;
use Exception;
use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\MethodGenerator;
use Laminas\Code\Generator\PropertyGenerator;
use Laminas\Code\Generator\TypeGenerator;
use Laminas\Code\Reflection\ClassReflection;
use Laminas\Code\Reflection\MethodReflection;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CommandComponent extends Component
{
    public const TYPE = 'Command';
    public const STUB_NAME = 'command.stub';
    protected string $defaultName = '';

    public function __construct(ComponentConfigInterface $componentConfig)
    {
        parent::__construct($componentConfig);
        $this->setType(self::TYPE);
    }

    public function getDefaultName(): string
    {
        return $this->defaultName;
    }

    protected function setDefaultName(string $defaultName): self
    {
        $this->defaultName = $defaultName;

        return $this;
    }

    /**
     * @throws ReflectionException
     */
    public function initializeExistingCommand(): self
    {
//        $class = ClassGenerator::fromReflection(
//            new ClassReflection($this->getFqcn())
//        );
//        exit($class->generate());
        $this->generator
            ->setName($this->getName())
            ->setNamespaceName($this->getNamespace())
            ->addUse(InputInterface::class)
            ->addUse(OutputInterface::class);

        $reflectionClass = new ReflectionClass($this->getFqcn());

        $extendedClass = $reflectionClass->getParentClass();
        if ($extendedClass instanceof ReflectionClass) {
            $this->generator->addUse($extendedClass->getName())->setExtendedClass($extendedClass->getName());
        }

        $interfaces = $reflectionClass->getInterfaces();
        if (! empty($interfaces)) {
            foreach ($interfaces as $interface) {
                $this->generator->addUse($interface->getName());
            }
            $this->generator->setImplementedInterfaces(array_keys($interfaces));
        }

        $reflectionConstants = $reflectionClass->getConstants();
        if ($extendedClass instanceof ReflectionClass) {
            $parentConstants = $extendedClass->getConstants();
            $reflectionConstants = array_diff_key($reflectionConstants, $parentConstants);
        }
        foreach ($reflectionConstants as $reflectionConstantName => $reflectionConstantValue) {
            $this->generator->addConstant($reflectionConstantName, $reflectionConstantValue);
        }

        $reflectionProperties = $reflectionClass->getProperties();
        foreach ($reflectionProperties as $reflectionProperty) {
            $generatedProperty = new PropertyGenerator($reflectionProperty->getName());
            if ($reflectionProperty->isPromoted()) {
                continue;
            }

            if ($reflectionProperty->isPrivate()) {
                $generatedProperty->setVisibility(PropertyGenerator::VISIBILITY_PRIVATE);
            }
            if ($reflectionProperty->isProtected()) {
                $generatedProperty->setVisibility(PropertyGenerator::VISIBILITY_PROTECTED);
            }
            if ($reflectionProperty->isPublic()) {
                $generatedProperty->setVisibility(PropertyGenerator::VISIBILITY_PUBLIC);
            }
            $generatedProperty->setReadonly($reflectionProperty->isReadOnly());
            $generatedProperty->setStatic($reflectionProperty->isStatic());
            if ($reflectionProperty->hasDefaultValue()) {
                $generatedProperty->setDefaultValue($reflectionProperty->getDefaultValue());
            }
            if ($reflectionProperty->hasType()) {
                $generatedProperty->setType(TypeGenerator::fromTypeString($reflectionProperty->getType()->getName()));
            }
            $this->generator->addPropertyFromGenerator($generatedProperty);
        }

        $reflectionMethods = $reflectionClass->getMethods();
        foreach ($reflectionMethods as $reflectionMethod) {
            if ('\\' . $reflectionMethod->getDeclaringClass()->getName() !== $this->getFqcn()) {
                continue;
            }

            foreach ($reflectionMethod->getParameters() as $reflectionMethodParameter) {
                $this->generator->addUse($reflectionMethodParameter->getType()->getName());
            }
            $generatedMethod = MethodGenerator::fromReflection(
                new MethodReflection($reflectionMethod->getDeclaringClass()->getName(), $reflectionMethod->getName())
            );
            $this->generator->addMethodFromGenerator($generatedMethod);
        }

        return $this;
    }

    /**
     * @throws DuplicateComponentException
     * @throws Exception
     */
    public function initializeNewCommand(string $fqcn, bool $annotated = false): self
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
            ->generateDefaultName($this->getName())
            ->setAnnotated($annotated)
            ->addFactory($annotated);
    }

    protected function getMethodBody(string $fileName, string $methodName): string
    {

//        $sourceCode = file_get_contents($fileName);
//        $tree = (new ParserFactory())->create(ParserFactory::PREFER_PHP7)->parse($sourceCode);
//        $methodVisitor = new MethodVisitor($sourceCode, $methodName);
//        $traverser = new NodeTraverser();
//        $traverser->addVisitor($methodVisitor);
//        $traverser->traverse($tree);
//        $body = $methodVisitor->getBody();
//        exit($body);

//        $lineStart = $reflectionMethod->getStartLine();
//        $lineEnd = $reflectionMethod->getEndLine() + 1;
//
//        $file = fopen($reflectionMethod->getFileName(), 'r');
//        $lines = [];
//
//        $currentLine = 0;
//        while (($line = fgets($file, 4096)) !== false) {
//            $currentLine++;
//            if ($currentLine <= $lineStart || $currentLine >= $lineEnd) {
//                continue;
//            }
//            $lines[$currentLine] = $line;
//        }
//
//        fclose($file);
//
//        $foundOpeningBrace = $foundClosingBrace = false;
////        foreach ($lines as $key => $line) {
//////            if () {}
////        }
//        var_dump($lines);exit;
//
//        return implode('', $lines);
    }

    public function initializeGenerator(): self
    {
        return $this;
    }

    public function create(): self
    {
//        $this->generator
//            ->setName($this->getName())
//            ->setNamespaceName($this->getNamespace())
//            ->addUse('Symfony\Component\Console\Command\Command')
//            ->addUse('Symfony\Component\Console\Input\InputInterface')
//            ->addUse('Symfony\Component\Console\Output\OutputInterface')
//            ->setExtendedClass(Command::class)
//            ->addProperty(
//                'defaultName',
//                $this->getDefaultName(),
//                PropertyGenerator::FLAG_PROTECTED | PropertyGenerator::FLAG_STATIC
//            )
//        ;
//        $this->generator->generate();
//        file_put_contents($this->getPath(), $this->prepareBody()->getBody());
        $code = $this->prepareBody($this->generator->generate());
        var_dump($code);exit;
//        file_put_contents($this->getPath(), $code);

        return $this;
    }

    /**
     * @throws Exception
     */
    protected function addFactory(bool $annotated): self
    {
        if (! $annotated) {
            $this->setFactory(
                (new FactoryComponent($this->getComponentConfig()))
                    ->setCommand($this)
                    ->init($this->getFactoryFqcn())
            );
        }

        return $this;
    }

    protected function generateDefaultName(string $name): self
    {
        $parts = preg_split('/(?=[A-Z])/', $name, -1, PREG_SPLIT_NO_EMPTY);
        array_pop($parts);
        $parts = array_map('strtolower', $parts);

        return $this->setDefaultName(
            sprintf('%s:%s', array_shift($parts), implode('-', $parts))
        );
    }

    public function exposePlaceholders(): array
    {
        return [
            '{COMMAND_ANNOTATIONS}' => $this->parseAnnotations(),
            '{COMMAND_ARGUMENTS}' => $this->parseArguments(),
            '{COMMAND_PROPERTIES}' => $this->parseProperties(),
            '{COMMAND_PROPERTY_ASSIGNMENTS}' => $this->parsePropertyAssignments(),
            '{COMPONENT_USES}' => $this->parseUses(),
            '{COMPONENT_CONTAINER_GETS}' => $this->parseContainerGets(),
            '{COMPONENT_NAME}' => $this->getName(),
            '{COMMAND_FQCN}' => $this->getFqcn(),
            '{COMMAND_NAME}' => $this->getName(),
            '{COMMAND_NAMESPACE}' => $this->getNamespace(),
            '{COMMAND_PATH}' => $this->getPath(),
            '{COMMAND_PROPERTY}' => $this->getVariable()->getProperty(),
            '{COMMAND_VARIABLE}' => $this->getVariable()->getName(),
            '{COMMAND_DEFAULT_NAME}' => $this->getDefaultName(),
        ];
    }

    public function getPlaceholders(): array
    {
        $placeholders = $this->exposePlaceholders();

        if ($this->hasFactory()) {
            $placeholders = array_merge($this->getFactory()->useSelf()->exposePlaceholders(), $placeholders);
        }

        return $placeholders;
    }
}
