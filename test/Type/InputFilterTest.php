<?php

declare(strict_types=1);

namespace DotTest\Maker\Type;

use Dot\Maker\Component\Import;
use Dot\Maker\Config;
use Dot\Maker\Context;
use Dot\Maker\FileSystem;
use Dot\Maker\IO\Input;
use Dot\Maker\IO\Output;
use Dot\Maker\Type\InputFilter;
use Dot\Maker\Type\Module;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

use function fclose;
use function fopen;
use function fwrite;
use function rewind;
use function stream_get_contents;

use const PHP_EOL;

class InputFilterTest extends TestCase
{
    private Config $config;
    private Context $context;
    private FileSystem $fileSystem;
    private Import $import;
    private Module $module;
    private string $moduleName   = 'ModuleName';
    private string $resourceName = 'BookStore';

    /** @var resource $outputStream */
    private $outputStream;
    /** @var resource $inputStream */
    private $inputStream;
    /** @var resource $errorStream */
    private $errorStream;

    protected function setUp(): void
    {
        $root = vfsStream::setup('root', 0644, [
            'composer.json' => '{
                "autoload": {
                    "psr-4": {
                        "Admin\\\\App\\\\": "src/App/src/",
                        "Core\\\\App\\\\": "src/Core/src/App/src/"
                    }
                }
            }',
        ]);

        $this->config     = new Config($root->url());
        $this->context    = new Context($root->url());
        $this->fileSystem = (new FileSystem($this->context))->setModuleName($this->moduleName);
        $this->import     = new Import($this->context);
        $this->module     = new Module($this->fileSystem, $this->context, $this->config);

        $this->outputStream = fopen('php://memory', 'w+');
        Output::setOutputStream($this->outputStream);

        $this->errorStream = fopen('php://memory', 'w+');
        Output::setErrorStream($this->errorStream);

