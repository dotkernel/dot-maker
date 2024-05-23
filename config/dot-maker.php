<?php

declare(strict_types=1);

return [
    'annotated_entity_injector' => '\Dot\AnnotatedServices\Annotation\Entity',
    'annotated_service_injector' => '\Dot\AnnotatedServices\Annotation\Inject',
    'annotated_service_factory' => '\Dot\AnnotatedServices\Factory\AnnotatedServiceFactory',
    'annotated_repository_factory' => '\Dot\AnnotatedServices\Factory\AnnotatedRepositoryFactory',
    'autoloader_file' => realpath(getcwd() . '/vendor/composer/autoload_psr4.php'),
    'cli_config_file' => realpath(getcwd() . '/config/autoload/cli.global.php'),
    'default_stubs_dir' => realpath(dirname(__DIR__) . '/src/Resources/stubs'),
    'published_stubs_dir' => getcwd() . '/src/App/stubs',
    'source_dir' => realpath(getcwd() . '/src'),
];
