<?php

declare(strict_types=1);

namespace Dot\Maker\Config;

use Exception;
use Webmozart\Assert\Assert;

class ComponentConfig implements ComponentConfigInterface
{
    protected array $autoloader = [];
    protected string $autoloaderFile = '';
    protected string $cliConfigFile = '';
    protected string $defaultStubsDir = '';
    protected ?string $annotatedEntityInjector = null;
    protected ?string $annotatedServiceInjector = null;
    protected ?string $annotatedServiceFactory = null;
    protected ?string $annotatedRepositoryFactory = null;
    protected string $publishedStubsDir = '';
    protected string $sourceDir = '';

    /**
     * @throws Exception
     */
    public function __construct(array $config)
    {
        Assert::keyExists($config, 'autoloader_file');
        Assert::notEmpty($config['autoloader_file']);
        Assert::string($config['autoloader_file']);
        $this->setAutoloaderFile($config['autoloader_file']);

        Assert::keyExists($config, 'cli_config_file');
        Assert::notEmpty($config['cli_config_file']);
        Assert::string($config['cli_config_file']);
        $this->setCliConfigFile($config['cli_config_file']);

        Assert::keyExists($config, 'default_stubs_dir');
        Assert::notEmpty($config['default_stubs_dir']);
        Assert::string($config['default_stubs_dir']);
        $this->setDefaultStubsDir($config['default_stubs_dir']);

        Assert::keyExists($config, 'annotated_entity_injector');
        $this->setAnnotatedEntityInjector($config['annotated_entity_injector']);

        Assert::keyExists($config, 'annotated_service_injector');
        $this->setAnnotatedServiceInjector($config['annotated_service_injector']);

        Assert::keyExists($config, 'annotated_service_factory');
        $this->setAnnotatedServiceFactory($config['annotated_service_factory']);

        Assert::keyExists($config, 'annotated_repository_factory');
        $this->setAnnotatedRepositoryFactory($config['annotated_repository_factory']);

        Assert::keyExists($config, 'published_stubs_dir');
        Assert::string($config['published_stubs_dir']);
        $this->setPublishedStubsDir($config['published_stubs_dir']);

        Assert::keyExists($config, 'source_dir');
        Assert::notEmpty($config['source_dir']);
        Assert::string($config['source_dir']);
        $this->setSourceDir($config['source_dir']);

        $this->initAutoloader($this->getAutoloaderFile());
    }

    public function exists(string $fqmn): bool
    {
        return array_key_exists($fqmn, $this->autoloader);
    }

    public function getPathFor(string $namespace): ?string
    {
        if (!array_key_exists($namespace, $this->autoloader)) {
            return null;
        }

        return current($this->autoloader[$namespace]);
    }

    protected function getAutoloaderFile(): string
    {
        return $this->autoloaderFile;
    }

    protected function setAutoloaderFile(string $autoloaderFile): self
    {
        $this->autoloaderFile = $autoloaderFile;

        return $this;
    }

    public function getCliConfigFile(): string
    {
        return $this->cliConfigFile;
    }

    protected function setCliConfigFile(string $cliConfigFile): self
    {
        $this->cliConfigFile = $cliConfigFile;

        return $this;
    }

    public function getDefaultStubsDir(): string
    {
        return $this->defaultStubsDir;
    }

    public function getDefaultStubsDirRealPath(): string
    {
        return (string) realpath($this->defaultStubsDir);
    }

    protected function setDefaultStubsDir(string $defaultStubsDir): self
    {
        $this->defaultStubsDir = $defaultStubsDir;

        return $this;
    }

    public function getAnnotatedEntityInjector(): ?string
    {
        return $this->annotatedEntityInjector;
    }

    public function hasAnnotatedEntityInjector(): bool
    {
        return !empty($this->annotatedEntityInjector);
    }

    protected function setAnnotatedEntityInjector(?string $annotatedEntityInjector): self
    {
        $this->annotatedEntityInjector = $annotatedEntityInjector;

        return $this;
    }

    public function getAnnotatedServiceInjector(): ?string
    {
        return $this->annotatedServiceInjector;
    }

    public function hasAnnotatedServiceInjector(): bool
    {
        return !empty($this->annotatedServiceInjector);
    }

    protected function setAnnotatedServiceInjector(?string $annotatedServiceInjector): self
    {
        $this->annotatedServiceInjector = $annotatedServiceInjector;

        return $this;
    }

    public function getAnnotatedServiceFactory(): ?string
    {
        return $this->annotatedServiceFactory;
    }

    public function hasAnnotatedServiceFactory(): bool
    {
        return !empty($this->annotatedServiceFactory);
    }

    protected function setAnnotatedServiceFactory(?string $annotatedServiceFactory): self
    {
        $this->annotatedServiceFactory = $annotatedServiceFactory;

        return $this;
    }

    public function getAnnotatedRepositoryFactory(): ?string
    {
        return $this->annotatedRepositoryFactory;
    }

    public function hasAnnotatedRepositoryFactory(): bool
    {
        return !empty($this->annotatedRepositoryFactory);
    }

    protected function setAnnotatedRepositoryFactory(?string $annotatedRepositoryFactory): self
    {
        $this->annotatedRepositoryFactory = $annotatedRepositoryFactory;

        return $this;
    }

    public function getPublishedStubsDir(): string
    {
        return $this->publishedStubsDir;
    }

    public function getPublishedStubsDirRealPath(): string
    {
        return (string) realpath($this->publishedStubsDir);
    }

    protected function setPublishedStubsDir(string $publishedStubsDir): self
    {
        $this->publishedStubsDir = $publishedStubsDir;

        return $this;
    }

    public function getSourceDir(): string
    {
        return $this->sourceDir;
    }

    protected function setSourceDir(string $sourceDir): self
    {
        $this->sourceDir = $sourceDir;

        return $this;
    }

    /**
     * @throws Exception
     */
    protected function initAutoloader(string $path): void
    {
        if (!file_exists($path)) {
            throw new Exception(
                sprintf('Autoloader file (%s) not found.', $this->getAutoloaderFile())
            );
        }

        $this->autoloader = require $path;
    }
}
