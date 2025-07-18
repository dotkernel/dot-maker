<?php

declare(strict_types=1);

namespace Dot\Maker\Type\Handler;

use Dot\Maker\Component;
use Dot\Maker\FileSystem\File;
use Dot\Maker\IO\Input;
use Dot\Maker\IO\Output;
use Dot\Maker\Type\AbstractType;
use Dot\Maker\Type\FileInterface;

use function sprintf;
use function ucfirst;

class PostCreateResourceHandler extends AbstractType implements FileInterface
{
    public function __invoke(): void
    {
        while (true) {
            $name = ucfirst(Input::prompt('Enter new Handler name: '));
            if ($name === '') {
                break;
            }

            if (! $this->isValid($name)) {
                Output::error(sprintf('Invalid Handler name: "%s"', $name));
                continue;
            }

            $handler = $this->fileSystem->handler($name);
            if ($handler->exists()) {
                Output::error(
                    sprintf(
                        'Handler "%s" already exists at %s',
                        $handler->getComponent()->getClassName(),
                        $handler->getPath()
                    )
                );
                continue;
            }

            $handler->ensureParentDirectoryExists();

            $content = $this->render($handler->getComponent());
            if (! $handler->create($content)) {
                Output::error(sprintf('Could not create ServiceInterface "%s"', $handler->getPath()), true);
            }
            Output::info(sprintf('Created ServiceInterface "%s"', $handler->getPath()));

            break;
        }
    }

    public function create(string $name): File
    {
        if (! $this->isValid($name)) {
            Output::error(sprintf('Invalid Handler name: "%s"', $name), true);
        }

        $handler = $this->fileSystem->handler($name);
        if ($handler->exists()) {
            Output::error(
                sprintf(
                    'Handler "%s" already exists at %s',
                    $handler->getComponent()->getClassName(),
                    $handler->getPath()
                ),
                true
            );
        }

        $handler->ensureParentDirectoryExists();

        $content = $this->render($handler->getComponent());
        if (! $handler->create($content)) {
            Output::error(sprintf('Could not create Handler "%s"', $handler->getPath()), true);
        }
        Output::info(sprintf('Created Handler "%s"', $handler->getPath()));

        return $handler;
    }

    public function render(Component $handler): string
    {
        return $this->stub->render('handler.stub', []);
    }
}
