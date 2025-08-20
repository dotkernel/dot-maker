<?php

declare(strict_types=1);

namespace Component;

use Dot\Maker\Component\ClassFile;
use Dot\Maker\Component\Inject;
use Dot\Maker\Component\Method;
use Dot\Maker\Component\Parameter;
use Dot\Maker\Component\PromotedProperty;
use Dot\Maker\Component\Property;
use Dot\Maker\VisibilityEnum;
use PHPUnit\Framework\TestCase;

class ClassFileTest extends TestCase
{
    private string $namespace = 'App\\Module\\Directory';
    private string $className = 'ClassName';

    public function testWillInstantiate(): void
    {
        $classFile = new ClassFile($this->namespace, $this->className);
        $this->assertContainsOnlyInstancesOf(ClassFile::class, [$classFile]);
        $this->assertSame($this->namespace, $classFile->namespace);
        $this->assertSame($this->className, $classFile->className);
    }

    public function testAccessorsWillGetAndSetProperties(): void
    {
        $classFile = new ClassFile($this->namespace, $this->className);

        $this->assertCount(0, $classFile->getInjects());
        $classFile->addInject(new Inject('injectName'));
        $this->assertCount(1, $classFile->getInjects());

        $this->assertCount(0, $classFile->getInterfaces());
        $classFile->addInterface('InterfaceName');
        $this->assertCount(1, $classFile->getInterfaces());

        $this->assertCount(0, $classFile->getMethods());
        $classFile->addMethod(new Method('MethodName'));
        $this->assertCount(1, $classFile->getMethods());
        $this->assertTrue($classFile->hasMethod('MethodName'));

        $this->assertCount(0, $classFile->getProperties());
        $classFile->addProperty(new Property('PropertyName', 'string'));
        $this->assertCount(1, $classFile->getProperties());

        $this->assertCount(0, $classFile->getTraits());
        $classFile->addTrait('TraitName');
        $this->assertCount(1, $classFile->getTraits());
        $this->assertSame([
            'use TraitName;' => 'use TraitName;',
        ], $classFile->getTraits());

        $this->assertCount(0, $classFile->getClassUses());
        $classFile->useClass('App\\ModuleA\\ClassName');
        $this->assertCount(1, $classFile->getClassUses());
        $classFile->useClass('App\\ModuleB\\ClassName', 'OtherClassName');
        $this->assertCount(2, $classFile->getClassUses());
        $this->assertSame([
            'use App\\ModuleA\\ClassName;'                   => 'use App\\ModuleA\\ClassName;',
            'use App\\ModuleB\\ClassName as OtherClassName;' => 'use App\\ModuleB\\ClassName as OtherClassName;',
        ], $classFile->getClassUses());

        $this->assertCount(0, $classFile->getFunctionUses());
        $classFile->useFunction('some_function');
        $this->assertCount(1, $classFile->getFunctionUses());
        $this->assertSame([
            'use function some_function;' => 'use function some_function;',
        ], $classFile->getFunctionUses());

        $this->assertCount(0, $classFile->getConstantUses());
        $classFile->useConstant('SOME_CONSTANT');
        $this->assertCount(1, $classFile->getConstantUses());
        $this->assertSame([
            'use const SOME_CONSTANT;' => 'use const SOME_CONSTANT;',
        ], $classFile->getConstantUses());

        $this->assertFalse($classFile->isAbstract());
        $classFile->setAbstract(true);
        $this->assertTrue($classFile->isAbstract());

        $this->assertFalse($classFile->isReadonly());
        $classFile->setReadonly(true);
        $this->assertTrue($classFile->isReadonly());

        $this->assertFalse($classFile->isFinal());
        $classFile->setFinal(true);
        $this->assertTrue($classFile->isFinal());

        $this->assertTrue($classFile->isStrictTypes());
        $classFile->setStrictTypes(false);
        $this->assertFalse($classFile->isStrictTypes());

        $this->assertNull($classFile->getExtends());
        $classFile->setExtends('OtherClassName');
        $this->assertSame('OtherClassName', $classFile->getExtends());

        $this->assertSame('', $classFile->getComment());
        $classFile->setComment('Comment');
        $this->assertSame('Comment', $classFile->getComment());
    }

    public function testWillRenderClass(): void
    {
        $actual = (new ClassFile($this->namespace, $this->className))
            ->setExtends('OtherClassName')
            ->addInterface('InterfaceName')
            ->addInject(
                (new Inject('CustomInjector'))->addArgument('true', 'param')
            )
            ->useClass('App\\Module\\OtherClassName')
            ->useClass('App\\Module\\InterfaceName')
            ->useFunction('sprintf')
            ->useConstant('PHP_EOL')
            ->addProperty(
                new Property('propertyName', 'bool', false, 'true')
            )
            ->addMethod(
                (new Method\Constructor())
                    ->addInject(
                        (new Inject())
                            ->addArgument('SomeClass::class')
                            ->addArgument('"config"')
                    )
                    ->addPromotedProperty(
                        new PromotedProperty('instance', 'SomeClass')
                    )
                    ->addPromotedProperty(
                        new PromotedProperty('config', 'array')
                    )
            )
            ->addMethod(
                (new Method('publicMethodName'))
                    ->setComment(<<<COMM
/**
     * @param string \$param
     * @return bool
     */
COMM)
                    ->setReturnType('bool')
                    ->setNullable(true)
                    ->addParameter(
                        new Parameter('param', 'string')
                    )
                    ->appendBody('return true;')
            )
            ->addMethod(
                (new Method('protectedMethodName'))
                    ->setVisibility(VisibilityEnum::Protected)
                    ->appendBody('// add logic')
            )
            ->addMethod(
                (new Method('privateMethodName'))
                    ->setVisibility(VisibilityEnum::Private)
                    ->appendBody('// add logic')
            );

        $this->assertSame($this->dataProviderRenderedClass(), $actual->render());
    }

    private function dataProviderRenderedClass(): string
    {
        return <<<BODY
<?php

declare(strict_types=1);

namespace App\Module\Directory;

use App\Module\InterfaceName;
use App\Module\OtherClassName;

use function sprintf;

use const PHP_EOL;

#[CustomInjector(param: true)]
class ClassName extends OtherClassName implements InterfaceName
{
    protected bool \$propertyName = true;

    #[Inject(
        SomeClass::class,
        "config",
    )]
    public function __construct(
        protected SomeClass \$instance,
        protected array \$config,
    ) {
    }

    /**
     * @param string \$param
     * @return bool
     */
    public function publicMethodName(
        string \$param,
    ): ?bool {
        return true;
    }

    protected function protectedMethodName(): void
    {
        // add logic
    }

    private function privateMethodName(): void
    {
        // add logic
    }
}

BODY;
    }
}
