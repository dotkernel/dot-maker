<?php

declare(strict_types=1);

namespace Dot\Maker\Type\Handler\Api;

use Dot\Maker\Component;
use Dot\Maker\Component\ClassFile;
use Dot\Maker\Component\Import;
use Dot\Maker\Component\Inject;
use Dot\Maker\Component\Method;
use Dot\Maker\Component\Parameter;
use Dot\Maker\Exception\BadRequestException;
use Dot\Maker\Exception\DuplicateFileException;
use Dot\Maker\Exception\RuntimeException;
use Dot\Maker\FileSystem\File;
use Dot\Maker\IO\Output;
use Dot\Maker\Type\AbstractType;
use Dot\Maker\Type\FileInterface;

use function sprintf;

class GetResourceHandler extends AbstractType implements FileInterface
{
    /**
     * @throws BadRequestException
     * @throws DuplicateFileException
     * @throws RuntimeException
     */
    public function create(string $name): File
    {
        $handler = $this->fileSystem->apiGetResourceHandler($name);
        if ($handler->exists()) {
            throw DuplicateFileException::create($handler);
        }

        $content = $this->render(
            $handler->getComponent(),
            $this->fileSystem->entity($name)->getComponent(),
        );

        $handler->create($content);

        Output::success(sprintf('Created Handler: %s', $handler->getPath()));

        return $handler;
    }

    public function render(Component $handler, Component $entity): string
    {
        $class = (new ClassFile($handler->getNamespace(), $handler->getClassName()))
            ->setExtends('AbstractHandler')
            ->useClass($this->import->getAbstractHandlerFqcn())
            ->useClass($this->import->getResourceAttributeFqcn())
            ->useClass(Import::PSR_HTTP_MESSAGE_SERVERREQUESTINTERFACE)
            ->useClass(Import::PSR_HTTP_MESSAGE_RESPONSEINTERFACE)
            ->useClass($entity->getFqcn());

        $handle = (new Method('handle'))
            ->setReturnType('ResponseInterface')
            ->addParameter(
                new Parameter('request', 'ServerRequestInterface')
            )
            ->addInject(
                (new Inject('Resource'))->addArgument($entity->getClassString(), 'entity')
            )
            ->setBody(<<<BODY
        return \$this->createResponse(
            \$request,
            \$request->getAttribute({$entity->getClassString()})
        );
BODY);
        $class->addMethod($handle);

        return $class->render();
    }
}
