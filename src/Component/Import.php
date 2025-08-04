<?php

declare(strict_types=1);

namespace Dot\Maker\Component;

use Dot\Maker\ContextInterface;

use function sprintf;

class Import
{
    public const DATETIMEIMMUTABLE                                           = 'DateTimeImmutable';
    public const DOCTRINE_ORM_MAPPING                                        = 'Doctrine\\ORM\\Mapping';
    public const DOCTRINE_ORM_MAPPING_DRIVER_ATTRIBUTEDRIVER                 = 'Doctrine\\ORM\\Mapping\\Driver\\AttributeDriver';
    public const DOCTRINE_ORM_QUERYBUILDER                                   = 'Doctrine\\ORM\\QueryBuilder';
    public const DOCTRINE_ORM_TOOLS_PAGINATION_PAGINATOR                     = 'Doctrine\\ORM\\Tools\\Pagination\\Paginator';
    public const DOCTRINE_PERSISTENCE_MAPPING_DRIVER_MAPPINGDRIVER           = 'Doctrine\\Persistence\\Mapping\\Driver\\MappingDriver';
    public const DOT_DEPENDENCYINJECTION_ATTRIBUTE_ENTITY                    = 'Dot\\DependencyInjection\\Attribute\\Entity';
    public const DOT_DEPENDENCYINJECTION_ATTRIBUTE_INJECT                    = 'Dot\\DependencyInjection\\Attribute\\Inject';
    public const DOT_DEPENDENCYINJECTION_FACTORY_ATTRIBUTEDREPOSITORYFACTORY = 'Dot\\DependencyInjection\\Factory\\AttributedRepositoryFactory';
    public const DOT_DEPENDENCYINJECTION_FACTORY_ATTRIBUTEDSERVICEFACTORY    = 'Dot\\DependencyInjection\\Factory\\AttributedServiceFactory';
    public const DOT_FLASHMESSENGER_FLASHMESSENGERINTERFACE                  = 'Dot\\FlashMessenger\\FlashMessengerInterface';
    public const DOT_LOG_LOGGER                                              = 'Dot\\Log\\Logger';
    public const DOT_ROUTER_ROUTECOLLECTORINTERFACE                          = 'Dot\\Router\\RouteCollectorInterface';
    public const FIG_HTTP_MESSAGE_STATUSCODEINTERFACE                        = 'Fig\\Http\\Message\\StatusCodeInterface';
    public const LAMINAS_DIACTOROS_RESPONSE_HTMLRESPONSE                     = 'Laminas\\Diactoros\\Response\\HtmlResponse';
    public const LAMINAS_DIACTOROS_RESPONSE_EMPTYRESPONSE                    = 'Laminas\\Diactoros\\Response\\EmptyResponse';
    public const LAMINAS_FORM_ELEMENT_CHECKBOX                               = 'Laminas\\Form\\Element\\Checkbox';
    public const LAMINAS_FORM_ELEMENT_CSRF                                   = 'Laminas\\Form\\Element\\Csrf';
    public const LAMINAS_FORM_ELEMENT_SUBMIT                                 = 'Laminas\\Form\\Element\\Submit';
    public const LAMINAS_FORM_EXCEPTION_EXCEPTIONINTERFACE                   = 'Laminas\\Form\\Exception\\ExceptionInterface';
    public const LAMINAS_INPUTFILTER_INPUT                                   = 'Laminas\\InputFilter\\Input';
    public const LAMINAS_SESSION_CONTAINER                                   = 'Laminas\\Session\\Container';
    public const LAMINAS_VALIDATOR_INARRAY                                   = 'Laminas\\Validator\\InArray';
    public const LAMINAS_VALIDATOR_NOTEMPTY                                  = 'Laminas\\Validator\\NotEmpty';
    public const MEZZIO_APPLICATION                                          = 'Mezzio\\Application';
    public const MEZZIO_HAL_METADATA_METADATAMAP                             = 'Mezzio\\Hal\\Metadata\\MetadataMap';
    public const MEZZIO_ROUTER_ROUTERINTERFACE                               = 'Mezzio\\Router\\RouterInterface';
    public const MEZZIO_TEMPLATE_TEMPLATERENDERERINTERFACE                   = 'Mezzio\\Template\\TemplateRendererInterface';
    public const OPENAPI_ATTRIBUTES                                          = 'OpenApi\\Attributes';
    public const PSR_CONTAINER_CONTAINERINTERFACE                            = 'Psr\\Container\\ContainerInterface';
    public const PSR_CONTAINER_CONTAINEREXCEPTIONINTERFACE                   = 'Psr\\Container\\ContainerExceptionInterface';
    public const PSR_CONTAINER_NOTFOUNDEXCEPTIONINTERFACE                    = 'Psr\\Container\\NotFoundExceptionInterface';
    public const PSR_HTTP_MESSAGE_RESPONSEINTERFACE                          = 'Psr\\Http\\Message\\ResponseInterface';
    public const PSR_HTTP_MESSAGE_SERVERREQUESTINTERFACE                     = 'Psr\\Http\\Message\\ServerRequestInterface';
    public const PSR_HTTP_SERVER_MIDDLEWAREINTERFACE                         = 'Psr\\Http\\Server\\MiddlewareInterface';
    public const PSR_HTTP_SERVER_REQUESTHANDLERINTERFACE                     = 'Psr\\Http\\Server\\RequestHandlerInterface';
    public const ROOT_APP_ATTRIBUTE_RESOURCE                                 = '%s\\App\\Attribute\\Resource';
    public const ROOT_APP_COLLECTION_RESOURCECOLLECTION                      = '%s\\App\\Collection\\ResourceCollection';
    public const ROOT_APP_CONFIGPROVIDER                                     = '%s\\App\\ConfigProvider';
    public const ROOT_APP_ENTITY_ABSTRACTENTITY                              = '%s\\App\\Entity\\AbstractEntity';
    public const ROOT_APP_ENTITY_TIMESTAMPSTRAIT                             = '%s\\App\\Entity\\TimestampsTrait';
    public const ROOT_APP_EXCEPTION_BADREQUESTEXCEPTION                      = '%s\\App\\Exception\\BadRequestException';
    public const ROOT_APP_EXCEPTION_CONFLICTEXCEPTION                        = '%s\\App\\Exception\\ConflictException';
    public const ROOT_APP_EXCEPTION_NOTFOUNDEXCEPTION                        = '%s\\App\\Exception\\NotFoundException';
    public const ROOT_APP_FACTORY_HANDLERDELEGATORFACTORY                    = '%s\\App\\Factory\\HandlerDelegatorFactory';
    public const ROOT_APP_FORM_ABSTRACTFORM                                  = '%s\App\Form\AbstractForm';
    public const ROOT_APP_INPUTFILTER_ABSTRACTINPUTFILTER                    = '%s\\App\\InputFilter\\AbstractInputFilter';
    public const ROOT_APP_INPUTFILTER_INPUT_CSRFINPUT                        = '%s\\App\\InputFilter\\Input\\CsrfInput';
    public const ROOT_APP_HELPER_PAGINATOR                                   = '%s\\App\\Helper\\Paginator';
    public const ROOT_APP_MESSAGE                                            = '%s\\App\\Message';
    public const ROOT_APP_HANDLER_ABSTRACTHANDLER                            = '%s\\App\\Handler\\AbstractHandler';
    public const ROOT_APP_REPOSITORY_ABSTRACTREPOSITORY                      = '%s\\App\\Repository\\AbstractRepository';
    public const SYMFONY_COMPONENT_CONSOLE_ATTRIBUTE_ASCOMMAND               = 'Symfony\\Component\\Console\\Attribute\\AsCommand';
    public const SYMFONY_COMPONENT_CONSOLE_COMMAND_COMMAND                   = 'Symfony\\Component\\Console\\Command\\Command';
    public const SYMFONY_COMPONENT_CONSOLE_INPUT_INPUTINTERFACE              = 'Symfony\\Component\\Console\\Input\\InputInterface';
    public const SYMFONY_COMPONENT_CONSOLE_OUTPUT_OUTPUTINTERFACE            = 'Symfony\\Component\\Console\\Output\\OutputInterface';
    public const SYMFONY_COMPONENT_CONSOLE_STYLE_SYMFONYSTYLE                = 'Symfony\\Component\\Console\\Style\\SymfonyStyle';
    public const THROWABLE                                                   = 'Throwable';

