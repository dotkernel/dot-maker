<?php

declare(strict_types=1);

namespace Dot\Maker;

use Dot\Maker\Exception\RuntimeException;
use Dot\Maker\FileSystem\Directory;
use Dot\Maker\FileSystem\File;

use function sprintf;

class FileSystem
{
    private ?string $moduleName = null;

    public function __construct(
        private readonly Context $context,
    ) {
    }

    public function collection(string $name): File
    {
        $name = Component::stripSuffix($name, 'Collection');

        return new File(
            new Directory('Collection', $this->modulePath('src')),
            sprintf('%s\\%s\\Collection', $this->context->getRootNamespace(), $this->getModuleName()),
            sprintf('%sCollection', $name),
        );
    }

    public function command(string $name): File
    {
        $name = Component::stripSuffix($name, 'Command');

        return new File(
            new Directory('Command', $this->modulePath('src')),
            sprintf('%s\\%s\\Command', $this->context->getRootNamespace(), $this->getModuleName()),
            sprintf('%sCommand', $name),
        );
    }

    public function configProvider(string $name = 'ConfigProvider'): File
    {
        return new File(
            new Directory('src', $this->modulePath()),
            sprintf('%s\\%s', $this->context->getRootNamespace(), $this->getModuleName()),
            $name,
        );
    }

    public function coreConfigProvider(string $name = 'ConfigProvider'): File
    {
        return new File(
            new Directory('src', $this->coreModulePath()),
            sprintf('%s\\%s', Context::NAMESPACE_CORE, $this->getModuleName()),
            $name,
        );
    }

    public function coreModule(string $name): Directory
    {
        return new Directory($name, sprintf('%s/src/Core/src', $this->context->getProjectPath()));
    }

    public function entity(string $name): File
    {
        $name = Component::stripSuffix($name, 'Entity');

        if ($this->context->hasCore()) {
            return new File(
                new Directory(
                    'Entity',
                    $this->coreModulePath('src')
                ),
                sprintf('%s\\%s\\Entity', Context::NAMESPACE_CORE, $this->getModuleName()),
                $name,
            );
        }

        return new File(
            new Directory('Entity', $this->modulePath('src')),
            sprintf('%s\\%s\\Entity', $this->context->getRootNamespace(), $this->getModuleName()),
            $name,
        );
    }

    public function form(string $name): File
    {
        $name = Component::stripSuffix($name, 'Form');

        return new File(
            new Directory('Form', $this->modulePath('src')),
            sprintf('%s\\%s\\Form', $this->context->getRootNamespace(), $this->getModuleName()),
            sprintf('%sForm', $name),
        );
    }

    public function createResourceForm(string $name): File
    {
        $name = Component::stripSuffix($name, 'Form');

        return new File(
            new Directory('Form', $this->modulePath('src')),
            sprintf('%s\\%s\\Form', $this->context->getRootNamespace(), $this->getModuleName()),
            sprintf('Create%sForm', $name),
        );
    }

    public function deleteResourceForm(string $name): File
    {
        $name = Component::stripSuffix($name, 'Form');

        return new File(
            new Directory('Form', $this->modulePath('src')),
            sprintf('%s\\%s\\Form', $this->context->getRootNamespace(), $this->getModuleName()),
            sprintf('Delete%sForm', $name),
        );
    }

    public function editResourceForm(string $name): File
    {
        $name = Component::stripSuffix($name, 'Form');

        return new File(
            new Directory('Form', $this->modulePath('src')),
            sprintf('%s\\%s\\Form', $this->context->getRootNamespace(), $this->getModuleName()),
            sprintf('Edit%sForm', $name),
        );
    }

    public function handler(string $name): File
    {
        $name = Component::stripSuffix($name, 'Handler');

        return new File(
            new Directory('Handler', $this->modulePath('src')),
            sprintf('%s\\%s\\Handler\\%s', $this->context->getRootNamespace(), $this->getModuleName(), $name),
            sprintf('%sHandler', $name),
        );
    }

    public function apiDeleteResourceHandler(string $name): File
    {
        $name = Component::stripSuffix($name, 'Handler');

        return new File(
            new Directory($name, $this->modulePath('src/Handler')),
            sprintf('%s\\%s\\Handler\\%s', $this->context->getRootNamespace(), $this->getModuleName(), $name),
            sprintf('Delete%sResourceHandler', $name),
        );
    }

    public function apiGetResourceHandler(string $name): File
    {
        $name = Component::stripSuffix($name, 'Handler');

        return new File(
            new Directory($name, $this->modulePath('src/Handler')),
            sprintf('%s\\%s\\Handler\\%s', $this->context->getRootNamespace(), $this->getModuleName(), $name),
            sprintf('Get%sResourceHandler', $name),
        );
    }

    public function apiGetCollectionHandler(string $name): File
    {
        $name = Component::stripSuffix($name, 'Handler');

        return new File(
            new Directory($name, $this->modulePath('src/Handler')),
            sprintf('%s\\%s\\Handler\\%s', $this->context->getRootNamespace(), $this->getModuleName(), $name),
            sprintf('Get%sCollectionHandler', $name),
        );
    }

