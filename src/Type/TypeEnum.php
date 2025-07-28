<?php

declare(strict_types=1);

namespace Dot\Maker\Type;

use Dot\Maker\Type\Handler\Api\DeleteResourceHandler;
use Dot\Maker\Type\Handler\Api\GetCollectionHandler;
use Dot\Maker\Type\Handler\Api\GetResourceHandler;
use Dot\Maker\Type\Handler\Api\PatchResourceHandler;
use Dot\Maker\Type\Handler\Api\PostResourceHandler;
use Dot\Maker\Type\Handler\Api\PutResourceHandler;
use Dot\Maker\Type\Handler\GetCreateResourceHandler;
use Dot\Maker\Type\Handler\GetDeleteResourceHandler;
use Dot\Maker\Type\Handler\GetEditResourceHandler;
use Dot\Maker\Type\Handler\GetListResourceHandler;
use Dot\Maker\Type\Handler\GetViewResourceHandler;
use Dot\Maker\Type\Handler\PostCreateResourceHandler;
use Dot\Maker\Type\Handler\PostDeleteResourceHandler;
use Dot\Maker\Type\Handler\PostEditResourceHandler;

enum TypeEnum: string
{
    case Collection                = 'collection';
    case Command                   = 'command';
    case Entity                    = 'entity';
    case Form                      = 'form';
    case Handler                   = 'handler';
    case HandlerApiDeleteResource  = 'api-delete-resource-handler';
    case HandlerApiGetResource     = 'api-get-resource-handler';
    case HandlerApiGetCollection   = 'api-get-collection-handler';
    case HandlerApiPatchResource   = 'api-patch-resource-handler';
    case HandlerApiPostResource    = 'api-post-resource-handler';
    case HandlerApiPutResource     = 'api-put-resource-handler';
    case HandlerGetCreateResource  = 'get-create-resource-handler';
    case HandlerGetDeleteResource  = 'get-delete-resource-handler';
    case HandlerGetEditResource    = 'get-edit-resource-handler';
    case HandlerGetListResource    = 'get-collection-handler';
    case HandlerGetViewResource    = 'get-view-resource-handler';
    case HandlerPostCreateResource = 'post-create-resource-handler';
    case HandlerPostDeleteResource = 'post-delete-resource-handler';
    case HandlerPostEditResource   = 'post-edit-resource-handler';
    case InputFilter               = 'inputFilter';
    case Middleware                = 'middleware';
    case Module                    = 'module';
    case OpenApi                   = 'openApi';
    case Repository                = 'repository';
    case Service                   = 'service';
    case ServiceInterface          = 'serviceInterface';

    public function getClass(): string
    {
        return match ($this) {
            self::Collection                => Collection::class,
            self::Command                   => Command::class,
            self::Entity                    => Entity::class,
            self::Form                      => Form::class,
            self::Handler                   => Handler::class,
            self::HandlerApiDeleteResource  => DeleteResourceHandler::class,
            self::HandlerApiGetResource     => GetResourceHandler::class,
            self::HandlerApiGetCollection   => GetCollectionHandler::class,
            self::HandlerApiPatchResource   => PatchResourceHandler::class,
            self::HandlerApiPostResource    => PostResourceHandler::class,
            self::HandlerApiPutResource     => PutResourceHandler::class,
            self::HandlerGetCreateResource  => GetCreateResourceHandler::class,
            self::HandlerGetDeleteResource  => GetDeleteResourceHandler::class,
            self::HandlerGetEditResource    => GetEditResourceHandler::class,
            self::HandlerGetListResource    => GetListResourceHandler::class,
            self::HandlerGetViewResource    => GetViewResourceHandler::class,
            self::HandlerPostCreateResource => PostCreateResourceHandler::class,
            self::HandlerPostDeleteResource => PostDeleteResourceHandler::class,
            self::HandlerPostEditResource   => PostEditResourceHandler::class,
            self::InputFilter               => InputFilter::class,
            self::Middleware                => Middleware::class,
            self::Module                    => Module::class,
            self::OpenApi                   => OpenApi::class,
            self::Repository                => Repository::class,
            self::Service                   => Service::class,
            self::ServiceInterface          => ServiceInterface::class,
        };
    }

    public function getCallables(): array
    {
        return [
            self::Collection,
            self::Command,
            self::Entity,
            self::Form,
            self::Handler,
            self::InputFilter,
            self::Middleware,
            self::Module,
            self::OpenApi,
            self::Repository,
            self::Service,
            self::ServiceInterface,
        ];
    }
}
