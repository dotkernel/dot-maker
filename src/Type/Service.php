<?php

declare(strict_types=1);

namespace Dot\Maker\Type;

use Dot\Maker\Component;
use Dot\Maker\Component\ClassFile;
use Dot\Maker\Component\Import;
use Dot\Maker\Component\Inject;
use Dot\Maker\Component\Method;
use Dot\Maker\Component\Method\Constructor;
use Dot\Maker\Component\Parameter;
use Dot\Maker\Component\PromotedProperty;
use Dot\Maker\Exception\BadRequestException;
use Dot\Maker\Exception\DuplicateFileException;
use Dot\Maker\Exception\RuntimeException;
use Dot\Maker\FileSystem\File;
use Dot\Maker\IO\Input;
use Dot\Maker\IO\Output;
use Throwable;

use function sprintf;
use function ucfirst;

class Service extends AbstractType implements FileInterface
{
    public function __invoke(): void
    {
        while (true) {
            $name = ucfirst(Input::prompt('Enter new Service name: '));
            if ($name === '') {
                break;
            }

            try {
                $this->create($name);
                $this->component(TypeEnum::ServiceInterface)->create($name);
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
            throw new BadRequestException(sprintf('Invalid Service name: "%s"', $name));
        }

        $service = $this->fileSystem->service($name);
        if ($service->exists()) {
            throw DuplicateFileException::create($service);
        }

        $content = $this->render(
            $service->getComponent(),
            $this->fileSystem->serviceInterface($name)->getComponent(),
            $this->fileSystem->repository($name)->getComponent(),
            $this->fileSystem->entity($name)->getComponent(),
        );

        $service->create($content);

        Output::success(sprintf('Created Service: %s', $service->getPath()));

        return $service;
    }

    public function render(
        Component $service,
        Component $serviceInterface,
        Component $repository,
        Component $entity,
    ): string {
        $class = (new ClassFile($service->getNamespace(), $service->getClassName()))
            ->addInterface($serviceInterface->getClassName())
            ->useClass($this->import->getAppHelperPaginatorFqcn())
            ->useClass(Import::DOT_DEPENDENCYINJECTION_ATTRIBUTE_INJECT)
            ->useClass($repository->getFqcn())
            ->useClass($entity->getFqcn())
            ->useFunction('in_array');

        $promotedProperty = new PromotedProperty(
            $repository->toCamelCase(true),
            $repository->getClassName()
        );

        $constructor = (new Constructor())
            ->addInject(
                (new Inject())->addArgument($repository->getClassString())
            )
            ->addPromotedProperty($promotedProperty);
        $class->addMethod($constructor);
        $class->addMethod($promotedProperty->getGetter());

        $deleteResource = (new Method($entity->getDeleteMethodName()))
            ->addParameter(
                new Parameter($entity->toCamelCase(), $entity->getClassName())
            )
            ->setBody(<<<BODY
        \$this->{$repository->toCamelCase()}->deleteResource({$entity->getVariable()});
BODY);
        $class->addMethod($deleteResource);

        $getResources = (new Method($entity->getCollectionMethodName()))
            ->addParameter(
                new Parameter('params', 'array')
            )
            ->setReturnType($this->context->isApi() ? 'QueryBuilder' : 'array')
            ->setComment(<<<COMM
/**
     * @param array<non-empty-string, mixed> \$params
     */
COMM)
            ->setBody(<<<BODY
        \$filters = \$params['filters'] ?? [];
        \$params  = Paginator::getParams(\$params, '{$entity->toCamelCase()}.created');

        \$sortableColumns = [
            '{$entity->toCamelCase()}.created',
            '{$entity->toCamelCase()}.updated',
        ];
        if (! in_array(\$params['sort'], \$sortableColumns, true)) {
            \$params['sort'] = '{$entity->toCamelCase()}.created';
        }

BODY);
        if ($this->context->isApi()) {
            $class->useClass(Import::DOCTRINE_ORM_QUERYBUILDER);
            $getResources->appendBody(
                sprintf(
                    'return $this->%s->%s($params, $filters);',
                    $repository->toCamelCase(),
                    $entity->getCollectionMethodName()
                )
            );
        } else {
            $class->useClass(Import::DOCTRINE_ORM_TOOLS_PAGINATION_PAGINATOR, 'DoctrinePaginator');
            $getResources
                ->appendBody(
                    sprintf(
                        '$paginator = new DoctrinePaginator($this->%s->%s($params, $filters)->getQuery());',
                        $repository->toCamelCase(),
                        $entity->getCollectionMethodName()
                    )
                )
                ->appendBody('', 0)
                ->appendBody('return Paginator::wrapper($paginator, $params, $filters);');
        }
        $class->addMethod($getResources);

        $saveResource = (new Method($entity->getSaveMethodName()))
            ->setReturnType($entity->getClassName())
            ->addParameter(
                new Parameter('data', 'array')
            )
            ->addParameter(
                new Parameter($entity->toCamelCase(), $entity->getClassName(), true, 'null')
            )
            ->setComment(<<<COMM
/**
     * @param array<non-empty-string, mixed> \$data
     */
COMM)
            ->setBody(<<<BODY
        if (! {$entity->getVariable()} instanceof {$entity->getClassName()}) {
            {$entity->getVariable()} = new {$entity->getClassName()}();
        }

        \$this->{$repository->toCamelCase()}->saveResource({$entity->getVariable()});

        return {$entity->getVariable()};
BODY);
        $class->addMethod($saveResource);

        if (! $this->context->isApi()) {
            $class
                ->useClass($this->import->getAppMessageFqcn())
                ->useClass($this->import->getNotFoundExceptionFqcn());

            $findResource = (new Method($entity->getFindMethodName()))
                ->setReturnType($entity->getClassName())
                ->addParameter(
                    new Parameter('uuid', 'string')
                )
                ->setComment(<<<COMM
/**
     * @throws NotFoundException
     */
COMM)
                ->setBody(<<<BODY
        {$entity->getVariable()} = \$this->{$repository->toCamelCase()}->find(\$uuid);
        if (! {$entity->getVariable()} instanceof {$entity->getClassName()}) {
            throw new NotFoundException(Message::resourceNotFound('{$entity->getClassName()}'));
        }

        return {$entity->getVariable()};
BODY);

            $class->addMethod($findResource);
        }

        return $class->render();
    }
}