    public function __construct(
        private ContextInterface $context,
    ) {
    }

    public function getAbstractFormFqcn(): string
    {
        return sprintf(self::ROOT_APP_FORM_ABSTRACTFORM, $this->context->getRootNamespace());
    }

    public function getAbstractInputFilterFqcn(): string
    {
        $format = self::ROOT_APP_INPUTFILTER_ABSTRACTINPUTFILTER;

        if ($this->context->hasCore()) {
            return sprintf($format, ContextInterface::NAMESPACE_CORE);
        }

        return sprintf($format, $this->context->getRootNamespace());
    }

    public function getAbstractHandlerFqcn(): string
    {
        return sprintf(self::ROOT_APP_HANDLER_ABSTRACTHANDLER, $this->context->getRootNamespace());
    }

    public function getAbstractRepositoryFqcn(): string
    {
        $format = self::ROOT_APP_REPOSITORY_ABSTRACTREPOSITORY;

        if ($this->context->hasCore()) {
            return sprintf($format, ContextInterface::NAMESPACE_CORE);
        }

        return sprintf($format, $this->context->getRootNamespace());
    }

    public function getAppHelperPaginatorFqcn(): string
    {
        $format = self::ROOT_APP_HELPER_PAGINATOR;

        if ($this->context->hasCore()) {
            return sprintf($format, ContextInterface::NAMESPACE_CORE);
        }

        return sprintf($format, $this->context->getRootNamespace());
    }

