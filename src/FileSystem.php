<?php

declare(strict_types=1);

namespace Dot\Maker;

use Dot\Maker\FileSystem\Directory;
use Dot\Maker\FileSystem\File;

use function getcwd;
use function preg_replace;
use function sprintf;

class FileSystem
{
    private string $moduleName;
    private string $rootDir;

    public function __construct(
        private readonly ContextInterface $context,
    ) {
        $this->rootDir = getcwd();
    }

    public function collection(string $name): File
    {
        $name = preg_replace('/Collection$/', '', $name);

        return new File(
            new Directory('Collection', sprintf('%s/src/%s/src', $this->rootDir, $this->moduleName)),
            sprintf('%s\\%s\\Collection', $this->context->getRootNamespace(), $this->moduleName),
            sprintf('%sCollection', $name),
        );
    }

    public function command(string $name): File
    {
        $name = preg_replace('/Command$/', '', $name);

        return new File(
            new Directory('Command', sprintf('%s/src/%s/src', $this->rootDir, $this->moduleName)),
            sprintf('%s\\%s\\Command', $this->context->getRootNamespace(), $this->moduleName),
            sprintf('%sCommand', $name),
        );
    }

    public function configProvider(string $name = 'ConfigProvider'): File
    {
        return new File(
            new Directory('src', sprintf('%s/src/%s', $this->rootDir, $this->moduleName)),
            sprintf('%s\\%s', $this->context->getRootNamespace(), $this->moduleName),
            $name,
        );
    }

    public function coreConfigProvider(string $name = 'ConfigProvider'): File
    {
        return new File(
            new Directory('src', sprintf('%s/src/Core/src/%s', $this->rootDir, $this->moduleName)),
            sprintf('%s\\%s', ContextInterface::NAMESPACE_CORE, $this->moduleName),
            $name,
        );
    }

    public function coreModule(string $name): Directory
    {
        return new Directory($name, sprintf('%s/src/Core/src', $this->rootDir));
    }

    public function entity(string $name): File
    {
        $name = preg_replace('/Entity$/', '', $name);

        if ($this->context->hasCore()) {
            return new File(
                new Directory('Entity', sprintf('%s/src/Core/src/%s/src', $this->rootDir, $this->moduleName)),
                sprintf('%s\\%s\\Entity', ContextInterface::NAMESPACE_CORE, $this->moduleName),
                $name,
            );
        }

        return new File(
            new Directory('Entity', sprintf('%s/src/%s/src', $this->rootDir, $this->moduleName)),
            sprintf('%s\\%s\\Entity', $this->context->getRootNamespace(), $this->moduleName),
            $name,
        );
    }

    public function form(string $name): File
    {
        $name = preg_replace('/Form$/', '', $name);

        return new File(
            new Directory('Form', sprintf('%s/src/%s/src', $this->rootDir, $this->moduleName)),
            sprintf('%s\\%s\\Form', $this->context->getRootNamespace(), $this->moduleName),
            sprintf('Create%sForm', $name),
        );
    }

    public function handler(string $name): File
    {
        $name = preg_replace('/Handler$/', '', $name);

        return new File(
            new Directory('Handler', sprintf('%s/src/%s/src', $this->rootDir, $this->moduleName)),
            sprintf('%s\\%s\\Handler', $this->context->getRootNamespace(), $this->moduleName),
            sprintf('%sHandler', $name),
        );
    }

    public function apiDeleteResourceHandler(string $name): File
    {
        $name = preg_replace('/Handler$/', '', $name);

        return new File(
            new Directory('Handler', sprintf('%s/src/%s/src', $this->rootDir, $this->moduleName)),
            sprintf('%s\\%s\\Handler', $this->context->getRootNamespace(), $this->moduleName),
            sprintf('Delete%sResourceHandler', $name),
        );
    }

    public function apiGetResourceHandler(string $name): File
    {
        $name = preg_replace('/Handler$/', '', $name);

        return new File(
            new Directory('Handler', sprintf('%s/src/%s/src', $this->rootDir, $this->moduleName)),
            sprintf('%s\\%s\\Handler', $this->context->getRootNamespace(), $this->moduleName),
            sprintf('Get%sResourceHandler', $name),
        );
    }

