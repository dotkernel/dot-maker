<?php

declare(strict_types=1);

namespace Dot\Maker\Config;

interface ComponentConfigInterface
{
    public function exists(string $fqmn): bool;

    public function getPathFor(string $namespace): ?string;

    public function getCliConfigFile(): string;

    public function getDefaultStubsDir(): string;

    public function getDefaultStubsDirRealPath(): string;

    public function getAnnotatedEntityInjector(): ?string;

    public function hasAnnotatedEntityInjector(): bool;

    public function getAnnotatedServiceInjector(): ?string;

    public function hasAnnotatedServiceInjector(): bool;

    public function getAnnotatedServiceFactory(): ?string;

    public function hasAnnotatedServiceFactory(): bool;

    public function getAnnotatedRepositoryFactory(): ?string;

    public function hasAnnotatedRepositoryFactory(): bool;

    public function getPublishedStubsDir(): string;

    public function getPublishedStubsDirRealPath(): string;

    public function getSourceDir(): string;
}