    public function apiPatchResourceHandler(string $name): File
    {
        $name = Component::stripSuffix($name, 'Handler');

        return new File(
            new Directory($name, $this->modulePath('src/Handler')),
            sprintf('%s\\%s\\Handler\\%s', $this->context->getRootNamespace(), $this->getModuleName(), $name),
            sprintf('Patch%sResourceHandler', $name),
        );
    }

    public function apiPostResourceHandler(string $name): File
    {
        $name = Component::stripSuffix($name, 'Handler');

        return new File(
            new Directory($name, $this->modulePath('src/Handler')),
            sprintf('%s\\%s\\Handler\\%s', $this->context->getRootNamespace(), $this->getModuleName(), $name),
            sprintf('Post%sResourceHandler', $name),
        );
    }

    public function apiPutResourceHandler(string $name): File
    {
        $name = Component::stripSuffix($name, 'Handler');

        return new File(
            new Directory($name, $this->modulePath('src/Handler')),
            sprintf('%s\\%s\\Handler\\%s', $this->context->getRootNamespace(), $this->getModuleName(), $name),
            sprintf('Put%sResourceHandler', $name),
        );
    }

    public function getCreateResourceHandler(string $name): File
    {
        return new File(
            new Directory($name, $this->modulePath('src/Handler')),
            sprintf('%s\\%s\\Handler\\%s', $this->context->getRootNamespace(), $this->getModuleName(), $name),
            sprintf('GetCreate%sFormHandler', $name),
        );
    }

    public function getDeleteResourceHandler(string $name): File
    {
        return new File(
            new Directory($name, $this->modulePath('src/Handler')),
            sprintf('%s\\%s\\Handler\\%s', $this->context->getRootNamespace(), $this->getModuleName(), $name),
            sprintf('GetDelete%sFormHandler', $name),
        );
    }

    public function getEditResourceHandler(string $name): File
    {
        return new File(
            new Directory($name, $this->modulePath('src/Handler')),
            sprintf('%s\\%s\\Handler\\%s', $this->context->getRootNamespace(), $this->getModuleName(), $name),
            sprintf('GetEdit%sFormHandler', $name),
        );
    }

    public function getListResourcesHandler(string $name): File
    {
        return new File(
            new Directory($name, $this->modulePath('src/Handler')),
            sprintf('%s\\%s\\Handler\\%s', $this->context->getRootNamespace(), $this->getModuleName(), $name),
            sprintf('GetList%sHandler', $name),
        );
    }

    public function getViewResourceHandler(string $name): File
    {
        return new File(
            new Directory($name, $this->modulePath('src/Handler')),
            sprintf('%s\\%s\\Handler\\%s', $this->context->getRootNamespace(), $this->getModuleName(), $name),
            sprintf('GetView%sHandler', $name),
        );
    }

    public function postCreateResourceHandler(string $name): File
    {
        return new File(
            new Directory($name, $this->modulePath('src/Handler')),
            sprintf('%s\\%s\\Handler\\%s', $this->context->getRootNamespace(), $this->getModuleName(), $name),
            sprintf('PostCreate%sHandler', $name),
        );
    }

    public function postDeleteResourceHandler(string $name): File
    {
        return new File(
            new Directory($name, $this->modulePath('src/Handler')),
            sprintf('%s\\%s\\Handler\\%s', $this->context->getRootNamespace(), $this->getModuleName(), $name),
            sprintf('PostDelete%sHandler', $name),
        );
    }

    public function postEditResourceHandler(string $name): File
    {
        return new File(
            new Directory($name, $this->modulePath('src/Handler')),
            sprintf('%s\\%s\\Handler\\%s', $this->context->getRootNamespace(), $this->getModuleName(), $name),
            sprintf('PostEdit%sHandler', $name),
        );
    }

    public function input(string $name): File
    {
        $name = Component::stripSuffix($name, 'Input');

        return new File(
            new Directory(
                'Input',
                $this->modulePath('src/InputFilter')
            ),
            sprintf('%s\\%s\\InputFilter\\Input', $this->context->getRootNamespace(), $this->getModuleName()),
            sprintf('%sInput', $name),
        );
    }

    public function confirmDeleteInput(string $name): File
    {
        $name = Component::stripSuffix($name, 'Input');

        return new File(
            new Directory(
                'Input',
                $this->modulePath('src/InputFilter')
            ),
            sprintf('%s\\%s\\InputFilter\\Input', $this->context->getRootNamespace(), $this->getModuleName()),
            sprintf('ConfirmDelete%sInput', $name),
        );
    }

    public function inputFilter(string $name): File
    {
        $name = Component::stripSuffix($name, 'InputFilter');

        return new File(
            new Directory('InputFilter', $this->modulePath('src')),
            sprintf('%s\\%s\\InputFilter', $this->context->getRootNamespace(), $this->getModuleName()),
            sprintf('%sInputFilter', $name),
        );
    }

