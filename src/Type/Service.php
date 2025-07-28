<?php

declare(strict_types=1);

namespace Dot\Maker\Type;

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

        $content = $this->render(
            $service,
            $this->fileSystem->serviceInterface($name),
            $this->fileSystem->repository($name),
            $this->fileSystem->entity($name),
        );

        try {
            $service->create($content);
            Output::info(sprintf('Created Service "%s"', $service->getPath()));
        } catch (RuntimeException $exception) {
            Output::error($exception->getMessage());
        }

        return $service;
    }

    public function render(File $service, File $serviceInterface, File $repository, File $entity): string
    {
        $class = (new ClassFile($service->getComponent()->getNamespace(), $service->getComponent()->getClassName()))
            ->addInterface($serviceInterface->getComponent()->getClassName())
            ->useClass(Import::DOT_DEPENDENCYINJECTION_ATTRIBUTE_INJECT)
            ->useClass(Import::DOCTRINE_ORM_QUERYBUILDER)
            ->useClass($repository->getComponent()->getFqcn())
            ->useClass($entity->getComponent()->getFqcn())
            ->useClass($this->getAppHelperPaginatorFqcn())
            ->useFunction('in_array');

        $promotedProperty = new PromotedProperty(
            $repository->getComponent()->getPropertyName(true),
            $repository->getComponent()->getClassName()
        );

        $constructor = (new Constructor())
            ->addInject(
                (new Inject())->addArgument($repository->getComponent()->getClassString())
            )
            ->addPromotedProperty($promotedProperty);
        $class->addMethod($constructor);
        $class->addMethod($promotedProperty->getGetter());

        $deleteResource = (new Method($entity->getComponent()->getDeleteMethodName()))
            ->addParameter(
                new Parameter($entity->getComponent()->getPropertyName(), $entity->getComponent()->getClassName())
            )
            ->setBody(<<<BODY
        \$this->{$repository->getComponent()->getPropertyName()}->deleteResource({$entity->getComponent()->getVariable()});
BODY);
        $class->addMethod($deleteResource);

        $getResources = (new Method($entity->getComponent()->getCollectionMethodName()))
            ->addParameter(
                new Parameter('params', 'array')
            )
            ->setReturnType($this->context->isApi() ? 'QueryBuilder' : 'array')
            ->setBody(<<<BODY
        \$filters = \$params['filters'] ?? [];
        \$params  = Paginator::getParams(\$params, '{$entity->getComponent()->getPropertyName()}.created');

        \$sortableColumns = [
            '{$entity->getComponent()->getPropertyName()}.created',
            '{$entity->getComponent()->getPropertyName()}.updated',
        ];
        if (! in_array(\$params['sort'], \$sortableColumns, true)) {
            \$params['sort'] = '{$entity->getComponent()->getPropertyName()}.created';
        }

        return \$this->{$repository->getComponent()->getPropertyName()}->{$entity->getComponent()->getCollectionMethodName()}(\$params, \$filters);
BODY);
        $class->addMethod($getResources);

        $saveResource = (new Method($entity->getComponent()->getSaveMethodName()))
            ->setReturnType($entity->getComponent()->getClassName())
            ->addParameter(
                new Parameter('data', 'array')
            )
            ->addParameter(
                new Parameter($entity->getComponent()->getPropertyName(), $entity->getComponent()->getClassName(), true, 'null')
            )
            ->setBody(<<<BODY
        if (! {$entity->getComponent()->getVariable()} instanceof {$entity->getComponent()->getClassName()}) {
            {$entity->getComponent()->getVariable()} = new {$entity->getComponent()->getClassName()}();
        }

        \$this->{$repository->getComponent()->getPropertyName()}->saveResource({$entity->getComponent()->getVariable()});

        return {$entity->getComponent()->getVariable()};
BODY);
        $class->addMethod($saveResource);

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
