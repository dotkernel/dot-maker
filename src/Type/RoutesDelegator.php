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

use const PHP_EOL;

class RoutesDelegator extends AbstractType implements FileInterface
{
    public function __invoke(): void
    {
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

        $content = $this->render(
            $routesDelegator->getComponent(),
            $this->fileSystem->module($name)->getComponent(),
            $this->fileSystem->entity($name)->getComponent(),
            $this->fileSystem->apiDeleteResourceHandler($name),
            $this->fileSystem->apiGetResourceHandler($name),
            $this->fileSystem->apiGetCollectionHandler($name),
            $this->fileSystem->apiPatchResourceHandler($name),
            $this->fileSystem->apiPostResourceHandler($name),
            $this->fileSystem->apiPutResourceHandler($name),
            $this->fileSystem->getCreateResourceHandler($name),
            $this->fileSystem->postCreateResourceHandler($name),
            $this->fileSystem->getDeleteResourceHandler($name),
            $this->fileSystem->postDeleteResourceHandler($name),
            $this->fileSystem->getEditResourceHandler($name),
            $this->fileSystem->postEditResourceHandler($name),
            $this->fileSystem->getListResourcesHandler($name),
            $this->fileSystem->getViewResourceHandler($name),
        );

        try {
            $routesDelegator->create($content);
            Output::info(sprintf('Created RoutesDelegator "%s"', $routesDelegator->getPath()));
        } catch (RuntimeException $exception) {
            Output::error($exception->getMessage());
        }

        return $routesDelegator;
    }

