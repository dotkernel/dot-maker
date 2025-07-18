<?php

declare(strict_types=1);

namespace Dot\Maker\Type;

use Dot\Maker\Component;
use Dot\Maker\Component\Import;
use Dot\Maker\FileSystem\File;
use Dot\Maker\IO\Input;
use Dot\Maker\IO\Output;
use Dot\Maker\VisibilityEnum;

use function sprintf;
use function ucfirst;

class Middleware extends AbstractType implements FileInterface
{
    public function __invoke(): void
    {
        while (true) {
            $name = ucfirst(Input::prompt('Enter new Middleware name: '));
            if ($name === '') {
                return;
            }

            if (! $this->isValid($name)) {
                Output::error(sprintf('Invalid Middleware name: "%s"', $name));
                continue;
            }

            $middleware = $this->fileSystem->middleware($name);
            if ($middleware->exists()) {
                Output::error(
                    sprintf(
                        'Middleware "%s" already exists at %s',
                        $middleware->getComponent()->getClassName(),
                        $middleware->getPath()
                    )
                );
                continue;
            }

            $middleware
                ->ensureParentDirectoryExists()
                ->getComponent()
                    ->useClass(Import::PSR_HTTP_MESSAGE_RESPONSEINTERFACE)
                    ->useClass(Import::PSR_HTTP_MESSAGE_SERVERREQUESTINTERFACE)
                    ->useClass(Import::PSR_HTTP_SERVER_MIDDLEWAREINTERFACE)
                    ->useClass(Import::PSR_HTTP_SERVER_REQUESTHANDLERINTERFACE);

            $content = $this->render($middleware->getComponent());
            if (! $middleware->create($content)) {
                Output::error(sprintf('Could not create Middleware "%s"', $middleware->getPath()), true);
            }
            Output::info(sprintf('Created new Middleware "%s"', $middleware->getPath()));
        }
    }

    public function create(string $name): ?File
    {
        if (! $this->isValid($name)) {
            Output::error(sprintf('Invalid Middleware name: "%s"', $name), true);
        }

        $middleware = $this->fileSystem->middleware($name);
        if ($middleware->exists()) {
            Output::error(
                sprintf(
                    'Middleware "%s" already exists at %s',
                    $middleware->getComponent()->getClassName(),
                    $middleware->getPath()
                ),
                true
            );
        }

        $middleware
            ->ensureParentDirectoryExists()
            ->getComponent()
                ->useClass(Import::PSR_HTTP_MESSAGE_RESPONSEINTERFACE)
                ->useClass(Import::PSR_HTTP_MESSAGE_SERVERREQUESTINTERFACE)
                ->useClass(Import::PSR_HTTP_SERVER_MIDDLEWAREINTERFACE)
                ->useClass(Import::PSR_HTTP_SERVER_REQUESTHANDLERINTERFACE);

        if ($this->module->hasServiceInterface()) {
            $serviceInterface = $this->module->getServiceInterface();
            $middleware
                ->getComponent()
                    ->useClass(Import::DOT_DEPENDENCYINJECTION_ATTRIBUTE_INJECT)
                    ->useClass($serviceInterface->getComponent()->getFqcn());
            $middleware
                ->getComponent()
                    ->getConstructor()
                        ->addPromotedProperty($serviceInterface->getComponent(), VisibilityEnum::Private)
                        ->addInject($serviceInterface->getComponent()->getClassString());
        }

        $content = $this->render($middleware->getComponent());
        if (! $middleware->create($content)) {
            Output::error(sprintf('Could not create Middleware "%s"', $middleware->getPath()), true);
        }
        Output::info(sprintf('Created new Middleware "%s"', $middleware->getPath()));

        return $middleware;
    }

    public function render(Component $inputFilter): string
    {
        return $this->stub->render('middleware.stub', [
            'MIDDLEWARE_CLASS_NAME' => $inputFilter->getClassName(),
            'MIDDLEWARE_NAMESPACE'  => $inputFilter->getNamespace(),
            'CONSTRUCTOR'           => $inputFilter->getConstructor()->render(),
            'USES'                  => $inputFilter->getImport()->render(),
        ]);
    }
}
