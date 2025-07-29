<?php

declare(strict_types=1);

namespace Dot\Maker\Component;

use function array_map;
use function array_merge;
use function count;
use function implode;
use function sort;
use function sprintf;

use const PHP_EOL;

class Import
{
    // phpcs:disable Generic.Files.LineLength.TooLong
    public const DATETIMEIMMUTABLE                                = 'DateTimeImmutable';
    public const DOCTRINE_ORM_MAPPING                             = 'Doctrine\\ORM\\Mapping';
    public const DOCTRINE_ORM_QUERYBUILDER                        = 'Doctrine\\ORM\\QueryBuilder';
    public const DOCTRINE_ORM_TOOLS_PAGINATION_PAGINATOR          = 'Doctrine\\ORM\\Tools\\Pagination\\Paginator';
    public const DOT_DEPENDENCYINJECTION_ATTRIBUTE_ENTITY         = 'Dot\\DependencyInjection\\Attribute\\Entity';
    public const DOT_DEPENDENCYINJECTION_ATTRIBUTE_INJECT         = 'Dot\\DependencyInjection\\Attribute\\Inject';
    public const DOT_FLASHMESSENGER_FLASHMESSENGERINTERFACE       = 'Dot\\FlashMessenger\\FlashMessengerInterface';
    public const DOT_LOG_LOGGER                                   = 'Dot\\Log\\Logger';
    public const DOT_ROUTER_ROUTECOLLECTORINTERFACE               = 'Dot\\Router\\RouteCollectorInterface';
    public const FIG_HTTP_MESSAGE_STATUSCODEINTERFACE             = 'Fig\\Http\\Message\\StatusCodeInterface';
    public const LAMINAS_DIACTOROS_RESPONSE_HTMLRESPONSE          = 'Laminas\\Diactoros\\Response\\HtmlResponse';
    public const LAMINAS_DIACTOROS_RESPONSE_EMPTYRESPONSE         = 'Laminas\\Diactoros\\Response\\EmptyResponse';
    public const MEZZIO_APPLICATION                               = 'Mezzio\\Application';
    public const MEZZIO_ROUTER_ROUTERINTERFACE                    = 'Mezzio\\Router\\RouterInterface';
    public const MEZZIO_TEMPLATE_TEMPLATERENDERERINTERFACE        = 'Mezzio\\Template\\TemplateRendererInterface';
    public const OPENAPI_ATTRIBUTES                               = 'OpenApi\\Attributes';
    public const PSR_CONTAINER_CONTAINERINTERFACE                 = 'Psr\\Container\\ContainerInterface';
    public const PSR_CONTAINER_CONTAINEREXCEPTIONINTERFACE        = 'Psr\\Container\\ContainerExceptionInterface';
    public const PSR_CONTAINER_NOTFOUNDEXCEPTIONINTERFACE         = 'Psr\\Container\\NotFoundExceptionInterface';
    public const PSR_HTTP_MESSAGE_RESPONSEINTERFACE               = 'Psr\\Http\\Message\\ResponseInterface';
    public const PSR_HTTP_MESSAGE_SERVERREQUESTINTERFACE          = 'Psr\\Http\\Message\\ServerRequestInterface';
    public const PSR_HTTP_SERVER_MIDDLEWAREINTERFACE              = 'Psr\\Http\\Server\\MiddlewareInterface';
    public const PSR_HTTP_SERVER_REQUESTHANDLERINTERFACE          = 'Psr\\Http\\Server\\RequestHandlerInterface';
    public const ROOT_APP_ATTRIBUTE_RESOURCE                      = '%s\\App\\Attribute\\Resource';
    public const ROOT_APP_COLLECTION_RESOURCECOLLECTION           = '%s\\App\\Collection\\ResourceCollection';
    public const ROOT_APP_ENTITY_ABSTRACTENTITY                   = '%s\\App\\Entity\\AbstractEntity';
    public const ROOT_APP_ENTITY_TIMESTAMPSTRAIT                  = '%s\\App\\Entity\\TimestampsTrait';
    public const ROOT_APP_EXCEPTION_BADREQUESTEXCEPTION           = '%s\\App\\Exception\\BadRequestException';
    public const ROOT_APP_EXCEPTION_CONFLICTEXCEPTION             = '%s\\App\\Exception\\ConflictException';
    public const ROOT_APP_EXCEPTION_NOTFOUNDEXCEPTION             = '%s\\App\\Exception\\NotFoundException';
    public const ROOT_APP_HELPER_PAGINATOR                        = '%s\\App\\Helper\\Paginator';
    public const ROOT_APP_MESSAGE                                 = '%s\\App\\Message';
    public const ROOT_APP_HANDLER_ABSTRACTHANDLER                 = '%s\\App\\Handler\\AbstractHandler';
    public const ROOT_APP_INPUTFILTER_ABSTRACTINPUTFILTER         = '%s\\App\\InputFilter\\AbstractInputFilter';
    public const ROOT_APP_REPOSITORY_ABSTRACTREPOSITORY           = '%s\\App\\Repository\\AbstractRepository';
    public const SYMFONY_COMPONENT_CONSOLE_ATTRIBUTE_ASCOMMAND    = 'Symfony\\Component\\Console\\Attribute\\AsCommand';
    public const SYMFONY_COMPONENT_CONSOLE_COMMAND_COMMAND        = 'Symfony\\Component\\Console\\Command\\Command';
    public const SYMFONY_COMPONENT_CONSOLE_INPUT_INPUTINTERFACE   = 'Symfony\\Component\\Console\\Input\\InputInterface';
    public const SYMFONY_COMPONENT_CONSOLE_OUTPUT_OUTPUTINTERFACE = 'Symfony\\Component\\Console\\Output\\OutputInterface';
    public const SYMFONY_COMPONENT_CONSOLE_STYLE_SYMFONYSTYLE     = 'Symfony\\Component\\Console\\Style\\SymfonyStyle';
    public const THROWABLE                                        = 'Throwable';
    // phpcs:enable Generic.Files.LineLength.TooLong

