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
    public const DOCTRINE_ORM_MAPPING                             = 'Doctrine\\ORM\\Mapping';
    public const DOT_DEPENDENCYINJECTION_ATTRIBUTE_ENTITY         = 'Dot\\DependencyInjection\\Attribute\\Entity';
    public const DOT_DEPENDENCYINJECTION_ATTRIBUTE_INJECT         = 'Dot\\DependencyInjection\\Attribute\\Inject';
    public const PSR_HTTP_MESSAGE_RESPONSEINTERFACE               = 'Psr\\Http\\Message\\ResponseInterface';
    public const PSR_HTTP_MESSAGE_SERVERREQUESTINTERFACE          = 'Psr\\Http\\Message\\ServerRequestInterface';
    public const PSR_HTTP_SERVER_MIDDLEWAREINTERFACE              = 'Psr\\Http\\Server\\MiddlewareInterface';
    public const PSR_HTTP_SERVER_REQUESTHANDLERINTERFACE          = 'Psr\\Http\\Server\\RequestHandlerInterface';
    public const ROOT_APP_ATTRIBUTE_RESOURCE                      = '%s\\App\\Attribute\\Resource';
    public const ROOT_APP_COLLECTION_RESOURCECOLLECTION           = '%s\\App\\Collection\\ResourceCollection';
    public const ROOT_APP_ENTITY_ABSTRACTENTITY                   = '%s\\App\\Entity\\AbstractEntity';
    public const ROOT_APP_ENTITY_TIMESTAMPSTRAIT                  = '%s\\App\\Entity\\TimestampsTrait';
    public const ROOT_APP_HANDLER_ABSTRACTHANDLER                 = '%s\\App\\Handler\\AbstractHandler';
    public const ROOT_APP_INPUTFILTER_ABSTRACTINPUTFILTER         = '%s\\App\\InputFilter\\AbstractInputFilter';
    public const ROOT_APP_REPOSITORY_ABSTRACTREPOSITORY           = '%s\\App\\Repository\\AbstractRepository';
    public const SYMFONY_COMPONENT_CONSOLE_ATTRIBUTE_ASCOMMAND    = 'Symfony\\Component\\Console\\Attribute\\AsCommand';
    public const SYMFONY_COMPONENT_CONSOLE_COMMAND_COMMAND        = 'Symfony\\Component\\Console\\Command\\Command';
    public const SYMFONY_COMPONENT_CONSOLE_INPUT_INPUTINTERFACE   = 'Symfony\\Component\\Console\\Input\\InputInterface';
    public const SYMFONY_COMPONENT_CONSOLE_OUTPUT_OUTPUTINTERFACE = 'Symfony\\Component\\Console\\Output\\OutputInterface';
    public const SYMFONY_COMPONENT_CONSOLE_STYLE_SYMFONYSTYLE     = 'Symfony\\Component\\Console\\Style\\SymfonyStyle';
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

        $functionUses = array_map(fn (string $functionUse) => sprintf('use %s;', $functionUse), $this->functionUses);
        sort($functionUses);

        $constantUses = array_map(fn (string $functionUse) => sprintf('use %s;', $functionUse), $this->constantUses);
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
}