    public function apiGetCollectionResourceHandler(string $name): File
    {
        $name = preg_replace('/Handler$/', '', $name);

        return new File(
            new Directory('Handler', sprintf('%s/src/%s/src', $this->rootDir, $this->moduleName)),
            sprintf('%s\\%s\\Handler', $this->context->getRootNamespace(), $this->moduleName),
            sprintf('Get%sCollectionHandler', $name),
        );
    }

    public function apiPatchResourceHandler(string $name): File
    {
        $name = preg_replace('/Handler$/', '', $name);

        return new File(
            new Directory('Handler', sprintf('%s/src/%s/src', $this->rootDir, $this->moduleName)),
            sprintf('%s\\%s\\Handler', $this->context->getRootNamespace(), $this->moduleName),
            sprintf('Patch%sResourceHandler', $name),
        );
    }

    public function apiPostResourceHandler(string $name): File
    {
        $name = preg_replace('/Handler$/', '', $name);

        return new File(
            new Directory('Handler', sprintf('%s/src/%s/src', $this->rootDir, $this->moduleName)),
            sprintf('%s\\%s\\Handler', $this->context->getRootNamespace(), $this->moduleName),
            sprintf('Post%sResourceHandler', $name),
        );
    }

    public function apiPutResourceHandler(string $name): File
    {
        $name = preg_replace('/Handler$/', '', $name);

        return new File(
            new Directory('Handler', sprintf('%s/src/%s/src', $this->rootDir, $this->moduleName)),
            sprintf('%s\\%s\\Handler', $this->context->getRootNamespace(), $this->moduleName),
            sprintf('Post%sResourceHandler', $name),
        );
    }

    public function getResourceCreateHandler(string $name): File
    {
        return new File(
            new Directory('Handler', sprintf('%s/src/%s/src', $this->rootDir, $this->moduleName)),
            sprintf('%s\\%s\\Handler', $this->context->getRootNamespace(), $this->moduleName),
            sprintf('Get%sCreateFormHandler', $name),
        );
    }

    public function getResourceDeleteHandler(string $name): File
    {
        return new File(
            new Directory('Handler', sprintf('%s/src/%s/src', $this->rootDir, $this->moduleName)),
            sprintf('%s\\%s\\Handler', $this->context->getRootNamespace(), $this->moduleName),
            sprintf('Get%sDeleteFormHandler', $name),
        );
    }

    public function getResourceEditHandler(string $name): File
    {
        return new File(
            new Directory('Handler', sprintf('%s/src/%s/src', $this->rootDir, $this->moduleName)),
            sprintf('%s\\%s\\Handler', $this->context->getRootNamespace(), $this->moduleName),
            sprintf('Get%sEditFormHandler', $name),
        );
    }

    public function getResourceListHandler(string $name): File
    {
        return new File(
            new Directory('Handler', sprintf('%s/src/%s/src', $this->rootDir, $this->moduleName)),
            sprintf('%s\\%s\\Handler', $this->context->getRootNamespace(), $this->moduleName),
            sprintf('Get%sListHandler', $name),
        );
    }

    public function postResourceCreateHandler(string $name): File
    {
        return new File(
            new Directory('Handler', sprintf('%s/src/%s/src', $this->rootDir, $this->moduleName)),
            sprintf('%s\\%s\\Handler', $this->context->getRootNamespace(), $this->moduleName),
            sprintf('Post%sCreateHandler', $name),
        );
    }

    public function postResourceDeleteHandler(string $name): File
    {
        return new File(
            new Directory('Handler', sprintf('%s/src/%s/src', $this->rootDir, $this->moduleName)),
            sprintf('%s\\%s\\Handler', $this->context->getRootNamespace(), $this->moduleName),
            sprintf('Post%sDeleteHandler', $name),
        );
    }

    public function postResourceEditHandler(string $name): File
    {
        return new File(
            new Directory('Handler', sprintf('%s/src/%s/src', $this->rootDir, $this->moduleName)),
            sprintf('%s\\%s\\Handler', $this->context->getRootNamespace(), $this->moduleName),
            sprintf('Post%sEditHandler', $name),
        );
    }

