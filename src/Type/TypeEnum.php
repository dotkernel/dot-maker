<?php

declare(strict_types=1);

namespace Dot\Maker\Type;

use Dot\Maker\Type\Form\CreateResourceForm;
use Dot\Maker\Type\Form\DeleteResourceForm;
use Dot\Maker\Type\Form\EditResourceForm;
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
use Dot\Maker\Type\InputFilter\CreateResourceInputFilter;
use Dot\Maker\Type\InputFilter\DeleteResourceInputFilter;
use Dot\Maker\Type\InputFilter\EditResourceInputFilter;
use Dot\Maker\Type\InputFilter\ReplaceResourceInputFilter;

use function strtolower;

enum TypeEnum: string
{
    case Collection                 = Collection::class;
    case Command                    = Command::class;
    case ConfigProvider             = ConfigProvider::class;
    case CoreConfigProvider         = CoreConfigProvider::class;
    case Entity                     = Entity::class;
    case Form                       = Form::class;
    case FormCreateResource         = CreateResourceForm::class;
    case FormDeleteResource         = DeleteResourceForm::class;
    case FormEditResource           = EditResourceForm::class;
    case Handler                    = Handler::class;
    case HandlerApiDeleteResource   = DeleteResourceHandler::class;
    case HandlerApiGetResource      = GetResourceHandler::class;
    case HandlerApiGetCollection    = GetCollectionHandler::class;
    case HandlerApiPatchResource    = PatchResourceHandler::class;
    case HandlerApiPostResource     = PostResourceHandler::class;
    case HandlerApiPutResource      = PutResourceHandler::class;
    case HandlerGetCreateResource   = GetCreateResourceHandler::class;
    case HandlerGetDeleteResource   = GetDeleteResourceHandler::class;
    case HandlerGetEditResource     = GetEditResourceHandler::class;
    case HandlerGetListResource     = GetListResourceHandler::class;
    case HandlerGetViewResource     = GetViewResourceHandler::class;
    case HandlerPostCreateResource  = PostCreateResourceHandler::class;
    case HandlerPostDeleteResource  = PostDeleteResourceHandler::class;
    case HandlerPostEditResource    = PostEditResourceHandler::class;
    case Input                      = Input::class;
    case InputFilter                = InputFilter::class;
    case InputFilterCreateResource  = CreateResourceInputFilter::class;
    case InputFilterDeleteResource  = DeleteResourceInputFilter::class;
    case InputFilterEditResource    = EditResourceInputFilter::class;
    case InputFilterReplaceResource = ReplaceResourceInputFilter::class;
    case Middleware                 = Middleware::class;
    case Module                     = Module::class;
    case OpenApi                    = OpenApi::class;
    case Repository                 = Repository::class;
    case RoutesDelegator            = RoutesDelegator::class;
    case Service                    = Service::class;
    case ServiceInterface           = ServiceInterface::class;

    public static function getClass(string $name): ?string
    {
        if ($name === '') {
            return Help::class;
        }

        return match (strtolower($name)) {
            'collection'        => Collection::class,
            'command'           => Command::class,
            'entity'            => Entity::class,
            'form'              => Form::class,
            'handler'           => Handler::class,
            'input'             => Input::class,
            'input-filter'      => InputFilter::class,
            'middleware'        => Middleware::class,
            'module'            => Module::class,
            'repository'        => Repository::class,
            'service'           => Service::class,
            'service-interface' => ServiceInterface::class,
            default             => null,
        };
    }
}