    public function render(
        Component $routesDelegator,
        Component $module,
        Component $entity,
        File $apiDeleteResourceHandler,
        File $apiGetResourceHandler,
        File $apiGetCollectionHandler,
        File $apiPatchResourceHandler,
        File $apiPostResourceHandler,
        File $apiPutResourceHandler,
        File $getCreateResourceHandler,
        File $postCreateResourceHandler,
        File $getDeleteResourceHandler,
        File $postDeleteResourceHandler,
        File $getEditResourceHandler,
        File $postEditResourceHandler,
        File $getListResourcesHandler,
        File $getViewResourceHandler,
    ): string {
        $class = (new ClassFile($routesDelegator->getNamespace(), $routesDelegator->getClassName()))
            ->useClass($this->import->getConfigProviderFqcn(true))
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
            ->appendBody('$uuid = ConfigProvider::REGEXP_UUID;')
            ->appendBody('', 0)
            ->appendBody('/** @var RouteCollectorInterface $routeCollector */')
            ->appendBody('$routeCollector = $container->get(RouteCollectorInterface::class);')
            ->appendBody('', 0)
            ->appendBody('$routeCollector');

        if ($apiDeleteResourceHandler->exists()) {
            $class->useClass($apiDeleteResourceHandler->getComponent()->getFqcn());
            $invoke->appendBody(
                sprintf(
                    '->delete(\'/%s/\' . $uuid, %s, \'%s::delete-%s\')',
                    $entity->toKebabCase(),
                    $apiDeleteResourceHandler->getComponent()->getClassString(),
                    $module->toKebabCase(),
                    $entity->toKebabCase(),
                ),
                12
            );
        }

        if ($apiGetResourceHandler->exists()) {
            $class->useClass($apiGetResourceHandler->getComponent()->getFqcn());
            $invoke->appendBody(
                sprintf(
                    '->get(\'/%s/\' . $uuid, %s, \'%s::view-%s\')',
                    $entity->toKebabCase(),
                    $apiGetResourceHandler->getComponent()->getClassString(),
                    $module->toKebabCase(),
                    $entity->toKebabCase(),
                ),
                12
            );
        }

        if ($apiGetCollectionHandler->exists()) {
            $class->useClass($apiGetCollectionHandler->getComponent()->getFqcn());
            $invoke->appendBody(
                sprintf(
                    '->get(\'/%s\', %s, \'%s::list-%s\')',
                    $entity->toKebabCase(),
                    $apiGetCollectionHandler->getComponent()->getClassString(),
                    $module->toKebabCase(),
                    $entity->toKebabCase(),
                ),
                12
            );
        }

        if ($apiPatchResourceHandler->exists()) {
            $class->useClass($apiPatchResourceHandler->getComponent()->getFqcn());
            $invoke->appendBody(
                sprintf(
                    '->patch(\'/%s/\' . $uuid, %s, \'%s::update-%s\')',
                    $entity->toKebabCase(),
                    $apiPatchResourceHandler->getComponent()->getClassString(),
                    $module->toKebabCase(),
                    $entity->toKebabCase(),
                ),
                12
            );
        }

        if ($apiPostResourceHandler->exists()) {
            $class->useClass($apiPostResourceHandler->getComponent()->getFqcn());
            $invoke->appendBody(
                sprintf(
                    '->post(\'/%s\', %s, \'%s::create-%s\')',
                    $entity->toKebabCase(),
                    $apiPostResourceHandler->getComponent()->getClassString(),
                    $module->toKebabCase(),
                    $entity->toKebabCase(),
                ),
                12
            );
        }

        if ($apiPutResourceHandler->exists()) {
            $class->useClass($apiPutResourceHandler->getComponent()->getFqcn());
            $invoke->appendBody(
                sprintf(
                    '->put(\'/%s/\' . $uuid, %s, \'%s::replace-%s\')',
                    $entity->toKebabCase(),
                    $apiPutResourceHandler->getComponent()->getClassString(),
                    $module->toKebabCase(),
                    $entity->toKebabCase(),
                ),
                12
            );
        }

        if ($getCreateResourceHandler->exists()) {
            $class->useClass($getCreateResourceHandler->getComponent()->getFqcn());
            $invoke->appendBody(
                sprintf(
                    '->get(\'/create-%s\', %s, \'%s::create-%s-form\')',
                    $entity->toKebabCase(),
                    $getCreateResourceHandler->getComponent()->getClassString(),
                    $module->toKebabCase(),
                    $entity->toKebabCase(),
                ),
                12
            );
        }

        if ($postCreateResourceHandler->exists()) {
            $class->useClass($postCreateResourceHandler->getComponent()->getFqcn());
            $invoke->appendBody(
                sprintf(
                    '->post(\'/create-%s\', %s, \'%s::create-%s\')',
                    $entity->toKebabCase(),
                    $postCreateResourceHandler->getComponent()->getClassString(),
                    $module->toKebabCase(),
                    $entity->toKebabCase(),
                ),
                12
            );
        }

        if ($getDeleteResourceHandler->exists()) {
            $class->useClass($getDeleteResourceHandler->getComponent()->getFqcn());
            $invoke->appendBody(
                sprintf(
                    '->get(\'/delete-%s/\' . $uuid, %s, \'%s::delete-%s-form\')',
                    $entity->toKebabCase(),
                    $getDeleteResourceHandler->getComponent()->getClassString(),
                    $module->toKebabCase(),
                    $entity->toKebabCase(),
                ),
                12
            );
        }

        if ($postDeleteResourceHandler->exists()) {
            $class->useClass($postDeleteResourceHandler->getComponent()->getFqcn());
            $invoke->appendBody(
                sprintf(
                    '->post(\'/delete-%s/\' . $uuid, %s, \'%s::delete-%s\')',
                    $entity->toKebabCase(),
                    $postDeleteResourceHandler->getComponent()->getClassString(),
                    $module->toKebabCase(),
                    $entity->toKebabCase(),
                ),
                12
            );
        }

        if ($getEditResourceHandler->exists()) {
            $class->useClass($getEditResourceHandler->getComponent()->getFqcn());
            $invoke->appendBody(
                sprintf(
                    '->get(\'/edit-%s/\' . $uuid, %s, \'%s::edit-%s-form\')',
                    $entity->toKebabCase(),
                    $getEditResourceHandler->getComponent()->getClassString(),
                    $module->toKebabCase(),
                    $entity->toKebabCase(),
                ),
                12
            );
        }

        if ($postEditResourceHandler->exists()) {
            $class->useClass($postEditResourceHandler->getComponent()->getFqcn());
            $invoke->appendBody(
                sprintf(
                    '->post(\'/edit-%s/\' . $uuid, %s, \'%s::edit-%s\')',
                    $entity->toKebabCase(),
                    $postEditResourceHandler->getComponent()->getClassString(),
                    $module->toKebabCase(),
                    $entity->toKebabCase(),
                ),
                12
            );
        }

        if ($getListResourcesHandler->exists()) {
            $class->useClass($getListResourcesHandler->getComponent()->getFqcn());
            $invoke->appendBody(
                sprintf(
                    '->get(\'/list-%s\', %s, \'%s::list-%s\')',
                    $entity->toKebabCase(),
                    $getListResourcesHandler->getComponent()->getClassString(),
                    $module->toKebabCase(),
                    $entity->toKebabCase(),
                ),
                12
            );
        }

        if ($getViewResourceHandler->exists()) {
            $class->useClass($getViewResourceHandler->getComponent()->getFqcn());
            $invoke->appendBody(
                sprintf(
                    '->get(\'/view-%s/\' . $uuid, %s, \'%s::view-%s-form\')',
                    $entity->toKebabCase(),
                    $getViewResourceHandler->getComponent()->getClassString(),
                    $module->toKebabCase(),
                    $entity->toKebabCase(),
                ),
                12
            );
        }

        $invoke
            ->appendBody(';' . PHP_EOL, 0, false)
            ->appendBody('return $callback();');

        $class->addMethod($invoke);

        return $class->render();
    }
}
