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
    case HandlerApiDeleteResource  = DeleteResourceHandler::class;
    case HandlerApiGetResource     = GetResourceHandler::class;
    case HandlerApiGetCollection   = GetCollectionHandler::class;
    case HandlerApiPatchResource   = PatchResourceHandler::class;
    case HandlerApiPostResource    = PostResourceHandler::class;
    case HandlerApiPutResource     = PutResourceHandler::class;
    case HandlerGetCreateResource  = GetCreateResourceHandler::class;
    case HandlerGetDeleteResource  = GetDeleteResourceHandler::class;
    case HandlerGetEditResource    = GetEditResourceHandler::class;
    case HandlerGetListResource    = GetListResourceHandler::class;
    case HandlerGetViewResource    = GetViewResourceHandler::class;
    case HandlerPostCreateResource = PostCreateResourceHandler::class;
    case HandlerPostDeleteResource = PostDeleteResourceHandler::class;
    case HandlerPostEditResource   = PostEditResourceHandler::class;
    case InputFilter               = 'inputFilter';
    case Middleware                = 'middleware';
    case Module                    = 'module';
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
            self::Repository                => Repository::class,
            self::Service                   => Service::class,
            self::ServiceInterface          => ServiceInterface::class,
        };
    }
}
