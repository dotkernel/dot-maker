<?php

declare(strict_types=1);

namespace Component;

use Dot\Maker\Component\Import;
use Dot\Maker\Context;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

class CoreImportTest extends TestCase
{
    private Context $context;

    protected function setUp(): void
    {
        $root = vfsStream::setup('root', 0644, [
            'composer.json' => '{
                "autoload": {
                    "psr-4": {
                        "Api\\\\App\\\\": "src/App/src/",
                        "Core\\\\App\\\\": "src/Core/src/App/src/"
                    }
                }
            }',
        ]);

        $this->context = new Context($root->url());
    }

    public function testWillGetAbstractFormFqcn(): void
    {
        $import = new Import($this->context);
        $this->assertSame('Api\App\Form\AbstractForm', $import->getAbstractFormFqcn());
    }

    public function testWillGetAbstractInputFilterFqcn(): void
    {
        $import = new Import($this->context);
        $this->assertSame('Core\App\InputFilter\AbstractInputFilter', $import->getAbstractInputFilterFqcn());
    }

    public function testWillGetAbstractHandlerFqcn(): void
    {
        $import = new Import($this->context);
        $this->assertSame('Api\App\Handler\AbstractHandler', $import->getAbstractHandlerFqcn());
    }

    public function testWillGetAbstractRepositoryFqcn(): void
    {
        $import = new Import($this->context);
        $this->assertSame('Core\App\Repository\AbstractRepository', $import->getAbstractRepositoryFqcn());
    }

    public function testWillGetAppHelperPaginatorFqcn(): void
    {
        $import = new Import($this->context);
        $this->assertSame('Core\App\Helper\Paginator', $import->getAppHelperPaginatorFqcn());
    }

    public function testWillGetAppMessageFqcn(): void
    {
        $import = new Import($this->context);
        $this->assertSame('Core\App\Message', $import->getAppMessageFqcn());
    }

    public function testWillGetBadRequestExceptionFqcn(): void
    {
        $import = new Import($this->context);
        $this->assertSame('Api\App\Exception\BadRequestException', $import->getBadRequestExceptionFqcn());
    }

    public function testWillGetConfigProviderFqcn(): void
    {
        $import = new Import($this->context);
        $this->assertSame('Api\App\ConfigProvider', $import->getConfigProviderFqcn());
    }

    public function testWillGetConflictExceptionFqcn(): void
    {
        $import = new Import($this->context);
        $this->assertSame('Api\App\Exception\ConflictException', $import->getConflictExceptionFqcn());
    }

    public function testWillGetCsrfInputFqcn(): void
    {
        $import = new Import($this->context);
        $this->assertSame('Api\App\InputFilter\Input\CsrfInput', $import->getCsrfInputFqcn());
    }

    public function testWillGetHandlerDelegatorFactoryFqcn(): void
    {
        $import = new Import($this->context);
        $this->assertSame('Api\App\Factory\HandlerDelegatorFactory', $import->getHandlerDelegatorFactoryFqcn());
    }

    public function testWillGetNotFoundExceptionFqcn(): void
    {
        $import = new Import($this->context);
        $this->assertSame('Api\App\Exception\NotFoundException', $import->getNotFoundExceptionFqcn());
    }

    public function testWillGetResourceAttributeFqcn(): void
    {
        $import = new Import($this->context);
        $this->assertSame('Api\App\Attribute\Resource', $import->getResourceAttributeFqcn());
    }

    public function testWillGetResourceCollectionFqcn(): void
    {
        $import = new Import($this->context);
        $this->assertSame('Api\App\Collection\ResourceCollection', $import->getResourceCollectionFqcn());
    }
}
