<?php

declare(strict_types=1);

namespace Dot\Maker\Service;

use Dot\Maker\Component\CommandComponent;
use Dot\Maker\Component\EntityComponent;
use Dot\Maker\Component\FactoryComponent;
use Dot\Maker\Component\HandlerComponent;
use Dot\Maker\Component\InterfaceComponent;
use Dot\Maker\Component\MiddlewareComponent;
use Dot\Maker\Component\RepositoryComponent;
use Dot\Maker\Component\ServiceComponent;
use Dot\Maker\Config\ComponentConfigInterface;
use Dot\Maker\Exception\DuplicateStubException;
use Dot\Maker\Stub;

class StubService implements StubServiceInterface
{
    protected ComponentConfigInterface $componentConfig;

    public function __construct(ComponentConfigInterface $componentConfig)
    {
        $this->componentConfig = $componentConfig;
    }

    public function getComponentConfig(): ComponentConfigInterface
    {
        return $this->componentConfig;
    }

    /**
     * @throws DuplicateStubException
     */
    public function publishStub(string $name, bool $overwrite = false): void
    {
        $stub = (new Stub($this->componentConfig))->init($name);
        if (file_exists($stub->findPublishedPath()) && $overwrite === false) {
            throw new DuplicateStubException(
                sprintf('Stub %s already exists.', $name)
            );
        }

        file_put_contents($stub->findPublishedPath(), $stub->readPath());
    }

    /**
     * @throws DuplicateStubException
     */
    public function publishStubs(bool $overwrite = false): void
    {
        if (!file_exists($this->componentConfig->getPublishedStubsDir())) {
            mkdir($this->componentConfig->getPublishedStubsDir(), 0755, true);
        }

        $stubNames = [
            CommandComponent::STUB_NAME,
            EntityComponent::STUB_NAME,
            FactoryComponent::STUB_NAME,
            HandlerComponent::STUB_NAME,
            InterfaceComponent::STUB_NAME,
            MiddlewareComponent::STUB_NAME,
            RepositoryComponent::STUB_NAME,
            ServiceComponent::STUB_NAME,
        ];

        foreach ($stubNames as $stubName) {
            $this->publishStub($stubName, $overwrite);
        }
    }
}
