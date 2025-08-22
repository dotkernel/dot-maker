<?php

declare(strict_types=1);

namespace DotTest\Maker;

use Dot\Maker\Component;
use PHPUnit\Framework\TestCase;

class ComponentTest extends TestCase
{
    public function testWillRenderCollection(): void
    {
        $component = new Component('Api\\ModuleName\\Collection', 'BookStoreCollection');

        $this->assertSame('BookStoreCollection', $component->getClassName());
        $this->assertSame('BookStoreCollection::class', $component->getClassString());
        $this->assertSame(
            'Api\\ModuleName\\Collection\\BookStoreCollection',
            $component->getFqcn()
        );
        $this->assertSame('Api\\ModuleName\\Collection', $component->getNamespace());
        $this->assertSame('$bookStoreCollection', $component->getVariable());
        $this->assertSame('$bookStoreCollection', $component->getVariable(false));
        $this->assertSame('bookStoreCollection', $component->toCamelCase());
        $this->assertSame('bookStoreCollection', $component->toCamelCase(true));
        $this->assertSame('book-store-collection', $component->toKebabCase());
        $this->assertSame('book-store-collection', $component->toKebabCase(false));
        $this->assertSame('book_store_collection', $component->toSnakeCase());
        $this->assertSame('book_store_collection', $component->toSnakeCase(false));
        $this->assertSame('BOOK_STORE_COLLECTION', $component->toUpperCase());
        $this->assertSame('BOOK_STORE_COLLECTION', $component->toUpperCase(false));
    }

    public function testWillRenderCommand(): void
    {
        $component = new Component('Api\\ModuleName\\Command', 'BookStoreCommand');

        $this->assertSame('BookStoreCommand', $component->getClassName());
        $this->assertSame('BookStoreCommand::class', $component->getClassString());
        $this->assertSame(
            'Api\\ModuleName\\Command\\BookStoreCommand',
            $component->getFqcn()
        );
        $this->assertSame('Api\\ModuleName\\Command', $component->getNamespace());
        $this->assertSame('$bookStoreCommand', $component->getVariable());
        $this->assertSame('$bookStoreCommand', $component->getVariable(false));
        $this->assertSame('bookStoreCommand', $component->toCamelCase());
        $this->assertSame('bookStoreCommand', $component->toCamelCase(true));
        $this->assertSame('book-store-command', $component->toKebabCase());
        $this->assertSame('book-store-command', $component->toKebabCase(false));
        $this->assertSame('book_store_command', $component->toSnakeCase());
        $this->assertSame('book_store_command', $component->toSnakeCase(false));
        $this->assertSame('BOOK_STORE_COMMAND', $component->toUpperCase());
        $this->assertSame('BOOK_STORE_COMMAND', $component->toUpperCase(false));
    }

    public function testWillRenderEntity(): void
    {
        $component = new Component('Api\\ModuleName\\Entity', 'BookStore');

        $this->assertSame('BookStore', $component->getClassName());
        $this->assertSame('BookStore::class', $component->getClassString());
        $this->assertSame('getBookStores', $component->getCollectionMethodName());
        $this->assertSame('deleteBookStore', $component->getDeleteMethodName());
        $this->assertSame('findBookStore', $component->getFindMethodName());
        $this->assertSame(
            'Api\\ModuleName\\Entity\\BookStore',
            $component->getFqcn()
        );
        $this->assertSame('getBookStore', $component->getGetterName());
        $this->assertSame('Api\\ModuleName\\Entity', $component->getNamespace());
        $this->assertSame('saveBookStore', $component->getSaveMethodName());
        $this->assertSame('setBookStore', $component->getSetterName());
        $this->assertSame('$bookStore', $component->getVariable());
        $this->assertSame('$bookStore', $component->getVariable(false));
        $this->assertSame('BookStores', Component::pluralize('BookStore'));
        $this->assertSame('bookStore', $component->toCamelCase());
        $this->assertSame('bookStore', $component->toCamelCase(true));
        $this->assertSame('book-store', $component->toKebabCase());
        $this->assertSame('book-store', $component->toKebabCase(false));
        $this->assertSame('book_store', $component->toSnakeCase());
        $this->assertSame('book_store', $component->toSnakeCase(false));
        $this->assertSame('BOOK_STORE', $component->toUpperCase());
        $this->assertSame('BOOK_STORE', $component->toUpperCase(false));
        $this->assertSame('BookStores', $component->toPlural());
    }

