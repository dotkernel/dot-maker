<?php

declare(strict_types=1);

namespace Dot\Maker\Type\Handler\Api;

use Dot\Maker\Component;
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
use Dot\Maker\Type\AbstractType;
use Dot\Maker\Type\FileInterface;
use Dot\Maker\Type\TypeEnum;

use Throwable;
use function implode;
use function sprintf;
use function ucfirst;

use const PHP_EOL;

class GetCollectionHandler extends AbstractType implements FileInterface
{
    public function __invoke(): void
    {
        while (true) {
            $name = ucfirst(Input::prompt('Enter new Handler name: '));
            if ($name === '') {
                break;
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
            Output::error(sprintf('Invalid Handler name: "%s"', $name), true);
        }

        $handler = $this->fileSystem->apiGetCollectionResourceHandler($name);
        if ($handler->exists()) {
            throw DuplicateFileException::create($handler);
        }

        $serviceInterface = $this->fileSystem->serviceInterface($name);
        $collection       = $this->fileSystem->collection($name);

        $content = $this->render(
            $handler->getComponent(),
            $serviceInterface->exists() ? $serviceInterface->getComponent() : null,
            $collection->exists() ? $collection->getComponent() : null,
        );

        try {
            $handler->create($content);
            Output::info(sprintf('Created Handler "%s"', $handler->getPath()));
        } catch (RuntimeException $exception) {
            Output::error($exception->getMessage());
        }

        return $handler;
    }

    public function render(
        Component $handler,
        ?Component $serviceInterface = null,
        ?Component $collection = null
    ): string {
        $class = (new ClassFile($collection->getNamespace(), $collection->getClassName()))
            ->useClass($this->getAbstractHandlerFqcn())
            ->useClass(Import::PSR_HTTP_MESSAGE_RESPONSEINTERFACE)
            ->useClass(Import::PSR_HTTP_MESSAGE_SERVERREQUESTINTERFACE);

        if ($serviceInterface !== null) {
            $class
                ->useClass(Import::DOT_DEPENDENCYINJECTION_ATTRIBUTE_INJECT)
                ->useClass($serviceInterface->getFqcn());
        }

        if ($serviceInterface !== null) {
            $handler->useClass($collection->getFqcn());
        }

        $methods = [];

        if ($serviceInterface !== null) {
            $methods[] = (new Constructor())
                ->addPromotedPropertyFromComponent($serviceInterface)
                ->addInject(
                    (new Inject())->addArgument($serviceInterface->getClassString())
                );
        }

        if ($collection !== null) {
            $getter = sprintf('get%s', ucfirst(Component::pluralize($this->fileSystem->getModuleName())));

            // phpcs:disable Generic.Files.LineLength.TooLong
            $body = <<<BODY
        return \$this->createResponse(
            \$request,
            new {$collection->getClassName()}(\$this->{$serviceInterface->getPropertyName(true)}->$getter(\$request->getQueryParams()))
        );
BODY;
            // phpcs:enable Generic.Files.LineLength.TooLong
        } else {
            $body = <<<BODY
        \$this->emptyResponse();
BODY;
        }

        $methods[] = (new Method('handle'))
            ->addParameter(
                new Parameter('request', 'ServerRequestInterface')
            )
            ->setReturnType('ResponseInterface')
            ->setBody($body);

        return $this->stub->render('api-handler.stub', [
            'HANDLER_CLASS_NAME' => $handler->getClassName(),
            'HANDLER_NAMESPACE'  => $handler->getNamespace(),
            'METHODS'            => implode(PHP_EOL . PHP_EOL . '    ', $methods),
            'USES'               => $handler->getImport()->render(),
        ]);
    }

    public function getAbstractHandlerFqcn(): string
    {
        return sprintf(Import::ROOT_APP_HANDLER_ABSTRACTHANDLER, $this->context->getRootNamespace());
    }
}