    private array $classUses    = [];
    private array $functionUses = [];
    private array $constantUses = [];

    public function addClassUse(string $fqcn, ?string $alias = null): self
    {
        $this->classUses[$fqcn] = [
            'fqcn'  => $fqcn,
            'alias' => $alias,
        ];

        return $this;
    }

    public function addFunctionUse(string $function): self
    {
        $this->functionUses[$function] = $function;

        return $this;
    }

    public function addConstantUse(string $constant): self
    {
        $this->constantUses[$constant] = $constant;

        return $this;
    }

    public function render(): string
    {
        $classUses = $this->renderClassUses();

        $functionUses = array_map(
            fn (string $functionUse) => sprintf('use function %s;', $functionUse),
            $this->functionUses
        );
        sort($functionUses);

        $constantUses = array_map(
            fn (string $constantUse) => sprintf('use const %s;', $constantUse),
            $this->constantUses
        );
        sort($constantUses);

        $uses = [];
        if (count($classUses) > 0) {
            $uses = array_merge($uses, $classUses);
        }
        if (count($functionUses) > 0) {
            $uses = array_merge($uses, ['']);
            $uses = array_merge($uses, $functionUses);
        }
        if (count($constantUses) > 0) {
            $uses = array_merge($uses, ['']);
            $uses = array_merge($uses, $constantUses);
        }

        if (count($uses) === 0) {
            return '';
        }

        if ($uses[0] === '') {
            unset($uses[0]);
        }

        return PHP_EOL . implode(PHP_EOL, $uses) . PHP_EOL;
    }

    private function renderClassUses(): array
    {
        $classUses = [];

        foreach ($this->classUses as $use) {
            if ($use['alias'] !== null) {
                $classUses[] = sprintf('use %s as %s;', $use['fqcn'], $use['alias']);
            } else {
                $classUses[] = sprintf('use %s;', $use['fqcn']);
            }
        }
        sort($classUses);

        return $classUses;
    }

    public static function getAbstractHandlerFqcn(string $rootNamespace): string
    {
        return sprintf(self::ROOT_APP_HANDLER_ABSTRACTHANDLER, $rootNamespace);
    }

    public static function getBadRequestExceptionFqcn(string $rootNamespace): string
    {
        return sprintf(self::ROOT_APP_EXCEPTION_BADREQUESTEXCEPTION, $rootNamespace);
    }

    public static function getConflictExceptionFqcn(string $rootNamespace): string
    {
        return sprintf(self::ROOT_APP_EXCEPTION_CONFLICTEXCEPTION, $rootNamespace);
    }

    public static function getNotFoundExceptionFqcn(string $rootNamespace): string
    {
        return sprintf(self::ROOT_APP_EXCEPTION_NOTFOUNDEXCEPTION, $rootNamespace);
    }

    public static function getResourceAttributeFqcn(string $rootNamespace): string
    {
        return sprintf(self::ROOT_APP_ATTRIBUTE_RESOURCE, $rootNamespace);
    }

    public static function getResourceCollectionFqcn(string $rootNamespace): string
    {
        return sprintf(self::ROOT_APP_COLLECTION_RESOURCECOLLECTION, $rootNamespace);
    }
}