    public function getAppMessageFqcn(): string
    {
        $format = self::ROOT_APP_MESSAGE;

        if ($this->context->hasCore()) {
            return sprintf($format, ContextInterface::NAMESPACE_CORE);
        }

        return sprintf($format, $this->context->getRootNamespace());
    }

    public function getBadRequestExceptionFqcn(): string
    {
        return sprintf(self::ROOT_APP_EXCEPTION_BADREQUESTEXCEPTION, $this->context->getRootNamespace());
    }

    public function getConfigProviderFqcn(bool $core = false): string
    {
        if ($core && $this->context->hasCore()) {
            $rootNamespace = ContextInterface::NAMESPACE_CORE;
        } else {
            $rootNamespace = $this->context->getRootNamespace();
        }

        return sprintf(self::ROOT_APP_CONFIGPROVIDER, $rootNamespace);
    }

    public function getConflictExceptionFqcn(): string
    {
        return sprintf(self::ROOT_APP_EXCEPTION_CONFLICTEXCEPTION, $this->context->getRootNamespace());
    }

    public function getCsrfInputFqcn(): string
    {
        return sprintf(self::ROOT_APP_INPUTFILTER_INPUT_CSRFINPUT, $this->context->getRootNamespace());
    }

    public function getHandlerDelegatorFactoryFqcn(): string
    {
        return sprintf(self::ROOT_APP_FACTORY_HANDLERDELEGATORFACTORY, $this->context->getRootNamespace());
    }

    public function getNotFoundExceptionFqcn(): string
    {
        return sprintf(self::ROOT_APP_EXCEPTION_NOTFOUNDEXCEPTION, $this->context->getRootNamespace());
    }

    public function getResourceAttributeFqcn(): string
    {
        return sprintf(self::ROOT_APP_ATTRIBUTE_RESOURCE, $this->context->getRootNamespace());
    }

    public function getResourceCollectionFqcn(): string
    {
        return sprintf(self::ROOT_APP_COLLECTION_RESOURCECOLLECTION, $this->context->getRootNamespace());
    }
}
