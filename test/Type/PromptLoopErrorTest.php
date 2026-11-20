<?php

declare(strict_types=1);

namespace DotTest\Maker\Type;

use Dot\Maker\Config;
use Dot\Maker\Context;
use Dot\Maker\FileSystem;
use Dot\Maker\IO\Input;
use Dot\Maker\IO\Output;
use Dot\Maker\Type\Form;
use Dot\Maker\Type\Handler;
use Dot\Maker\Type\InputFilter;
use Dot\Maker\Type\Module;
use DotTest\Maker\StreamTrait;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

use function fwrite;
use function preg_replace;
use function rewind;
use function sprintf;
use function stream_get_contents;

use const PHP_EOL;

/**
 * An error while creating a component is reported and the prompt loop goes on, instead of aborting the run.
 * Each test pre-creates one of the files the component would generate, so the creation fails with a duplicate.
 */
class PromptLoopErrorTest extends TestCase
{
    use StreamTrait;

    private Config $config;
    private Context $context;
    private FileSystem $fileSystem;
    private Module $module;
    /** @var resource $errorStream */
    private $errorStream;

    public function testFormReportsErrorAndKeepsPrompting(): void
    {
        $this->project('Admin');
        $existing = $this->fileSystem->createResourceForm('Book');
        $existing->create('existing');

        $this->answer('Book');
        (new Form($this->fileSystem, $this->context, $this->config, $this->module))();

        $this->assertStringContainsString(
            sprintf('Class "CreateBookForm" already exists at %s', $existing->getPath()),
            $this->errors()
        );
    }

    public function testFormCreateInApiReturnsTheFileWithoutCreatingIt(): void
    {
        $this->project('Api');

        $form = (new Form($this->fileSystem, $this->context, $this->config, $this->module))->create('Book');

        $this->assertSame('BookForm', $form->getComponent()->getClassName());
        $this->assertFalse($form->exists());
    }

    public function testHandlerReportsErrorAndKeepsPrompting(): void
    {
        $this->project('Admin');
        $existing = $this->fileSystem->getListResourcesHandler('Book');
        $existing->create('existing');

        $this->answer('Book');
        (new Handler($this->fileSystem, $this->context, $this->config, $this->module))();

        $this->assertStringContainsString(
            sprintf('Class "GetListBookHandler" already exists at %s', $existing->getPath()),
            $this->errors()
        );
    }

    public function testInputFilterReportsErrorAndKeepsPrompting(): void
    {
        $this->project('Admin');
        $existing = $this->fileSystem->createResourceInputFilter('Book');
        $existing->create('existing');

        $this->answer('Book');
        (new InputFilter($this->fileSystem, $this->context, $this->config, $this->module))();

        $this->assertStringContainsString(
            sprintf('Class "CreateBookInputFilter" already exists at %s', $existing->getPath()),
            $this->errors()
        );
    }

    public function testModuleReportsErrorFromAComponent(): void
    {
        $this->project('Api');
        $this->fileSystem->setModuleName('Shelf');
        $existing = $this->fileSystem->coreConfigProvider();
        $existing->create('existing');

        $this->answer('Shelf');
        $module = new Module($this->fileSystem, $this->context, $this->config);
        $module->setModule($module);
        $module();

        $this->assertStringContainsString(
            sprintf('Class "ConfigProvider" already exists at %s', $existing->getPath()),
            $this->errors()
        );
    }

    /**
     * A project with Core architecture and the module "Book".
     */
    private function project(string $projectType): void
    {
        $root = vfsStream::setup('root', 0755, [
            'composer.json' => sprintf(
                '{"autoload":{"psr-4":{"%s\\\\App\\\\":"src/App/src/","Core\\\\App\\\\":"src/Core/src/App/src/"}}}',
                $projectType
            ),
            'src'           => [
                'Book' => [
                    'src' => [],
                ],
                'Core' => [
                    'src' => [],
                ],
            ],
        ]);

        $this->config     = new Config($root->url());
        $this->context    = new Context($root->url());
        $this->fileSystem = (new FileSystem($this->context))->setModuleName('Book');
        $this->module     = new Module($this->fileSystem, $this->context, $this->config);

        $this->errorStream = $this->openMemoryStream();
        Output::setErrorStream($this->errorStream);
        Output::setOutputStream($this->openMemoryStream());
    }

    /**
     * Answers followed by the end of input: every confirmation then takes its default ("yes"),
     * and the name prompt receives a blank answer, which ends the loop.
     */
    private function answer(string ...$lines): void
    {
        $stream = $this->openMemoryStream();
        foreach ($lines as $line) {
            fwrite($stream, $line . PHP_EOL);
        }
        rewind($stream);
        Input::setStream($stream);
    }

    private function errors(): string
    {
        rewind($this->errorStream);

        return (string) preg_replace('/\e\[\d+(;\d+)?m/', '', (string) stream_get_contents($this->errorStream));
    }
}
