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
use Dot\Maker\ContextInterface;
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
                $this->initComponent(TypeEnum::ServiceInterface)->create($name);
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
            throw new BadRequestException(sprintf('Invalid Service name: "%s"', $name));
        }

        $service = $this->fileSystem->service($name);
        if ($service->exists()) {
            throw DuplicateFileException::create($service);
        }

        $repository = $this->fileSystem->repository($name);
        $entity     = $this->fileSystem->entity($name);

        $serviceInterface = $this->fileSystem->serviceInterface($name);

        $content = $this->render(
            $service->getComponent(),
            $serviceInterface->getComponent(),
            $repository->exists() ? $repository->getComponent() : null,
            $entity->exists() ? $entity->getComponent() : null,
        );

        try {
            $service->create($content);
            Output::info(sprintf('Created Service "%s"', $service->getPath()));
        } catch (RuntimeException $exception) {
            Output::error($exception->getMessage());
        }

        return $service;
    }

    public function render(
        Component $service,
        Component $serviceInterface,
        ?Component $repository = null,
        ?Component $entity = null,
    ): string {
        $class = (new ClassFile($service->getNamespace(), $service->getClassName()))
            ->addInterface($serviceInterface->getClassName());
        if ($repository !== null && $entity !== null) {
            $class
                ->useClass(Import::DOT_DEPENDENCYINJECTION_ATTRIBUTE_INJECT)
                ->useClass(Import::DOCTRINE_ORM_QUERYBUILDER)
                ->useClass($repository->getFqcn())
                ->useClass($entity->getFqcn())
                ->useClass($this->getAppHelperPaginatorFqcn())
                ->useFunction('in_array');

            $promotedProperty = new PromotedProperty($repository->getPropertyName(true), $repository->getClassName());

            $constructor = (new Constructor())
                ->addInject(
                    (new Inject())->addArgument($repository->getClassString())
                )
                ->addPromotedProperty($promotedProperty);
            $class->addMethod($constructor);
            $class->addMethod($promotedProperty->getGetter());

            $deleteResource = (new Method($entity->getDeleteMethodName()))
                ->addParameter(
                    new Parameter($entity->getPropertyName(), $entity->getClassName())
                )
                ->addBodyLine(
                    sprintf(
                        '$this->%s->deleteResource(%s);',
                        $repository->getPropertyName(),
                        $entity->getVariable()
                    )
                );
            $class->addMethod($deleteResource);

            $getResources = (new Method($entity->getCollectionMethodName()))
                ->addParameter(
                    new Parameter('params', 'array')
                )
                ->setReturnType($this->context->isApi() ? 'QueryBuilder' : 'array')
                ->addBodyLine('$filters = $params[\'filters\'] ?? [];')
                ->addBodyLine('return $filters;')
                ->setBody(<<<BODY
        \$filters = \$params['filters'] ?? [];
        \$params  = Paginator::getParams(\$params, '{$entity->getPropertyName()}.created');

        \$sortableColumns = [
            '{$entity->getPropertyName()}.created',
            '{$entity->getPropertyName()}.updated',
        ];
        if (! in_array(\$params['sort'], \$sortableColumns, true)) {
            \$params['sort'] = '{$entity->getPropertyName()}.created';
        }

        return \$this->{$repository->getPropertyName()}->{$entity->getCollectionMethodName()}(\$params, \$filters);
BODY);
            $class->addMethod($getResources);
        }

        return $class->render();
    }

    public function getAppHelperPaginatorFqcn(): string
    {
        $format = Import::ROOT_APP_HELPER_PAGINATOR;

        if ($this->context->hasCore()) {
            return sprintf($format, ContextInterface::NAMESPACE_CORE);
        }

        return sprintf($format, $this->context->getRootNamespace());
    }
}