    public function testWillRenderForm(): void
    {
        $component = new Component('Api\\ModuleName\\Form', 'CreateBookStoreForm');

        $this->assertSame('CreateBookStoreForm', $component->getClassName());
        $this->assertSame('CreateBookStoreForm::class', $component->getClassString());
        $this->assertSame(
            'Api\\ModuleName\\Form\\CreateBookStoreForm',
            $component->getFqcn()
        );
        $this->assertSame('Api\\ModuleName\\Form', $component->getNamespace());
        $this->assertSame('$createBookStoreForm', $component->getVariable());
        $this->assertSame('$createBookStoreForm', $component->getVariable(false));
        $this->assertSame('createBookStoreForm', $component->toCamelCase());
        $this->assertSame('createBookStoreForm', $component->toCamelCase(true));
        $this->assertSame('create-book-store-form', $component->toKebabCase());
        $this->assertSame('create-book-store-form', $component->toKebabCase(false));
        $this->assertSame('create_book_store_form', $component->toSnakeCase());
        $this->assertSame('create_book_store_form', $component->toSnakeCase(false));
        $this->assertSame('CREATE_BOOK_STORE_FORM', $component->toUpperCase());
        $this->assertSame('CREATE_BOOK_STORE_FORM', $component->toUpperCase(false));
    }

    public function testWillRenderHandler(): void
    {
        $component = new Component('Api\\ModuleName\\Handler', 'CreateBookStoreResourceHandler');

        $this->assertSame('CreateBookStoreResourceHandler', $component->getClassName());
        $this->assertSame('CreateBookStoreResourceHandler::class', $component->getClassString());
        $this->assertSame(
            'Api\\ModuleName\\Handler\\CreateBookStoreResourceHandler',
            $component->getFqcn()
        );
        $this->assertSame('Api\\ModuleName\\Handler', $component->getNamespace());
        $this->assertSame('$createBookStoreResourceHandler', $component->getVariable());
        $this->assertSame('$createBookStoreResourceHandler', $component->getVariable(false));
        $this->assertSame('createBookStoreResourceHandler', $component->toCamelCase());
        $this->assertSame('createBookStoreResourceHandler', $component->toCamelCase(true));
        $this->assertSame('create-book-store-resource-handler', $component->toKebabCase());
        $this->assertSame('create-book-store-resource-handler', $component->toKebabCase(false));
        $this->assertSame('create_book_store_resource_handler', $component->toSnakeCase());
        $this->assertSame('create_book_store_resource_handler', $component->toSnakeCase(false));
        $this->assertSame('CREATE_BOOK_STORE_RESOURCE_HANDLER', $component->toUpperCase());
        $this->assertSame('CREATE_BOOK_STORE_RESOURCE_HANDLER', $component->toUpperCase(false));
    }