    public function inputFilter(string $name): File
    {
        $name = preg_replace('/InputFilter$/', '', $name);

        return new File(
            new Directory('InputFilter', sprintf('%s/src/%s/src', $this->rootDir, $this->moduleName)),
            sprintf('%s\\%s\\InputFilter', $this->context->getRootNamespace(), $this->moduleName),
            sprintf('%sInputFilter', $name),
        );
    }

    public function createResourceInputFilter(string $name): File
    {
        $name = preg_replace('/InputFilter$/', '', $name);

        return new File(
            new Directory('InputFilter', sprintf('%s/src/%s/src', $this->rootDir, $this->moduleName)),
            sprintf('%s\\%s\\InputFilter', $this->context->getRootNamespace(), $this->moduleName),
            sprintf('Create%sInputFilter', $name),
        );
    }

    public function deleteResourceInputFilter(string $name): File
    {
        $name = preg_replace('/InputFilter$/', '', $name);

        return new File(
            new Directory('InputFilter', sprintf('%s/src/%s/src', $this->rootDir, $this->moduleName)),
            sprintf('%s\\%s\\InputFilter', $this->context->getRootNamespace(), $this->moduleName),
            sprintf('Delete%sInputFilter', $name),
        );
    }

    public function editResourceInputFilter(string $name): File
    {
        $name = preg_replace('/InputFilter$/', '', $name);

        return new File(
            new Directory('InputFilter', sprintf('%s/src/%s/src', $this->rootDir, $this->moduleName)),
            sprintf('%s\\%s\\InputFilter', $this->context->getRootNamespace(), $this->moduleName),
            sprintf('Edit%sInputFilter', $name),
        );
    }

    public function middleware(string $name): File
    {
        $name = preg_replace('/Middleware$/', '', $name);

        return new File(
            new Directory('Middleware', sprintf('%s/src/%s/src', $this->rootDir, $this->moduleName)),
            sprintf('%s\\%s\\Middleware', $this->context->getRootNamespace(), $this->moduleName),
            sprintf('%sMiddleware', $name),
        );
    }

    public function module(string $name): Directory
    {
        return new Directory($name, sprintf('%s/src', $this->rootDir));
    }

    public function repository(string $name): File
    {
        $name = preg_replace('/Repository$/', '', $name);

        if ($this->context->hasCore()) {
            return new File(
                new Directory('Repository', sprintf('%s/src/Core/src/%s/src', $this->rootDir, $this->moduleName)),
                sprintf('%s\\%s\\Repository', ContextInterface::NAMESPACE_CORE, $this->moduleName),
                sprintf('%sRepository', $name),
            );
        }

        return new File(
            new Directory('Repository', sprintf('%s/src/%s/src', $this->rootDir, $this->moduleName)),
            sprintf('%s\\%s\\Repository', $this->context->getRootNamespace(), $this->moduleName),
            sprintf('%sRepository', $name),
        );
    }

    public function routesDelegator(string $name = 'RoutesDelegator'): File
    {
        return new File(
            new Directory('src', sprintf('%s/src/%s', $this->rootDir, $this->moduleName)),
            sprintf('%s\\%s', $this->context->getRootNamespace(), $this->moduleName),
            $name,
        );
    }

    public function service(string $name): File
    {
        $name = preg_replace('/Service$/', '', $name);

        return new File(
            new Directory('Service', sprintf('%s/src/%s/src', $this->rootDir, $this->moduleName)),
            sprintf('%s\\%s\\Service', $this->context->getRootNamespace(), $this->moduleName),
            sprintf('%sService', $name),
        );
    }

    public function serviceInterface(string $name): File
    {
        $name = preg_replace('/ServiceInterface$/', '', $name);

        return new File(
            new Directory('Service', sprintf('%s/src/%s/src', $this->rootDir, $this->moduleName)),
            sprintf('%s\\%s\\Service', $this->context->getRootNamespace(), $this->moduleName),
            sprintf('%sServiceInterface', $name),
        );
    }

    public function getModuleName(): string
    {
        return $this->moduleName;
    }

    public function setModuleName(string $moduleName): void
    {
        $this->moduleName = $moduleName;
    }
}