    public function createResourceInputFilter(string $name): File
    {
        $name = Component::stripSuffix($name, 'InputFilter');

        return new File(
            new Directory('InputFilter', $this->modulePath('src')),
            sprintf('%s\\%s\\InputFilter', $this->context->getRootNamespace(), $this->getModuleName()),
            sprintf('Create%sInputFilter', $name),
        );
    }

    public function deleteResourceInputFilter(string $name): File
    {
        $name = Component::stripSuffix($name, 'InputFilter');

        return new File(
            new Directory('InputFilter', $this->modulePath('src')),
            sprintf('%s\\%s\\InputFilter', $this->context->getRootNamespace(), $this->getModuleName()),
            sprintf('Delete%sInputFilter', $name),
        );
    }

    public function editResourceInputFilter(string $name): File
    {
        $name = Component::stripSuffix($name, 'InputFilter');

        return new File(
            new Directory('InputFilter', $this->modulePath('src')),
            sprintf('%s\\%s\\InputFilter', $this->context->getRootNamespace(), $this->getModuleName()),
            sprintf('Edit%sInputFilter', $name),
        );
    }

    public function replaceResourceInputFilter(string $name): File
    {
        $name = Component::stripSuffix($name, 'InputFilter');

        return new File(
            new Directory('InputFilter', $this->modulePath('src')),
            sprintf('%s\\%s\\InputFilter', $this->context->getRootNamespace(), $this->getModuleName()),
            sprintf('Replace%sInputFilter', $name),
        );
    }

    public function middleware(string $name): File
    {
        $name = Component::stripSuffix($name, 'Middleware');

        return new File(
            new Directory('Middleware', $this->modulePath('src')),
            sprintf('%s\\%s\\Middleware', $this->context->getRootNamespace(), $this->getModuleName()),
            sprintf('%sMiddleware', $name),
        );
    }

    public function module(string $name): Directory
    {
        return new Directory($name, sprintf('%s/src', $this->context->getProjectPath()));
    }

    public function openApi(string $name = 'OpenAPI'): File
    {
        return new File(
            new Directory('src', $this->modulePath()),
            sprintf('%s\\%s', $this->context->getRootNamespace(), $this->getModuleName()),
            $name,
        );
    }

    public function repository(string $name): File
    {
        $name = Component::stripSuffix($name, 'Repository');

        if ($this->context->hasCore()) {
            return new File(
                new Directory(
                    'Repository',
                    $this->coreModulePath('src')
                ),
                sprintf('%s\\%s\\Repository', Context::NAMESPACE_CORE, $this->getModuleName()),
                sprintf('%sRepository', $name),
            );
        }

        return new File(
            new Directory('Repository', $this->modulePath('src')),
            sprintf('%s\\%s\\Repository', $this->context->getRootNamespace(), $this->getModuleName()),
            sprintf('%sRepository', $name),
        );
    }

    public function routesDelegator(string $name = 'RoutesDelegator'): File
    {
        return new File(
            new Directory('src', $this->modulePath()),
            sprintf('%s\\%s', $this->context->getRootNamespace(), $this->getModuleName()),
            $name,
        );
    }

    public function service(string $name): File
    {
        $name = Component::stripSuffix($name, 'Service');

        return new File(
            new Directory('Service', $this->modulePath('src')),
            sprintf('%s\\%s\\Service', $this->context->getRootNamespace(), $this->getModuleName()),
            sprintf('%sService', $name),
        );
    }

    public function serviceInterface(string $name): File
    {
        $name = Component::stripSuffix($name, 'Interface');
        $name = Component::stripSuffix($name, 'Service');

        return new File(
            new Directory('Service', $this->modulePath('src')),
            sprintf('%s\\%s\\Service', $this->context->getRootNamespace(), $this->getModuleName()),
            sprintf('%sServiceInterface', $name),
        );
    }

    public function templateDir(string $name): Directory
    {
        return new Directory(
            $name,
            $this->modulePath('templates')
        );
    }

    public function templateFile(string $subDirectory, string $name): File
    {
        return new File(
            $this->templateDir($subDirectory),
            '',
            $name,
            'html.twig'
        );
    }

    /**
     * @throws RuntimeException
     */
    public function getModuleName(): string
    {
        return $this->moduleName ?? throw new RuntimeException('No module selected.');
    }

    /**
     * @throws RuntimeException
     */
    private function modulePath(string $subPath = ''): string
    {
        $path = sprintf('%s/src/%s', $this->context->getProjectPath(), $this->getModuleName());

        return $subPath === '' ? $path : sprintf('%s/%s', $path, $subPath);
    }

    /**
     * @throws RuntimeException
     */
    private function coreModulePath(string $subPath = ''): string
    {
        $path = sprintf('%s/src/Core/src/%s', $this->context->getProjectPath(), $this->getModuleName());

        return $subPath === '' ? $path : sprintf('%s/%s', $path, $subPath);
    }

    public function setModuleName(string $moduleName): self
    {
        $this->moduleName = $moduleName;

        return $this;
    }
}