    public function testWillRenderInput(): void
    {
        $component = new Component('Api\\ModuleName\\InputFilter\\Input', 'ConfirmDeleteBookStoreInput');

        $this->assertSame('ConfirmDeleteBookStoreInput', $component->getClassName());
        $this->assertSame('ConfirmDeleteBookStoreInput::class', $component->getClassString());
        $this->assertSame(
            'Api\\ModuleName\\InputFilter\\Input\\ConfirmDeleteBookStoreInput',
            $component->getFqcn()
        );
        $this->assertSame('Api\\ModuleName\\InputFilter\\Input', $component->getNamespace());
        $this->assertSame('$confirmDeleteBookStoreInput', $component->getVariable());
        $this->assertSame('$confirmDeleteBookStoreInput', $component->getVariable(false));
        $this->assertSame('confirmDeleteBookStoreInput', $component->toCamelCase());
        $this->assertSame('confirmDeleteBookStoreInput', $component->toCamelCase(true));
        $this->assertSame('confirm-delete-book-store-input', $component->toKebabCase());
        $this->assertSame('confirm-delete-book-store-input', $component->toKebabCase(false));
        $this->assertSame('confirm_delete_book_store_input', $component->toSnakeCase());
        $this->assertSame('confirm_delete_book_store_input', $component->toSnakeCase(false));
        $this->assertSame('CONFIRM_DELETE_BOOK_STORE_INPUT', $component->toUpperCase());
        $this->assertSame('CONFIRM_DELETE_BOOK_STORE_INPUT', $component->toUpperCase(false));
    }

    public function testWillRenderInputFilter(): void
    {
        $component = new Component('Api\\ModuleName\\InputFilter', 'CreateBookStoreInputFilter');

        $this->assertSame('CreateBookStoreInputFilter', $component->getClassName());
        $this->assertSame('CreateBookStoreInputFilter::class', $component->getClassString());
        $this->assertSame(
            'Api\\ModuleName\\InputFilter\\CreateBookStoreInputFilter',
            $component->getFqcn()
        );
        $this->assertSame('Api\\ModuleName\\InputFilter', $component->getNamespace());
        $this->assertSame('$createBookStoreInputFilter', $component->getVariable());
        $this->assertSame('$createBookStoreInputFilter', $component->getVariable(false));
        $this->assertSame('createBookStoreInputFilter', $component->toCamelCase());
        $this->assertSame('createBookStoreInputFilter', $component->toCamelCase(true));
        $this->assertSame('create-book-store-input-filter', $component->toKebabCase());
        $this->assertSame('create-book-store-input-filter', $component->toKebabCase(false));
        $this->assertSame('create_book_store_input_filter', $component->toSnakeCase());
        $this->assertSame('create_book_store_input_filter', $component->toSnakeCase(false));
        $this->assertSame('CREATE_BOOK_STORE_INPUT_FILTER', $component->toUpperCase());
        $this->assertSame('CREATE_BOOK_STORE_INPUT_FILTER', $component->toUpperCase(false));
    }

    public function testWillRenderInputMiddleware(): void
    {
        $component = new Component('Api\\ModuleName\\Middleware', 'BookStoreMiddleware');

        $this->assertSame('BookStoreMiddleware', $component->getClassName());
        $this->assertSame('BookStoreMiddleware::class', $component->getClassString());
        $this->assertSame(
            'Api\\ModuleName\\Middleware\\BookStoreMiddleware',
            $component->getFqcn()
        );
        $this->assertSame('Api\\ModuleName\\Middleware', $component->getNamespace());
        $this->assertSame('$bookStoreMiddleware', $component->getVariable());
        $this->assertSame('$bookStoreMiddleware', $component->getVariable(false));
        $this->assertSame('bookStoreMiddleware', $component->toCamelCase());
        $this->assertSame('bookStoreMiddleware', $component->toCamelCase(true));
        $this->assertSame('book-store-middleware', $component->toKebabCase());
        $this->assertSame('book-store-middleware', $component->toKebabCase(false));
        $this->assertSame('book_store_middleware', $component->toSnakeCase());
        $this->assertSame('book_store_middleware', $component->toSnakeCase(false));
        $this->assertSame('BOOK_STORE_MIDDLEWARE', $component->toUpperCase());
        $this->assertSame('BOOK_STORE_MIDDLEWARE', $component->toUpperCase(false));
    }

