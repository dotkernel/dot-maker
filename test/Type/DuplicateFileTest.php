<?php

declare(strict_types=1);

namespace DotTest\Maker\Type;

use Dot\Maker\Config;
use Dot\Maker\Context;
use Dot\Maker\Exception\BadRequestException;
use Dot\Maker\Exception\DuplicateFileException;
use Dot\Maker\FileSystem;
use Dot\Maker\IO\Output;
use Dot\Maker\Type\FileInterface;
use Dot\Maker\Type\Module;
use Dot\Maker\Type\TypeEnum;
use DotTest\Maker\StreamTrait;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function sprintf;

/**
 * Every component that creates exactly one file refuses to overwrite it.
 * The composite types (Form, Handler, InputFilter, Module) are covered by their own tests.
 */
class DuplicateFileTest extends TestCase
{
    use StreamTrait;

    private Config $config;
    private Context $context;
    private FileSystem $fileSystem;
    private Module $module;

    protected function setUp(): void
    {
        $root = vfsStream::setup('root', 0755, [
            'composer.json' => '{"autoload":{"psr-4":{'
                . '"Api\\\\App\\\\":"src/App/src/",'
                . '"Core\\\\App\\\\":"src/Core/src/App/src/"'
                . '}}}',
        ]);

        $this->config     = new Config($root->url());
        $this->context    = new Context($root->url());
        $this->fileSystem = (new FileSystem($this->context))->setModuleName('BookStore');
        $this->module     = new Module($this->fileSystem, $this->context, $this->config);

        Output::setOutputStream($this->openMemoryStream());
        Output::setErrorStream($this->openMemoryStream());
    }

    #[DataProvider('singleFileTypeProvider')]
    public function testSecondCreateThrowsDuplicateFileException(TypeEnum $typeEnum): void
    {
        $type = $this->type($typeEnum);
        $file = $type->create('BookStore');
        $this->assertTrue($file->exists());

        $this->expectException(DuplicateFileException::class);
        $this->expectExceptionMessage(sprintf('already exists at %s', $file->getPath()));

        $type->create('BookStore');
    }

    public function testConfirmDeleteInputRejectsInvalidName(): void
    {
        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('Input name: "1Book"');

        $this->type(TypeEnum::InputConfirmDelete)->create('1Book');
    }

    /**
     * @return array<string, array{TypeEnum}>
     */
    public static function singleFileTypeProvider(): array
    {
        $cases = [
            TypeEnum::ConfigProvider,
            TypeEnum::CoreConfigProvider,
            TypeEnum::RoutesDelegator,
            TypeEnum::FormCreateResource,
            TypeEnum::FormDeleteResource,
            TypeEnum::FormEditResource,
            TypeEnum::HandlerApiDeleteResource,
            TypeEnum::HandlerApiGetResource,
            TypeEnum::HandlerApiGetCollection,
            TypeEnum::HandlerApiPatchResource,
            TypeEnum::HandlerApiPostResource,
            TypeEnum::HandlerApiPutResource,
            TypeEnum::HandlerGetCreateResource,
            TypeEnum::HandlerGetDeleteResource,
            TypeEnum::HandlerGetEditResource,
            TypeEnum::HandlerGetListResource,
            TypeEnum::HandlerGetViewResource,
            TypeEnum::HandlerPostCreateResource,
            TypeEnum::HandlerPostDeleteResource,
            TypeEnum::HandlerPostEditResource,
            TypeEnum::InputConfirmDelete,
            TypeEnum::InputFilterCreateResource,
            TypeEnum::InputFilterDeleteResource,
            TypeEnum::InputFilterEditResource,
            TypeEnum::InputFilterReplaceResource,
        ];

        $data = [];
        foreach ($cases as $case) {
            $data[$case->name] = [$case];
        }

        return $data;
    }

    private function type(TypeEnum $typeEnum): FileInterface
    {
        $type = new ($typeEnum->value)($this->fileSystem, $this->context, $this->config, $this->module);
        $this->assertInstanceOf(FileInterface::class, $type);

        return $type;
    }
}