        $this->inputStream = fopen('php://memory', 'w+');
        Input::setStream($this->inputStream);
    }

    protected function tearDown(): void
    {
        fclose($this->inputStream);
        fclose($this->errorStream);
        fclose($this->outputStream);
    }

    public function testCallToInvokeWillNotCreateFileOnEmptyInput(): void
    {
        $file = $this->fileSystem->inputFilter($this->resourceName);
        $this->assertFileDoesNotExist($file->getPath());
        $this->assertFalse($file->exists());

        fwrite($this->inputStream, PHP_EOL);
        rewind($this->inputStream);

        $inputFilter = new InputFilter($this->fileSystem, $this->context, $this->config, $this->module);
        $inputFilter();

        rewind($this->errorStream);
        $this->assertEmpty(stream_get_contents($this->errorStream));
        $this->assertFalse($file->exists());
        $this->assertFileDoesNotExist($file->getPath());
    }

    public function testCallToInvokeWillOutputErrorAndWillNotCreateFileWhenNameIsInvalid(): void
    {
        $file = $this->fileSystem->inputFilter($this->resourceName);
        $this->assertFalse($file->exists());
        $this->assertFileDoesNotExist($file->getPath());

        fwrite($this->inputStream, '.' . PHP_EOL);
        rewind($this->inputStream);

        $inputFilter = new InputFilter($this->fileSystem, $this->context, $this->config, $this->module);
        $inputFilter();

        rewind($this->errorStream);
        $this->assertStringContainsString('Invalid InputFilter name: "."', stream_get_contents($this->errorStream));
        $this->assertFalse($file->exists());
        $this->assertFileDoesNotExist($file->getPath());
    }

    public function testCallToInvokeWillCreateOnlyCreateResourceInputFilter(): void
    {
        fwrite($this->inputStream, $this->resourceName . PHP_EOL);
        fwrite($this->inputStream, 'yes' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        rewind($this->inputStream);

        $createResourceInputFilter = $this->fileSystem->createResourceInputFilter($this->resourceName);
        $this->assertFileDoesNotExist($createResourceInputFilter->getPath());
        $this->assertFalse($createResourceInputFilter->exists());

        $deleteResourceInputFilter = $this->fileSystem->deleteResourceInputFilter($this->resourceName);
        $this->assertFileDoesNotExist($deleteResourceInputFilter->getPath());
        $this->assertFalse($deleteResourceInputFilter->exists());

        $editResourceInputFilter = $this->fileSystem->editResourceInputFilter($this->resourceName);
        $this->assertFileDoesNotExist($editResourceInputFilter->getPath());
        $this->assertFalse($editResourceInputFilter->exists());

        $replaceResourceInputFilter = $this->fileSystem->replaceResourceInputFilter($this->resourceName);
        $this->assertFileDoesNotExist($replaceResourceInputFilter->getPath());
        $this->assertFalse($replaceResourceInputFilter->exists());

        $inputFilter = new InputFilter($this->fileSystem, $this->context, $this->config, $this->module);
        $inputFilter();

        $this->assertFileExists($createResourceInputFilter->getPath());
        $this->assertTrue($createResourceInputFilter->exists());
        $this->assertSame($this->dataProviderCreateResourceInputFilter(), $createResourceInputFilter->read());

        $this->assertFileDoesNotExist($deleteResourceInputFilter->getPath());
        $this->assertFalse($deleteResourceInputFilter->exists());

        $this->assertFileDoesNotExist($editResourceInputFilter->getPath());
        $this->assertFalse($editResourceInputFilter->exists());

        $this->assertFileDoesNotExist($replaceResourceInputFilter->getPath());
        $this->assertFalse($replaceResourceInputFilter->exists());

        rewind($this->errorStream);
        $this->assertEmpty(stream_get_contents($this->errorStream));
    }

    public function testCallToInvokeWillCreateOnlyDeleteResourceInputFilter(): void
    {
        fwrite($this->inputStream, $this->resourceName . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'yes' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        rewind($this->inputStream);

        $createResourceInputFilter = $this->fileSystem->createResourceInputFilter($this->resourceName);
        $this->assertFileDoesNotExist($createResourceInputFilter->getPath());
        $this->assertFalse($createResourceInputFilter->exists());

        $deleteResourceInputFilter = $this->fileSystem->deleteResourceInputFilter($this->resourceName);
        $this->assertFileDoesNotExist($deleteResourceInputFilter->getPath());
        $this->assertFalse($deleteResourceInputFilter->exists());

        $editResourceInputFilter = $this->fileSystem->editResourceInputFilter($this->resourceName);
        $this->assertFileDoesNotExist($editResourceInputFilter->getPath());
        $this->assertFalse($editResourceInputFilter->exists());

        $replaceResourceInputFilter = $this->fileSystem->replaceResourceInputFilter($this->resourceName);
        $this->assertFileDoesNotExist($replaceResourceInputFilter->getPath());
        $this->assertFalse($replaceResourceInputFilter->exists());

        $inputFilter = new InputFilter($this->fileSystem, $this->context, $this->config, $this->module);
        $inputFilter();

        $this->assertFileDoesNotExist($createResourceInputFilter->getPath());
        $this->assertFalse($createResourceInputFilter->exists());

        $this->assertFileExists($deleteResourceInputFilter->getPath());
        $this->assertTrue($deleteResourceInputFilter->exists());
        $this->assertSame($this->dataProviderDeleteResourceInputFilter(), $deleteResourceInputFilter->read());

        $this->assertFileDoesNotExist($editResourceInputFilter->getPath());
        $this->assertFalse($editResourceInputFilter->exists());

        $this->assertFileDoesNotExist($replaceResourceInputFilter->getPath());
        $this->assertFalse($replaceResourceInputFilter->exists());

        rewind($this->errorStream);
        $this->assertEmpty(stream_get_contents($this->errorStream));
    }

    public function testCallToInvokeWillCreateOnlyEditResourceInputFilter(): void
    {
        fwrite($this->inputStream, $this->resourceName . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'yes' . PHP_EOL);
        rewind($this->inputStream);

        $createResourceInputFilter = $this->fileSystem->createResourceInputFilter($this->resourceName);
        $this->assertFileDoesNotExist($createResourceInputFilter->getPath());
        $this->assertFalse($createResourceInputFilter->exists());

        $deleteResourceInputFilter = $this->fileSystem->deleteResourceInputFilter($this->resourceName);
        $this->assertFileDoesNotExist($deleteResourceInputFilter->getPath());
        $this->assertFalse($deleteResourceInputFilter->exists());

        $editResourceInputFilter = $this->fileSystem->editResourceInputFilter($this->resourceName);
        $this->assertFileDoesNotExist($editResourceInputFilter->getPath());
        $this->assertFalse($editResourceInputFilter->exists());

        $replaceResourceInputFilter = $this->fileSystem->replaceResourceInputFilter($this->resourceName);
        $this->assertFileDoesNotExist($replaceResourceInputFilter->getPath());
        $this->assertFalse($replaceResourceInputFilter->exists());

        $inputFilter = new InputFilter($this->fileSystem, $this->context, $this->config, $this->module);
        $inputFilter();

        $this->assertFileDoesNotExist($createResourceInputFilter->getPath());
        $this->assertFalse($createResourceInputFilter->exists());

        $this->assertFileDoesNotExist($deleteResourceInputFilter->getPath());
        $this->assertFalse($deleteResourceInputFilter->exists());

        $this->assertFileExists($editResourceInputFilter->getPath());
        $this->assertTrue($editResourceInputFilter->exists());
        $this->assertSame($this->dataProviderEditResourceInputFilter(), $editResourceInputFilter->read());

        $this->assertFileDoesNotExist($replaceResourceInputFilter->getPath());
        $this->assertFalse($replaceResourceInputFilter->exists());

        rewind($this->errorStream);
        $this->assertEmpty(stream_get_contents($this->errorStream));
    }

    public function testCallToInvokeWillNotCreateReplaceResourceInputFilter(): void
    {
        fwrite($this->inputStream, $this->resourceName . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        rewind($this->inputStream);

        $createResourceInputFilter = $this->fileSystem->createResourceInputFilter($this->resourceName);
        $this->assertFileDoesNotExist($createResourceInputFilter->getPath());
        $this->assertFalse($createResourceInputFilter->exists());

        $deleteResourceInputFilter = $this->fileSystem->deleteResourceInputFilter($this->resourceName);
        $this->assertFileDoesNotExist($deleteResourceInputFilter->getPath());
        $this->assertFalse($deleteResourceInputFilter->exists());

        $editResourceInputFilter = $this->fileSystem->editResourceInputFilter($this->resourceName);
        $this->assertFileDoesNotExist($editResourceInputFilter->getPath());
        $this->assertFalse($editResourceInputFilter->exists());

        $replaceResourceInputFilter = $this->fileSystem->replaceResourceInputFilter($this->resourceName);
        $this->assertFileDoesNotExist($replaceResourceInputFilter->getPath());
        $this->assertFalse($replaceResourceInputFilter->exists());

        $inputFilter = new InputFilter($this->fileSystem, $this->context, $this->config, $this->module);
        $inputFilter();

        $this->assertFileDoesNotExist($createResourceInputFilter->getPath());
        $this->assertFalse($createResourceInputFilter->exists());

        $this->assertFileDoesNotExist($deleteResourceInputFilter->getPath());
        $this->assertFalse($deleteResourceInputFilter->exists());

        $this->assertFileDoesNotExist($editResourceInputFilter->getPath());
        $this->assertFalse($editResourceInputFilter->exists());

        $this->assertFileDoesNotExist($replaceResourceInputFilter->getPath());
        $this->assertFalse($replaceResourceInputFilter->exists());

        rewind($this->errorStream);
        $this->assertEmpty(stream_get_contents($this->errorStream));
    }

    public function testCallToCreateWillCreateOnlyCreateResourceInputFilter(): void
    {
        fwrite($this->inputStream, 'yes' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        rewind($this->inputStream);

        $createResourceInputFilter = $this->fileSystem->createResourceInputFilter($this->resourceName);
        $this->assertFileDoesNotExist($createResourceInputFilter->getPath());
        $this->assertFalse($createResourceInputFilter->exists());

        $deleteResourceInputFilter = $this->fileSystem->deleteResourceInputFilter($this->resourceName);
        $this->assertFileDoesNotExist($deleteResourceInputFilter->getPath());
        $this->assertFalse($deleteResourceInputFilter->exists());

        $editResourceInputFilter = $this->fileSystem->editResourceInputFilter($this->resourceName);
        $this->assertFileDoesNotExist($editResourceInputFilter->getPath());
        $this->assertFalse($editResourceInputFilter->exists());

        $replaceResourceInputFilter = $this->fileSystem->replaceResourceInputFilter($this->resourceName);
        $this->assertFileDoesNotExist($replaceResourceInputFilter->getPath());
        $this->assertFalse($replaceResourceInputFilter->exists());

        $inputFilter = new InputFilter($this->fileSystem, $this->context, $this->config, $this->module);
        $inputFilter->create($this->resourceName);

        $this->assertFileExists($createResourceInputFilter->getPath());
        $this->assertTrue($createResourceInputFilter->exists());
        $this->assertSame($this->dataProviderCreateResourceInputFilter(), $createResourceInputFilter->read());

        $this->assertFileDoesNotExist($deleteResourceInputFilter->getPath());
        $this->assertFalse($deleteResourceInputFilter->exists());

        $this->assertFileDoesNotExist($editResourceInputFilter->getPath());
        $this->assertFalse($editResourceInputFilter->exists());

        $this->assertFileDoesNotExist($replaceResourceInputFilter->getPath());
        $this->assertFalse($replaceResourceInputFilter->exists());

        rewind($this->errorStream);
        $this->assertEmpty(stream_get_contents($this->errorStream));
    }

    public function testCallToCreateWillCreateOnlyDeleteResourceInputFilter(): void
    {
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'yes' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        rewind($this->inputStream);

        $createResourceInputFilter = $this->fileSystem->createResourceInputFilter($this->resourceName);
        $this->assertFileDoesNotExist($createResourceInputFilter->getPath());
        $this->assertFalse($createResourceInputFilter->exists());

        $deleteResourceInputFilter = $this->fileSystem->deleteResourceInputFilter($this->resourceName);
        $this->assertFileDoesNotExist($deleteResourceInputFilter->getPath());
        $this->assertFalse($deleteResourceInputFilter->exists());

        $editResourceInputFilter = $this->fileSystem->editResourceInputFilter($this->resourceName);
        $this->assertFileDoesNotExist($editResourceInputFilter->getPath());
        $this->assertFalse($editResourceInputFilter->exists());

        $replaceResourceInputFilter = $this->fileSystem->replaceResourceInputFilter($this->resourceName);
        $this->assertFileDoesNotExist($replaceResourceInputFilter->getPath());
        $this->assertFalse($replaceResourceInputFilter->exists());

        $inputFilter = new InputFilter($this->fileSystem, $this->context, $this->config, $this->module);
        $inputFilter->create($this->resourceName);

        $this->assertFileDoesNotExist($createResourceInputFilter->getPath());
        $this->assertFalse($createResourceInputFilter->exists());

        $this->assertFileExists($deleteResourceInputFilter->getPath());
        $this->assertTrue($deleteResourceInputFilter->exists());
        $this->assertSame($this->dataProviderDeleteResourceInputFilter(), $deleteResourceInputFilter->read());

        $this->assertFileDoesNotExist($editResourceInputFilter->getPath());
        $this->assertFalse($editResourceInputFilter->exists());

        $this->assertFileDoesNotExist($replaceResourceInputFilter->getPath());
        $this->assertFalse($replaceResourceInputFilter->exists());

        rewind($this->errorStream);
        $this->assertEmpty(stream_get_contents($this->errorStream));
    }

    public function testCallToCreateWillCreateOnlyEditResourceInputFilter(): void
    {
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'yes' . PHP_EOL);
        rewind($this->inputStream);

        $createResourceInputFilter = $this->fileSystem->createResourceInputFilter($this->resourceName);
        $this->assertFileDoesNotExist($createResourceInputFilter->getPath());
        $this->assertFalse($createResourceInputFilter->exists());

        $deleteResourceInputFilter = $this->fileSystem->deleteResourceInputFilter($this->resourceName);
        $this->assertFileDoesNotExist($deleteResourceInputFilter->getPath());
        $this->assertFalse($deleteResourceInputFilter->exists());

        $editResourceInputFilter = $this->fileSystem->editResourceInputFilter($this->resourceName);
        $this->assertFileDoesNotExist($editResourceInputFilter->getPath());
        $this->assertFalse($editResourceInputFilter->exists());

        $replaceResourceInputFilter = $this->fileSystem->replaceResourceInputFilter($this->resourceName);
        $this->assertFileDoesNotExist($replaceResourceInputFilter->getPath());
        $this->assertFalse($replaceResourceInputFilter->exists());

        $inputFilter = new InputFilter($this->fileSystem, $this->context, $this->config, $this->module);
        $inputFilter->create($this->resourceName);

        $this->assertFileDoesNotExist($createResourceInputFilter->getPath());
        $this->assertFalse($createResourceInputFilter->exists());

        $this->assertFileDoesNotExist($deleteResourceInputFilter->getPath());
        $this->assertFalse($deleteResourceInputFilter->exists());

        $this->assertFileExists($editResourceInputFilter->getPath());
        $this->assertTrue($editResourceInputFilter->exists());
        $this->assertSame($this->dataProviderEditResourceInputFilter(), $editResourceInputFilter->read());

        $this->assertFileDoesNotExist($replaceResourceInputFilter->getPath());
        $this->assertFalse($replaceResourceInputFilter->exists());

        rewind($this->errorStream);
        $this->assertEmpty(stream_get_contents($this->errorStream));
    }

    public function testCallToCreateWillNotCreateReplaceResourceInputFilter(): void
    {
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        rewind($this->inputStream);

        $createResourceInputFilter = $this->fileSystem->createResourceInputFilter($this->resourceName);
        $this->assertFileDoesNotExist($createResourceInputFilter->getPath());
        $this->assertFalse($createResourceInputFilter->exists());

        $deleteResourceInputFilter = $this->fileSystem->deleteResourceInputFilter($this->resourceName);
        $this->assertFileDoesNotExist($deleteResourceInputFilter->getPath());
        $this->assertFalse($deleteResourceInputFilter->exists());

        $editResourceInputFilter = $this->fileSystem->editResourceInputFilter($this->resourceName);
        $this->assertFileDoesNotExist($editResourceInputFilter->getPath());
        $this->assertFalse($editResourceInputFilter->exists());

        $replaceResourceInputFilter = $this->fileSystem->replaceResourceInputFilter($this->resourceName);
        $this->assertFileDoesNotExist($replaceResourceInputFilter->getPath());
        $this->assertFalse($replaceResourceInputFilter->exists());

        $inputFilter = new InputFilter($this->fileSystem, $this->context, $this->config, $this->module);
        $inputFilter->create($this->resourceName);

        $this->assertFileDoesNotExist($createResourceInputFilter->getPath());
        $this->assertFalse($createResourceInputFilter->exists());

        $this->assertFileDoesNotExist($deleteResourceInputFilter->getPath());
        $this->assertFalse($deleteResourceInputFilter->exists());

        $this->assertFileDoesNotExist($editResourceInputFilter->getPath());
        $this->assertFalse($editResourceInputFilter->exists());

        $this->assertFileDoesNotExist($replaceResourceInputFilter->getPath());
        $this->assertFalse($replaceResourceInputFilter->exists());

        rewind($this->errorStream);
        $this->assertEmpty(stream_get_contents($this->errorStream));
    }

    private function dataProviderCreateResourceInputFilter(): string
    {
        return <<<BODY
<?php

declare(strict_types=1);

namespace Admin\ModuleName\InputFilter;

use {$this->import->getCsrfInputFqcn()};
use {$this->import->getAbstractInputFilterFqcn()};

/**
 * @phpstan-type CreateBookStoreDataType array{}
 * @extends AbstractInputFilter<CreateBookStoreDataType>
 */
class CreateBookStoreInputFilter extends AbstractInputFilter
{
    public function init(): self
    {
        // chain inputs below

        return \$this
            ->add(new CsrfInput('createBookStoreCsrf', true));
    }
}

BODY;
    }

    private function dataProviderDeleteResourceInputFilter(): string
    {
        $confirmDeleteInput = $this->fileSystem->input('ConfirmDeleteBookStoreInput');

        return <<<BODY
<?php

declare(strict_types=1);

namespace Admin\ModuleName\InputFilter;

use {$this->import->getCsrfInputFqcn()};
use {$confirmDeleteInput->getComponent()->getFqcn()};
use {$this->import->getAbstractInputFilterFqcn()};

/**
 * @phpstan-type DeleteBookStoreDataType array{}
 * @extends AbstractInputFilter<DeleteBookStoreDataType>
 */
class DeleteBookStoreInputFilter extends AbstractInputFilter
{
    public function init(): self
    {
        // chain inputs below

        return \$this
            ->add(new ConfirmDeleteBookStoreInput('confirmation'))
            ->add(new CsrfInput('deleteBookStoreCsrf', true));
    }
}

BODY;
    }

    private function dataProviderEditResourceInputFilter(): string
    {
        return <<<BODY
<?php

declare(strict_types=1);

namespace Admin\ModuleName\InputFilter;

use {$this->import->getCsrfInputFqcn()};
use {$this->import->getAbstractInputFilterFqcn()};

/**
 * @phpstan-type EditBookStoreDataType array{}
 * @extends AbstractInputFilter<EditBookStoreDataType>
 */
class EditBookStoreInputFilter extends AbstractInputFilter
{
    public function init(): self
    {
        // chain inputs below

        return \$this
            ->add(new CsrfInput('editBookStoreCsrf', true));
    }
}

BODY;
    }
}