    public function testWillRenderInputRepository(): void
    {
        $component = new Component('Api\\ModuleName\\Repository', 'BookStoreRepository');

        $this->assertSame('BookStoreRepository', $component->getClassName());
        $this->assertSame('BookStoreRepository::class', $component->getClassString());
        $this->assertSame(
            'Api\\ModuleName\\Repository\\BookStoreRepository',
            $component->getFqcn()
        );
        $this->assertSame('Api\\ModuleName\\Repository', $component->getNamespace());
        $this->assertSame('$bookStoreRepository', $component->getVariable());
        $this->assertSame('$bookStoreRepository', $component->getVariable(false));
        $this->assertSame('bookStoreRepository', $component->toCamelCase());
        $this->assertSame('bookStoreRepository', $component->toCamelCase(true));
        $this->assertSame('book-store-repository', $component->toKebabCase());
        $this->assertSame('book-store-repository', $component->toKebabCase(false));
        $this->assertSame('book_store_repository', $component->toSnakeCase());
        $this->assertSame('book_store_repository', $component->toSnakeCase(false));
        $this->assertSame('BOOK_STORE_REPOSITORY', $component->toUpperCase());
        $this->assertSame('BOOK_STORE_REPOSITORY', $component->toUpperCase(false));
    }

    public function testWillRenderInputService(): void
    {
        $component = new Component('Api\\ModuleName\\Service', 'BookStoreService');

        $this->assertSame('BookStoreService', $component->getClassName());
        $this->assertSame('BookStoreService::class', $component->getClassString());
        $this->assertSame(
            'Api\\ModuleName\\Service\\BookStoreService',
            $component->getFqcn()
        );
        $this->assertSame('Api\\ModuleName\\Service', $component->getNamespace());
        $this->assertSame('$bookStoreService', $component->getVariable());
        $this->assertSame('$bookStoreService', $component->getVariable(false));
        $this->assertSame('bookStoreService', $component->toCamelCase());
        $this->assertSame('bookStoreService', $component->toCamelCase(true));
        $this->assertSame('book-store-service', $component->toKebabCase());
        $this->assertSame('book-store-service', $component->toKebabCase(false));
        $this->assertSame('book_store_service', $component->toSnakeCase());
        $this->assertSame('book_store_service', $component->toSnakeCase(false));
        $this->assertSame('BOOK_STORE_SERVICE', $component->toUpperCase());
        $this->assertSame('BOOK_STORE_SERVICE', $component->toUpperCase(false));
    }

    public function testWillRenderInputServiceInterface(): void
    {
        $component = new Component('Api\\ModuleName\\Service', 'BookStoreServiceInterface');

        $this->assertSame('BookStoreServiceInterface', $component->getClassName());
        $this->assertSame('BookStoreServiceInterface::class', $component->getClassString());
        $this->assertSame(
            'Api\\ModuleName\\Service\\BookStoreServiceInterface',
            $component->getFqcn()
        );
        $this->assertSame('Api\\ModuleName\\Service', $component->getNamespace());
        $this->assertSame('$bookStoreServiceInterface', $component->getVariable(false));
        $this->assertSame('$bookStoreService', $component->getVariable());
        $this->assertSame('bookStoreServiceInterface', $component->toCamelCase());
        $this->assertSame('bookStoreService', $component->toCamelCase(true));
        $this->assertSame('book-store-service-interface', $component->toKebabCase(false));
        $this->assertSame('book-store-service', $component->toKebabCase());
        $this->assertSame('book_store_service_interface', $component->toSnakeCase(false));
        $this->assertSame('book_store_service', $component->toSnakeCase());
        $this->assertSame('BOOK_STORE_SERVICE_INTERFACE', $component->toUpperCase(false));
        $this->assertSame('BOOK_STORE_SERVICE', $component->toUpperCase());
    }

    public function testWillPluralize(): void
    {
        $this->assertSame('buses', Component::pluralize('bus'));
        $this->assertSame('boxes', Component::pluralize('box'));
        $this->assertSame('jazzes', Component::pluralize('jazz'));
        $this->assertSame('fishes', Component::pluralize('fish'));
        $this->assertSame('watches', Component::pluralize('watch'));
        $this->assertSame('candies', Component::pluralize('candy'));
        $this->assertSame('books', Component::pluralize('book'));
    }
}
