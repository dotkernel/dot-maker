<?php

declare(strict_types=1);

namespace Dot\Maker\Type;

use Dot\Maker\Component\ClassFile;
use Dot\Maker\Component\Import;
use Dot\Maker\Component\Inject;
use Dot\Maker\Component\Method;
use Dot\Maker\Component\Method\Constructor;
use Dot\Maker\Component\Parameter;
use Dot\Maker\Exception\BadRequestException;
use Dot\Maker\Exception\DuplicateFileException;
use Dot\Maker\Exception\RuntimeException;
use Dot\Maker\FileSystem\File;
use Dot\Maker\IO\Input;
use Dot\Maker\IO\Output;
use Throwable;

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
            throw new BadRequestException(sprintf('Invalid Middleware name: "%s"', $name));
        }

        $middleware = $this->fileSystem->middleware($name);
        if ($middleware->exists()) {
            throw DuplicateFileException::create($middleware);
        }

        $content = $this->render(
            $middleware,
            $this->fileSystem->serviceInterface($name),
        );

        try {
            $middleware->create($content);
            Output::info(sprintf('Created Middleware "%s"', $middleware->getPath()));
        } catch (RuntimeException $exception) {
            Output::error($exception->getMessage());
        }

        return $middleware;
    }

    public function render(File $middleware, File $serviceInterface): string
    {
        $class = (new ClassFile(
            $middleware->getComponent()->getNamespace(),
            $middleware->getComponent()->getClassName()
        ))
            ->addInterface('MiddlewareInterface')
            ->useClass(Import::PSR_HTTP_MESSAGE_RESPONSEINTERFACE)
            ->useClass(Import::PSR_HTTP_MESSAGE_SERVERREQUESTINTERFACE)
            ->useClass(Import::PSR_HTTP_SERVER_MIDDLEWAREINTERFACE)
            ->useClass(Import::PSR_HTTP_SERVER_REQUESTHANDLERINTERFACE)
            ->useClass(Import::DOT_DEPENDENCYINJECTION_ATTRIBUTE_INJECT)
            ->useClass($serviceInterface->getComponent()->getFqcn());

        $constructor = (new Constructor())
            ->addInject(
                (new Inject())->addArgument($serviceInterface->getComponent()->getClassString())
            )
            ->addPromotedPropertyFromComponent($serviceInterface->getComponent());
        $class->addMethod($constructor);

        $execute = (new Method('process'))
            ->setReturnType('ResponseInterface')
            ->addParameter(
                new Parameter('request', 'ServerRequestInterface')
            )
            ->addParameter(
                new Parameter('handler', 'RequestHandlerInterface')
            )
            ->setBody(<<<BODY
        // add logic here

        return \$handler->handle(\$request);
BODY);
        $class->addMethod($execute);

        return $class->render();
    }
}
