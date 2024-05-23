<?php

declare(strict_types=1);

namespace Dot\Maker\Service;

use Dot\Maker\Config\ComponentConfigInterface;
use Dot\Maker\Exception\DuplicateStubException;

interface StubServiceInterface
{
    public function getComponentConfig(): ComponentConfigInterface;

    /**
     * @throws DuplicateStubException
     */
    public function publishStub(string $name, bool $overwrite = false): void;

    /**
     * @throws DuplicateStubException
     */
    public function publishStubs(bool $overwrite = false): void;
}
