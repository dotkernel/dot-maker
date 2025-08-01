<?php

declare(strict_types=1);

namespace Dot\Maker\Type;

use Dot\Maker\Component;
use Dot\Maker\Component\ClassFile;
use Dot\Maker\Exception\BadRequestException;
use Dot\Maker\Exception\DuplicateFileException;
use Dot\Maker\Exception\RuntimeException;
use Dot\Maker\FileSystem\File;
use Dot\Maker\IO\Input;
use Dot\Maker\IO\Output;
use Throwable;

use function sprintf;
use function ucfirst;

class Collection extends AbstractType implements FileInterface
{
    public function __invoke(): void
    {
        if (! $this->context->isApi()) {
            return;
        }

        while (true) {
            $name = ucfirst(Input::prompt('Enter new Collection name: '));
            if ($name === '') {
                return;
            }

            try {
                $this->create($name);
                break;
            } catch (Throwable $exception) {
                Output::error($exception->getMessage());
            }
        }
    }

    /**
     * @throws BadRequestException
     * @throws DuplicateFileException
     * @throws RuntimeException
     */
    public function create(string $name): File
    {
        if (! $this->isValid($name)) {
            throw new BadRequestException(sprintf('Invalid Collection name: "%s"', $name));
        }

        $collection = $this->fileSystem->collection($name);
        if ($collection->exists()) {
            throw DuplicateFileException::create($collection);
        }

        $content = $this->render($collection->getComponent());

        try {
            $collection->create($content);
            Output::info(sprintf('Created Collection "%s"', $collection->getPath()));
        } catch (RuntimeException $exception) {
            Output::error($exception->getMessage());
        }

        return $collection;
    }

    public function render(Component $collection): string
    {
        return (new ClassFile($collection->getNamespace(), $collection->getClassName()))
            ->useClass($this->import->getResourceCollectionFqcn())
            ->setExtends('ResourceCollection')
            ->render();
    }
}
