<?php

declare(strict_types=1);

namespace Dot\Maker\Type;

use Dot\Maker\Component;
use Dot\Maker\Component\ClassFile;
use Dot\Maker\Component\Import;
use Dot\Maker\Exception\DuplicateFileException;
use Dot\Maker\Exception\RuntimeException;
use Dot\Maker\FileSystem\File;
use Dot\Maker\IO\Output;
use Throwable;

use function sprintf;

class OpenApi extends AbstractType implements FileInterface
{
    public function __invoke(): void
    {
        if (! $this->context->isApi()) {
            return;
        }

        try {
            $this->create('OpenAPI');
        } catch (Throwable $exception) {
            Output::error($exception->getMessage());
        }
    }

    /**
     * @throws DuplicateFileException
     * @throws RuntimeException
     */
    public function create(string $name): File
    {
        $openApi = $this->fileSystem->openApi();
        if ($openApi->exists()) {
            throw DuplicateFileException::create($openApi);
        }

        $content = $this->render(
            $openApi->getComponent(),
            $this->fileSystem->collection($name)->getComponent(),
            $this->fileSystem->entity($name)->getComponent(),
        );

        try {
            $openApi->create($content);
            Output::info(sprintf('Created OpenAPI "%s"', $openApi->getPath()));
        } catch (RuntimeException $exception) {
            Output::error($exception->getMessage());
        }

        return $openApi;
    }

    public function render(Component $openApi, Component $collection, Component $entity): string
    {
        $class = (new ClassFile($openApi->getNamespace(), $openApi->getClassName()))
            ->useClass(Import::DATETIMEIMMUTABLE)
            ->useClass(Import::FIG_HTTP_MESSAGE_STATUSCODEINTERFACE)
            ->useClass(Import::OPENAPI_ATTRIBUTES, 'OA')
            ->useClass($collection->getFqcn())
            ->useClass($entity->getFqcn());

        return $class->render();
    }
}
