<?php

declare(strict_types=1);

namespace Dot\Maker\Type;

use Dot\Maker\Component;
use Dot\Maker\Component\Import;
use Dot\Maker\Component\Inject;
use Dot\Maker\Component\Method;
use Dot\Maker\Component\Method\Constructor;
use Dot\Maker\Component\Parameter;
use Dot\Maker\FileSystem\File;
use Dot\Maker\IO\Input;
use Dot\Maker\IO\Output;

use function implode;
use function sprintf;
use function ucfirst;

use const PHP_EOL;

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

        $serviceInterface = $this->fileSystem->serviceInterface($name);
        if ($serviceInterface->exists()) {
            $middleware
                ->getComponent()
                    ->useClass(Import::DOT_DEPENDENCYINJECTION_ATTRIBUTE_INJECT)
                    ->useClass($serviceInterface->getComponent()->getFqcn());
        }

        $content = $this->render(
            $middleware->getComponent(),
            $serviceInterface->exists() ? $serviceInterface->getComponent() : null
        );
        if (! $middleware->create($content)) {
            Output::error(sprintf('Could not create Middleware "%s"', $middleware->getPath()), true);
        }
        Output::info(sprintf('Created new Middleware "%s"', $middleware->getPath()));

        return $middleware;
    }

    public function render(Component $middleware, ?Component $serviceInterface = null): string
    {
        $methods = [];

        if ($serviceInterface !== null) {
            $methods[] = (new Constructor())
                ->addInject(
                    (new Inject())->addArgument($serviceInterface->getClassString())
                )
                ->addPromotedPropertyFromComponent($serviceInterface);
        }

        $methods[] = (new Method('process'))
            ->addParameter(
                new Parameter('request', 'ServerRequestInterface')
            )
            ->addParameter(
                new Parameter('handler', 'RequestHandlerInterface')
            )
            ->addBodyLine('// add logic here')
            ->addBodyLine('', 0)
            ->addBodyLine('return $handler->handle($request);')
            ->setReturnType('ResponseInterface');

        return $this->stub->render('middleware.stub', [
            'MIDDLEWARE_CLASS_NAME' => $middleware->getClassName(),
            'MIDDLEWARE_NAMESPACE'  => $middleware->getNamespace(),
            'METHODS'               => implode(PHP_EOL . PHP_EOL . '    ', $methods),
            'USES'                  => $middleware->getImport()->render(),
        ]);
    }
}
