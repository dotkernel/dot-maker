<?php

declare(strict_types=1);

namespace Component;

use Dot\Maker\Component\Declaration;
use Dot\Maker\Component\InterfaceFile;
use PHPUnit\Framework\TestCase;

class InterfaceFileTest extends TestCase
{
    private string $namespace     = 'App\\Module\\Directory';
    private string $interfaceName = 'ClassName';

    public function testWillInstantiate(): void
    {
        $interfaceFile = new InterfaceFile($this->namespace, $this->interfaceName);
        $this->assertContainsOnlyInstancesOf(InterfaceFile::class, [$interfaceFile]);
        $this->assertSame($this->namespace, $interfaceFile->namespace);
        $this->assertSame($this->interfaceName, $interfaceFile->interfaceName);
    }

    public function testAccessorsWillGetAndSetProperties(): void
    {
        $interfaceFile = new InterfaceFile($this->namespace, $this->interfaceName);

        $this->assertCount(0, $interfaceFile->getDeclarations());
        $interfaceFile->addDeclaration((new Declaration('getResource'))->setReturnType('Resource'));
        $this->assertCount(1, $interfaceFile->getDeclarations());

        $this->assertCount(0, $interfaceFile->getInterfaces());
        $interfaceFile->addInterface('InterfaceName');
        $this->assertCount(1, $interfaceFile->getInterfaces());

        $this->assertCount(0, $interfaceFile->getClassUses());
        $interfaceFile->useClass('App\\ModuleA\\ClassName');
        $this->assertCount(1, $interfaceFile->getClassUses());
        $interfaceFile->useClass('App\\ModuleB\\ClassName', 'OtherClassName');
        $this->assertCount(2, $interfaceFile->getClassUses());
        $this->assertSame([
            'use App\\ModuleA\\ClassName;'                   => 'use App\\ModuleA\\ClassName;',
            'use App\\ModuleB\\ClassName as OtherClassName;' => 'use App\\ModuleB\\ClassName as OtherClassName;',
        ], $interfaceFile->getClassUses());

        $this->assertCount(0, $interfaceFile->getFunctionUses());
        $interfaceFile->useFunction('some_function');
        $this->assertCount(1, $interfaceFile->getFunctionUses());
        $this->assertSame([
            'use function some_function;' => 'use function some_function;',
        ], $interfaceFile->getFunctionUses());

        $this->assertCount(0, $interfaceFile->getConstantUses());
        $interfaceFile->useConstant('SOME_CONSTANT');
        $this->assertCount(1, $interfaceFile->getConstantUses());
        $this->assertSame([
            'use const SOME_CONSTANT;' => 'use const SOME_CONSTANT;',
        ], $interfaceFile->getConstantUses());

        $this->assertTrue($interfaceFile->isStrictTypes());
        $interfaceFile->setStrictTypes(false);
        $this->assertFalse($interfaceFile->isStrictTypes());

        $this->assertSame('', $interfaceFile->getComment());
        $interfaceFile->setComment('Comment');
        $this->assertSame('Comment', $interfaceFile->getComment());
    }

    public function testWillRenderInterface(): void
    {
        $actual = (new InterfaceFile($this->namespace, $this->interfaceName))
            ->addInterface('OtherInterface')
            ->useClass('App\\Module\\OtherInterface')
            ->useFunction('sprintf')
            ->useConstant('PHP_EOL')
            ->setComment(<<<COMM
/**
 * Comment
 */
COMM)
            ->addDeclaration((new Declaration('getResource'))->setReturnType('Resource'));

        $this->assertSame($this->dataProviderRenderedClass(), (string) $actual);
    }

    private function dataProviderRenderedClass(): string
    {
        return <<<BODY
<?php

declare(strict_types=1);

namespace App\Module\Directory;

use App\Module\OtherInterface;

use function sprintf;

use const PHP_EOL;

/**
 * Comment
 */
interface ClassName extends OtherInterface
{
    public function getResource(): Resource;
}

BODY;
    }
}
