<?php

declare(strict_types=1);

namespace DotTest\Maker\Type;

use Dot\Maker\ColorEnum;
use Dot\Maker\Component\Import;
use Dot\Maker\Config;
use Dot\Maker\Context;
use Dot\Maker\FileSystem;
use Dot\Maker\IO\Input;
use Dot\Maker\IO\Output;
use Dot\Maker\Type\Form;
use Dot\Maker\Type\Module;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

use function fclose;
use function fopen;
use function fwrite;
use function implode;
use function rewind;
use function sprintf;
use function stream_get_contents;

use const PHP_EOL;

class FormTest extends TestCase
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

    public function testCallToInvokeWillEarlyReturnWhenProjectTypeIsApi(): void
    {
        $root = vfsStream::setup('root', 0644, [
            'composer.json' => '{
                "autoload": {
                    "psr-4": {
                        "Api\\\\App\\\\": "src/App/src/"
                    }
                }
            }',
        ]);

        $this->config     = new Config($root->url());
        $this->context    = new Context($root->url());
        $this->fileSystem = (new FileSystem($this->context))->setModuleName($this->moduleName);
        $this->import     = new Import($this->context);
        $this->module     = new Module($this->fileSystem, $this->context, $this->config);

        $form = new Form($this->fileSystem, $this->context, $this->config, $this->module);
        $form();

        rewind($this->errorStream);
        $this->assertSame(
            ColorEnum::colorize('Cannot create Forms in an API', ColorEnum::ForegroundBrightRed) . PHP_EOL,
            stream_get_contents($this->errorStream)
        );
    }

    public function testCallToInvokeWillNotCreateFileOnEmptyInput(): void
    {
        $root = vfsStream::setup('root', 0644, [
            'composer.json' => '{
                "autoload": {
                    "psr-4": {
                        "Admin\\\\App\\\\": "src/App/src/"
                    }
                }
            }',
        ]);

        $this->config     = new Config($root->url());
        $this->context    = new Context($root->url());
        $this->fileSystem = (new FileSystem($this->context))->setModuleName($this->moduleName);
        $this->import     = new Import($this->context);
        $this->module     = new Module($this->fileSystem, $this->context, $this->config);

        $file = $this->fileSystem->form($this->resourceName);
        $this->assertFileDoesNotExist($file->getPath());
        $this->assertFalse($file->exists());

        fwrite($this->inputStream, PHP_EOL);
        rewind($this->inputStream);

        $form = new Form($this->fileSystem, $this->context, $this->config, $this->module);
        $form();

        rewind($this->errorStream);
        $this->assertEmpty(stream_get_contents($this->errorStream));
        $this->assertFalse($file->exists());
        $this->assertFileDoesNotExist($file->getPath());
    }

    public function testCallToInvokeWillOutputErrorAndWillNotCreateFileWhenNameIsInvalid(): void
    {
        $root = vfsStream::setup('root', 0644, [
            'composer.json' => '{
                "autoload": {
                    "psr-4": {
                        "Admin\\\\App\\\\": "src/App/src/"
                    }
                }
            }',
        ]);

        $this->config     = new Config($root->url());
        $this->context    = new Context($root->url());
        $this->fileSystem = (new FileSystem($this->context))->setModuleName($this->moduleName);
        $this->import     = new Import($this->context);
        $this->module     = new Module($this->fileSystem, $this->context, $this->config);

        $file = $this->fileSystem->form($this->resourceName);
        $this->assertFalse($file->exists());
        $this->assertFileDoesNotExist($file->getPath());

        fwrite($this->inputStream, '.' . PHP_EOL);
        rewind($this->inputStream);

        $form = new Form($this->fileSystem, $this->context, $this->config, $this->module);
        $form();

        rewind($this->errorStream);
        $this->assertStringContainsString('Invalid Form name: "."', stream_get_contents($this->errorStream));
        $this->assertFalse($file->exists());
        $this->assertFileDoesNotExist($file->getPath());
    }

    public function testCallToInvokeWillCreateOnlyCreateResourceForm(): void
    {
        $root = vfsStream::setup('root', 0644, [
            'composer.json' => '{
                "autoload": {
                    "psr-4": {
                        "Admin\\\\App\\\\": "src/App/src/"
                    }
                }
            }',
        ]);

        $this->config     = new Config($root->url());
        $this->context    = new Context($root->url());
        $this->fileSystem = (new FileSystem($this->context))->setModuleName($this->moduleName);
        $this->import     = new Import($this->context);
        $this->module     = new Module($this->fileSystem, $this->context, $this->config);

        fwrite($this->inputStream, $this->resourceName . PHP_EOL);
        fwrite($this->inputStream, 'yes' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        rewind($this->inputStream);

        $createResourceForm = $this->fileSystem->createResourceForm($this->resourceName);
        $this->assertFileDoesNotExist($createResourceForm->getPath());
        $this->assertFalse($createResourceForm->exists());

        $createResourceInputFilter = $this->fileSystem->createResourceInputFilter($this->resourceName);
        $this->assertFileDoesNotExist($createResourceInputFilter->getPath());
        $this->assertFalse($createResourceInputFilter->exists());

        $deleteResourceForm = $this->fileSystem->deleteResourceForm($this->resourceName);
        $this->assertFileDoesNotExist($deleteResourceForm->getPath());
        $this->assertFalse($deleteResourceForm->exists());

        $editResourceForm = $this->fileSystem->editResourceForm($this->resourceName);
        $this->assertFileDoesNotExist($editResourceForm->getPath());
        $this->assertFalse($editResourceForm->exists());

        $form = new Form($this->fileSystem, $this->context, $this->config, $this->module);
        $form();

        $this->assertFileExists($createResourceForm->getPath());
        $this->assertTrue($createResourceForm->exists());
        $this->assertSame($this->dataProviderCreateResourceForm(), $createResourceForm->read());

        $createResourceInputFilter = $this->fileSystem->createResourceInputFilter($this->resourceName);
        $this->assertFileExists($createResourceInputFilter->getPath());
        $this->assertTrue($createResourceInputFilter->exists());

        $this->assertFileDoesNotExist($deleteResourceForm->getPath());
        $this->assertFalse($deleteResourceForm->exists());

        $this->assertFileDoesNotExist($editResourceForm->getPath());
        $this->assertFalse($editResourceForm->exists());

        rewind($this->errorStream);
        $this->assertEmpty(stream_get_contents($this->errorStream));
    }

    public function testCallToInvokeWillCreateOnlyDeleteResourceForm(): void
    {
        $root = vfsStream::setup('root', 0644, [
            'composer.json' => '{
                "autoload": {
                    "psr-4": {
                        "Admin\\\\App\\\\": "src/App/src/"
                    }
                }
            }',
        ]);

        $this->config     = new Config($root->url());
        $this->context    = new Context($root->url());
        $this->fileSystem = (new FileSystem($this->context))->setModuleName($this->moduleName);
        $this->import     = new Import($this->context);
        $this->module     = new Module($this->fileSystem, $this->context, $this->config);

        fwrite($this->inputStream, $this->resourceName . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'yes' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        rewind($this->inputStream);

        $createResourceForm = $this->fileSystem->createResourceForm($this->resourceName);
        $this->assertFileDoesNotExist($createResourceForm->getPath());
        $this->assertFalse($createResourceForm->exists());

        $deleteResourceForm = $this->fileSystem->deleteResourceForm($this->resourceName);
        $this->assertFileDoesNotExist($deleteResourceForm->getPath());
        $this->assertFalse($deleteResourceForm->exists());

        $deleteResourceInputFilter = $this->fileSystem->deleteResourceInputFilter($this->resourceName);
        $this->assertFileDoesNotExist($deleteResourceInputFilter->getPath());
        $this->assertFalse($deleteResourceInputFilter->exists());

        $confirmDeleteInput = $this->fileSystem->confirmDeleteInput($this->resourceName);
        $this->assertFileDoesNotExist($confirmDeleteInput->getPath());
        $this->assertFalse($confirmDeleteInput->exists());

        $editResourceForm = $this->fileSystem->editResourceInputFilter($this->resourceName);
        $this->assertFileDoesNotExist($editResourceForm->getPath());
        $this->assertFalse($editResourceForm->exists());

        $form = new Form($this->fileSystem, $this->context, $this->config, $this->module);
        $form();

        $this->assertFileDoesNotExist($createResourceForm->getPath());
        $this->assertFalse($createResourceForm->exists());

        $this->assertFileExists($deleteResourceForm->getPath());
        $this->assertTrue($deleteResourceForm->exists());
        $this->assertSame($this->dataProviderDeleteResourceForm(), $deleteResourceForm->read());

        $this->assertFileExists($deleteResourceInputFilter->getPath());
        $this->assertTrue($deleteResourceInputFilter->exists());

        $this->assertFileExists($confirmDeleteInput->getPath());
        $this->assertTrue($confirmDeleteInput->exists());

        $this->assertFileDoesNotExist($editResourceForm->getPath());
        $this->assertFalse($editResourceForm->exists());

        rewind($this->errorStream);
        $this->assertEmpty(stream_get_contents($this->errorStream));
    }

    public function testCallToInvokeWillCreateOnlyEditResourceForm(): void
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

        fwrite($this->inputStream, $this->resourceName . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'yes' . PHP_EOL);
        rewind($this->inputStream);

        $createResourceForm = $this->fileSystem->createResourceForm($this->resourceName);
        $this->assertFileDoesNotExist($createResourceForm->getPath());
        $this->assertFalse($createResourceForm->exists());

        $deleteResourceForm = $this->fileSystem->deleteResourceForm($this->resourceName);
        $this->assertFileDoesNotExist($deleteResourceForm->getPath());
        $this->assertFalse($deleteResourceForm->exists());

        $editResourceForm = $this->fileSystem->editResourceForm($this->resourceName);
        $this->assertFileDoesNotExist($editResourceForm->getPath());
        $this->assertFalse($editResourceForm->exists());

        $editResourceInputFilter = $this->fileSystem->editResourceInputFilter($this->resourceName);
        $this->assertFileDoesNotExist($editResourceInputFilter->getPath());
        $this->assertFalse($editResourceInputFilter->exists());

        $form = new Form($this->fileSystem, $this->context, $this->config, $this->module);
        $form();

        $this->assertFileDoesNotExist($createResourceForm->getPath());
        $this->assertFalse($createResourceForm->exists());

        $this->assertFileDoesNotExist($deleteResourceForm->getPath());
        $this->assertFalse($deleteResourceForm->exists());

        $this->assertFileExists($editResourceForm->getPath());
        $this->assertTrue($editResourceForm->exists());
        $this->assertSame($this->dataProviderEditResourceForm(), $editResourceForm->read());

        $this->assertFileExists($editResourceInputFilter->getPath());
        $this->assertTrue($editResourceInputFilter->exists());

        rewind($this->errorStream);
        $this->assertEmpty(stream_get_contents($this->errorStream));
    }

    public function testCallToCreateWillCreateOnlyCreateResourceForm(): void
    {
        $root = vfsStream::setup('root', 0644, [
            'composer.json' => '{
                "autoload": {
                    "psr-4": {
                        "Admin\\\\App\\\\": "src/App/src/"
                    }
                }
            }',
        ]);

        $this->config     = new Config($root->url());
        $this->context    = new Context($root->url());
        $this->fileSystem = (new FileSystem($this->context))->setModuleName($this->moduleName);
        $this->import     = new Import($this->context);
        $this->module     = new Module($this->fileSystem, $this->context, $this->config);

        fwrite($this->inputStream, $this->resourceName . PHP_EOL);
        fwrite($this->inputStream, 'yes' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        rewind($this->inputStream);

        $createResourceForm = $this->fileSystem->createResourceForm($this->resourceName);
        $this->assertFileDoesNotExist($createResourceForm->getPath());
        $this->assertFalse($createResourceForm->exists());

        $createResourceInputFilter = $this->fileSystem->createResourceInputFilter($this->resourceName);
        $this->assertFileDoesNotExist($createResourceInputFilter->getPath());
        $this->assertFalse($createResourceInputFilter->exists());

        $deleteResourceForm = $this->fileSystem->deleteResourceForm($this->resourceName);
        $this->assertFileDoesNotExist($deleteResourceForm->getPath());
        $this->assertFalse($deleteResourceForm->exists());

        $editResourceForm = $this->fileSystem->editResourceForm($this->resourceName);
        $this->assertFileDoesNotExist($editResourceForm->getPath());
        $this->assertFalse($editResourceForm->exists());

        $form = new Form($this->fileSystem, $this->context, $this->config, $this->module);
        $form->create($this->resourceName);

        $this->assertFileExists($createResourceForm->getPath());
        $this->assertTrue($createResourceForm->exists());
        $this->assertSame($this->dataProviderCreateResourceForm(), $createResourceForm->read());

        $this->assertFileExists($createResourceInputFilter->getPath());
        $this->assertTrue($createResourceInputFilter->exists());

        $this->assertFileDoesNotExist($deleteResourceForm->getPath());
        $this->assertFalse($deleteResourceForm->exists());

        $this->assertFileDoesNotExist($editResourceForm->getPath());
        $this->assertFalse($editResourceForm->exists());

        rewind($this->errorStream);
        $this->assertEmpty(stream_get_contents($this->errorStream));
    }

    public function testCallToCreateWillCreateOnlyDeleteResourceForm(): void
    {
        $root = vfsStream::setup('root', 0644, [
            'composer.json' => '{
                "autoload": {
                    "psr-4": {
                        "Admin\\\\App\\\\": "src/App/src/"
                    }
                }
            }',
        ]);

        $this->config     = new Config($root->url());
        $this->context    = new Context($root->url());
        $this->fileSystem = (new FileSystem($this->context))->setModuleName($this->moduleName);
        $this->import     = new Import($this->context);
        $this->module     = new Module($this->fileSystem, $this->context, $this->config);

        fwrite($this->inputStream, $this->resourceName . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'yes' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        rewind($this->inputStream);

        $createResourceForm = $this->fileSystem->createResourceForm($this->resourceName);
        $this->assertFileDoesNotExist($createResourceForm->getPath());
        $this->assertFalse($createResourceForm->exists());

        $deleteResourceForm = $this->fileSystem->deleteResourceForm($this->resourceName);
        $this->assertFileDoesNotExist($deleteResourceForm->getPath());
        $this->assertFalse($deleteResourceForm->exists());

        $deleteResourceInputFilter = $this->fileSystem->deleteResourceInputFilter($this->resourceName);
        $this->assertFileDoesNotExist($deleteResourceInputFilter->getPath());
        $this->assertFalse($deleteResourceInputFilter->exists());

        $confirmDeleteInput = $this->fileSystem->confirmDeleteInput($this->resourceName);
        $this->assertFileDoesNotExist($confirmDeleteInput->getPath());
        $this->assertFalse($confirmDeleteInput->exists());

        $editResourceForm = $this->fileSystem->editResourceInputFilter($this->resourceName);
        $this->assertFileDoesNotExist($editResourceForm->getPath());
        $this->assertFalse($editResourceForm->exists());

        $form = new Form($this->fileSystem, $this->context, $this->config, $this->module);
        $form->create($this->resourceName);

        $this->assertFileDoesNotExist($createResourceForm->getPath());
        $this->assertFalse($createResourceForm->exists());

        $this->assertFileExists($deleteResourceForm->getPath());
        $this->assertTrue($deleteResourceForm->exists());
        $this->assertSame($this->dataProviderDeleteResourceForm(), $deleteResourceForm->read());

        $this->assertFileExists($deleteResourceInputFilter->getPath());
        $this->assertTrue($deleteResourceInputFilter->exists());

        $this->assertFileExists($confirmDeleteInput->getPath());
        $this->assertTrue($confirmDeleteInput->exists());

        $this->assertFileDoesNotExist($editResourceForm->getPath());
        $this->assertFalse($editResourceForm->exists());

        rewind($this->errorStream);
        $this->assertEmpty(stream_get_contents($this->errorStream));
    }

    public function testCallToCreateWillCreateOnlyEditResourceForm(): void
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

        fwrite($this->inputStream, $this->resourceName . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'no' . PHP_EOL);
        fwrite($this->inputStream, 'yes' . PHP_EOL);
        rewind($this->inputStream);

        $createResourceForm = $this->fileSystem->createResourceForm($this->resourceName);
        $this->assertFileDoesNotExist($createResourceForm->getPath());
        $this->assertFalse($createResourceForm->exists());

        $deleteResourceForm = $this->fileSystem->deleteResourceForm($this->resourceName);
        $this->assertFileDoesNotExist($deleteResourceForm->getPath());
        $this->assertFalse($deleteResourceForm->exists());

        $editResourceForm = $this->fileSystem->editResourceForm($this->resourceName);
        $this->assertFileDoesNotExist($editResourceForm->getPath());
        $this->assertFalse($editResourceForm->exists());

        $editResourceInputFilter = $this->fileSystem->editResourceInputFilter($this->resourceName);
        $this->assertFileDoesNotExist($editResourceInputFilter->getPath());
        $this->assertFalse($editResourceInputFilter->exists());

        $form = new Form($this->fileSystem, $this->context, $this->config, $this->module);
        $form->create($this->resourceName);

        $this->assertFileDoesNotExist($createResourceForm->getPath());
        $this->assertFalse($createResourceForm->exists());

        $this->assertFileDoesNotExist($deleteResourceForm->getPath());
        $this->assertFalse($deleteResourceForm->exists());

        $this->assertFileExists($editResourceForm->getPath());
        $this->assertTrue($editResourceForm->exists());
        $this->assertSame($this->dataProviderEditResourceForm(), $editResourceForm->read());

        $this->assertFileExists($editResourceInputFilter->getPath());
        $this->assertTrue($editResourceInputFilter->exists());

        rewind($this->errorStream);
        $this->assertEmpty(stream_get_contents($this->errorStream));
    }

    private function dataProviderCreateResourceForm(): string
    {
        $uses = [
            sprintf('use %s;', $this->import->getAbstractFormFqcn()),
            sprintf('use %s;', $this->fileSystem->inputFilter('CreateBookStore')->getComponent()->getFqcn()),
            sprintf('use %s;', Import::LAMINAS_FORM_ELEMENT_CSRF),
            sprintf('use %s;', Import::LAMINAS_FORM_ELEMENT_SUBMIT),
            sprintf('use %s;', Import::LAMINAS_FORM_EXCEPTION_EXCEPTIONINTERFACE),
            sprintf('use %s;', Import::LAMINAS_SESSION_CONTAINER),
        ];
        $uses = implode(PHP_EOL, $uses);

        return <<<BODY
<?php

declare(strict_types=1);

namespace Admin\ModuleName\Form;

{$uses}

/**
 * @phpstan-import-type CreateBookStoreDataType from CreateBookStoreInputFilter
 * @extends AbstractForm<CreateBookStoreDataType>
 */
class CreateBookStoreForm extends AbstractForm
{
    /**
     * @throws ExceptionInterface
     */
    public function __construct(
        ?string \$name = null,
        array \$options = [],
    ) {
        parent::__construct(\$name, \$options);

        \$this->init();

        \$this->setAttribute('id', 'book-store-form');
        \$this->setAttribute('class', 'row g-3 needs-validation');
        \$this->setAttribute('novalidate', 'novalidate');

        \$this->inputFilter = new CreateBookStoreInputFilter();
        \$this->inputFilter->init();
    }

    /**
     * @throws ExceptionInterface
     */
    public function init(): void
    {
        // add more form elements

        \$this->add(
            (new Csrf('createBookStoreCsrf'))
                ->setOptions([
                    'csrf_options' => ['timeout' => 3600, 'session' => new Container()],
                ])
                ->setAttribute('required', true)
        );
        \$this->add(
            (new Submit('submit'))
                ->setAttribute('type', 'submit')
                ->setAttribute('value', 'Save')
                ->setAttribute('class', 'btn btn-primary btn-color btn-sm')
        );
    }
}

BODY;
    }

    private function dataProviderDeleteResourceForm(): string
    {
        $uses = [
            sprintf('use %s;', $this->import->getAbstractFormFqcn()),
            sprintf('use %s;', $this->fileSystem->inputFilter('DeleteBookStore')->getComponent()->getFqcn()),
            sprintf('use %s;', Import::LAMINAS_FORM_ELEMENT_CHECKBOX),
            sprintf('use %s;', Import::LAMINAS_FORM_ELEMENT_CSRF),
            sprintf('use %s;', Import::LAMINAS_FORM_ELEMENT_SUBMIT),
            sprintf('use %s;', Import::LAMINAS_FORM_EXCEPTION_EXCEPTIONINTERFACE),
            sprintf('use %s;', Import::LAMINAS_SESSION_CONTAINER),
        ];
        $uses = implode(PHP_EOL, $uses);

        return <<<BODY
<?php

declare(strict_types=1);

namespace Admin\ModuleName\Form;

{$uses}

/**
 * @phpstan-import-type DeleteBookStoreDataType from DeleteBookStoreInputFilter
 * @extends AbstractForm<DeleteBookStoreDataType>
 */
class DeleteBookStoreForm extends AbstractForm
{
    /**
     * @throws ExceptionInterface
     */
    public function __construct(
        ?string \$name = null,
        array \$options = [],
    ) {
        parent::__construct(\$name, \$options);

        \$this->init();

        \$this->setAttribute('id', 'book-store-form');
        \$this->setAttribute('class', 'row g-3 needs-validation');
        \$this->setAttribute('novalidate', 'novalidate');

        \$this->inputFilter = new DeleteBookStoreInputFilter();
        \$this->inputFilter->init();
    }

    /**
     * @throws ExceptionInterface
     */
    public function init(): void
    {
        // add more form elements

        \$this->add(
            (new Checkbox('confirmation'))
                ->setCheckedValue('yes')
                ->setUncheckedValue('no')
                ->setAttribute('id', 'confirmation')
                ->setAttribute('class', 'form-check-input')
                ->setAttribute('required', true)
                ->setValue('no')
        );
        \$this->add(
            (new Csrf('deleteBookStoreCsrf'))
                ->setOptions([
                    'csrf_options' => ['timeout' => 3600, 'session' => new Container()],
                ])
                ->setAttribute('required', true)
        );
        \$this->add(
            (new Submit('submit'))
                ->setAttribute('type', 'submit')
                ->setAttribute('value', 'Delete')
                ->setAttribute('class', 'btn btn-primary btn-color btn-sm')
        );
    }
}

BODY;
    }

    private function dataProviderEditResourceForm(): string
    {
        $uses = [
            sprintf('use %s;', $this->import->getAbstractFormFqcn()),
            sprintf('use %s;', $this->fileSystem->inputFilter('EditBookStore')->getComponent()->getFqcn()),
            sprintf('use %s;', Import::LAMINAS_FORM_ELEMENT_CSRF),
            sprintf('use %s;', Import::LAMINAS_FORM_ELEMENT_SUBMIT),
            sprintf('use %s;', Import::LAMINAS_FORM_EXCEPTION_EXCEPTIONINTERFACE),
            sprintf('use %s;', Import::LAMINAS_SESSION_CONTAINER),
        ];
        $uses = implode(PHP_EOL, $uses);

        return <<<BODY
<?php

declare(strict_types=1);

namespace Admin\ModuleName\Form;

{$uses}

/**
 * @phpstan-import-type EditBookStoreDataType from EditBookStoreInputFilter
 * @extends AbstractForm<EditBookStoreDataType>
 */
class EditBookStoreForm extends AbstractForm
{
    /**
     * @throws ExceptionInterface
     */
    public function __construct(
        ?string \$name = null,
        array \$options = [],
    ) {
        parent::__construct(\$name, \$options);

        \$this->init();

        \$this->setAttribute('id', 'book-store-form');
        \$this->setAttribute('class', 'row g-3 needs-validation');
        \$this->setAttribute('novalidate', 'novalidate');

        \$this->inputFilter = new EditBookStoreInputFilter();
        \$this->inputFilter->init();
    }

    /**
     * @throws ExceptionInterface
     */
    public function init(): void
    {
        // add more form elements

        \$this->add(
            (new Csrf('editBookStoreCsrf'))
                ->setOptions([
                    'csrf_options' => ['timeout' => 3600, 'session' => new Container()],
                ])
                ->setAttribute('required', true)
        );
        \$this->add(
            (new Submit('submit'))
                ->setAttribute('type', 'submit')
                ->setAttribute('value', 'Save')
                ->setAttribute('class', 'btn btn-primary btn-color btn-sm')
        );
    }
}

BODY;
    }
}
