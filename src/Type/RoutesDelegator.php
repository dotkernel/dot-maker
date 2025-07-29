<?php

declare(strict_types=1);

namespace Dot\Maker\Type;

use Dot\Maker\Component;
use Dot\Maker\Component\ClassFile;
use Dot\Maker\Component\Import;
use Dot\Maker\Component\Method;
use Dot\Maker\Component\Parameter;
use Dot\Maker\Exception\DuplicateFileException;
use Dot\Maker\Exception\RuntimeException;
use Dot\Maker\FileSystem\File;
use Dot\Maker\IO\Output;
use Throwable;

use function sprintf;

class RoutesDelegator extends AbstractType implements FileInterface
{
    public function __invoke(): void
    {
        if (! $this->context->isApi()) {
            return;
        }

        try {
            $this->create('RoutesDelegator');
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
        $routesDelegator = $this->fileSystem->routesDelegator();
        if ($routesDelegator->exists()) {
            throw DuplicateFileException::create($routesDelegator);
        }

        if ($this->context->isApi()) {
            $content = $this->render(
                $routesDelegator->getComponent(),
                [
                    'collection' => $this->fileSystem->apiDeleteResourceHandler($name),
                    'delete'     => $this->fileSystem->apiDeleteResourceHandler($name),
                    'get'        => $this->fileSystem->apiGetResourceHandler($name),
                    'patch'      => $this->fileSystem->apiPatchResourceHandler($name),
                    'post'       => $this->fileSystem->apiPostResourceHandler($name),
                    'put'        => $this->fileSystem->apiPutResourceHandler($name),
                ]
            );
        } else {
            $content = $this->render(
                $routesDelegator->getComponent(),
                [
                    'get-create'  => $this->fileSystem->getResourceCreateHandler($name),
                    'post-create' => $this->fileSystem->postResourceCreateHandler($name),
                    'get-delete'  => $this->fileSystem->getResourceDeleteHandler($name),
                    'post-delete' => $this->fileSystem->postResourceDeleteHandler($name),
                    'get-edit'    => $this->fileSystem->getResourceEditHandler($name),
                    'post-edit'   => $this->fileSystem->postResourceEditHandler($name),
                    'get-list'    => $this->fileSystem->getResourceListHandler($name),
                    'get-view'    => $this->fileSystem->getResourceViewHandler($name),
                ]
            );
        }

        try {
            $routesDelegator->create($content);
            Output::info(sprintf('Created RoutesDelegator "%s"', $routesDelegator->getPath()));
        } catch (RuntimeException $exception) {
            Output::error($exception->getMessage());
        }

        return $routesDelegator;
    }

    public function render(Component $routesDelegator, array $handlers): string
    {
        $class = (new ClassFile($routesDelegator->getNamespace(), $routesDelegator->getClassName()))
            ->useClass(Import::DOT_ROUTER_ROUTECOLLECTORINTERFACE)
            ->useClass(Import::PSR_CONTAINER_CONTAINEREXCEPTIONINTERFACE)
            ->useClass(Import::PSR_CONTAINER_CONTAINERINTERFACE)
            ->useClass(Import::PSR_CONTAINER_NOTFOUNDEXCEPTIONINTERFACE)
            ->useClass(Import::MEZZIO_APPLICATION);

        $invoke = (new Method('__invoke'))
            ->setReturnType('Application')
            ->addParameter(
                new Parameter('container', 'ContainerInterface')
            )
            ->addParameter(
                new Parameter('serviceName', 'string')
            )
            ->addParameter(
                new Parameter('callback', 'callable')
            )
            ->setComment(<<<COMM
/**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
COMM)
            ->appendBody('/** @var RouteCollectorInterface $routeCollector */')
            ->appendBody('$routeCollector = $container->get(RouteCollectorInterface::class);')
            ->appendBody('')
            ->appendBody('$routeCollector;');

//        foreach ($handlers as $method => $handler) {
//            ;
//        }

        $class->addMethod($invoke);

        return $class->render();
    }
}
