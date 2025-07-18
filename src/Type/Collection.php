<?php

declare(strict_types=1);

namespace Dot\Maker\Type;

use Dot\Maker\Component;
use Dot\Maker\Component\Import;
use Dot\Maker\FileSystem\File;
use Dot\Maker\IO\Input;
use Dot\Maker\IO\Output;

use function sprintf;
use function ucfirst;

class Collection extends AbstractType implements FileInterface
{
    public function __invoke(): void
    {
        while (true) {
            $name = ucfirst(Input::prompt('Enter new Collection name: '));
            if ($name === '') {
                return;
            }

            if (! $this->isValid($name)) {
                Output::error(sprintf('Invalid Collection name: "%s"', $name));
                continue;
            }

            $collection = $this->fileSystem->collection($name);
            if ($collection->exists()) {
                Output::error(
                    sprintf(
                        'Collection "%s" already exists at %s',
                        $collection->getComponent()->getClassName(),
                        $collection->getPath()
                    )
                );
                continue;
            }

            $collection
                ->ensureParentDirectoryExists()
                ->getComponent()
                    ->useClass($this->getResourceCollectionFqcn());

            $content = $this->render($collection->getComponent());
            if (! $collection->create($content)) {
                Output::error(sprintf('Could not create Collection "%s"', $collection->getPath()), true);
            }
            Output::info(sprintf('Created new Collection "%s"', $collection->getPath()));
        }
    }

    public function create(string $name): ?File
    {
        if (! $this->context->isApi()) {
            return null;
        }

        if (! $this->isValid($name)) {
            Output::error(sprintf('Invalid Collection name: "%s"', $name), true);
        }

        $collection = $this->fileSystem->collection($name);
        if ($collection->exists()) {
            Output::error(
                sprintf(
                    'Collection "%s" already exists at %s',
                    $collection->getComponent()->getClassName(),
                    $collection->getPath()
                ),
                true
            );
        }

        $collection
            ->ensureParentDirectoryExists()
            ->getComponent()
            ->useClass($this->getResourceCollectionFqcn());

        $content = $this->render($collection->getComponent());
        if (! $collection->create($content)) {
            Output::error(sprintf('Could not create Collection "%s"', $collection->getPath()), true);
        }
        Output::info(sprintf('Created new Collection "%s"', $collection->getPath()));

        return $collection;
    }

    public function getResourceCollectionFqcn(): string
    {
        $format = Import::ROOT_APP_COLLECTION_RESOURCECOLLECTION;

        return sprintf($format, $this->context->getRootNamespace());
    }

    public function render(Component $collection): string
    {
        return $this->stub->render('collection.stub', [
            'COLLECTION_CLASS_NAME' => $collection->getClassName(),
            'COLLECTION_NAMESPACE'  => $collection->getNamespace(),
            'USES'                  => $collection->getImport()->render(),
        ]);
    }
}
