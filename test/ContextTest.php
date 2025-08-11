<?php

declare(strict_types=1);

namespace DotTest\Maker;

use Dot\Maker\Context;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

use function sprintf;

class ContextTest extends TestCase
{
    public function testWillNotInstantiateWithoutComposerJson(): void
    {
        $projectPath = '/invalid/path';
        $this->expectExceptionMessage(sprintf('%s/composer.json: not found', $projectPath));

        new Context($projectPath);
    }

    public function testWillNotInstantiateWithInvalidComposerJson(): void
    {
        $fileSystem = vfsStream::setup('root', 0644, [
            'composer.json' => '',
        ]);
        $this->expectExceptionMessage(sprintf('%s/composer.json: invalid JSON', $fileSystem->url()));

        new Context($fileSystem->url());
    }

    public function testWillNotInstantiateWithoutComposerJsonAutoload(): void
    {
        $fileSystem = vfsStream::setup('root', 0644, [
            'composer.json' => '{}',
        ]);
        $this->expectExceptionMessage(sprintf('%s/composer.json: key "autoload" not found', $fileSystem->url()));

        new Context($fileSystem->url());
    }

    public function testWillNotInstantiateWithoutComposerJsonAutoloadPSR4(): void
    {
        $fileSystem = vfsStream::setup('root', 0644, [
            'composer.json' => '{"autoload":[]}',
        ]);
        $this->expectExceptionMessage(sprintf('%s/composer.json: key "autoload"."psr-4" not found', $fileSystem->url()));

        new Context($fileSystem->url());
    }

    public function testWillDetectCoreArchitectureWhenUsed(): void
    {
        $fileSystem = vfsStream::setup('root', 0644, [
            'composer.json' => '{
                "autoload": {
                    "psr-4": {
                        "Api\\\\App\\\\": "src/App/src/",
                        "Core\\\\App\\\\": "src/Core/src/App/src/"
                    }
                }
            }',
        ]);

        $context = new Context($fileSystem->url());
        $this->assertTrue($context->hasCore());
    }

    public function testWillDetectCoreArchitectureWhenNotUsed(): void
    {
        $fileSystem = vfsStream::setup('root', 0644, [
            'composer.json' => '{
                "autoload": {
                    "psr-4": {
                        "Api\\\\App\\\\": "src/App/src/",
                        "Other\\\\App\\\\": "src/Other/src/"
                    }
                }
            }',
        ]);

        $context = new Context($fileSystem->url());
        $this->assertFalse($context->hasCore());
    }

    public function testWillDetectProjectTypeAdmin(): void
    {
        $fileSystem = vfsStream::setup('root', 0644, [
            'composer.json' => '{
                "autoload": {
                    "psr-4": {
                        "Admin\\\\App\\\\": "src/Admin/src/",
                        "Other\\\\App\\\\": "src/Other/src/"
                    }
                }
            }',
        ]);

        $context = new Context($fileSystem->url());
        $this->assertTrue($context->isAdmin());
        $this->assertFalse($context->isApi());
        $this->assertFalse($context->isFrontend());
        $this->assertFalse($context->isLight());
        $this->assertFalse($context->isQueue());
        $this->assertSame(Context::NAMESPACE_ADMIN, $context->getProjectType());
    }

    public function testWillDetectProjectTypeApi(): void
    {
        $fileSystem = vfsStream::setup('root', 0644, [
            'composer.json' => '{
                "autoload": {
                    "psr-4": {
                        "Api\\\\App\\\\": "src/App/src/",
                        "Other\\\\App\\\\": "src/Other/src/"
                    }
                }
            }',
        ]);

        $context = new Context($fileSystem->url());
        $this->assertFalse($context->isAdmin());
        $this->assertTrue($context->isApi());
        $this->assertFalse($context->isFrontend());
        $this->assertFalse($context->isLight());
        $this->assertFalse($context->isQueue());
        $this->assertSame(Context::NAMESPACE_API, $context->getProjectType());
    }

    public function testWillDetectProjectTypeFrontend(): void
    {
        $fileSystem = vfsStream::setup('root', 0644, [
            'composer.json' => '{
                "autoload": {
                    "psr-4": {
                        "Frontend\\\\App\\\\": "src/Frontend/src/",
                        "Other\\\\App\\\\": "src/Other/src/"
                    }
                }
            }',
        ]);

        $context = new Context($fileSystem->url());
        $this->assertFalse($context->isAdmin());
        $this->assertFalse($context->isApi());
        $this->assertTrue($context->isFrontend());
        $this->assertFalse($context->isLight());
        $this->assertFalse($context->isQueue());
        $this->assertSame(Context::NAMESPACE_FRONTEND, $context->getProjectType());
    }

    public function testWillDetectProjectTypeLight(): void
    {
        $fileSystem = vfsStream::setup('root', 0644, [
            'composer.json' => '{
                "autoload": {
                    "psr-4": {
                        "Light\\\\App\\\\": "src/Light/src/",
                        "Other\\\\App\\\\": "src/Other/src/"
                    }
                }
            }',
        ]);

        $context = new Context($fileSystem->url());
        $this->assertFalse($context->isAdmin());
        $this->assertFalse($context->isApi());
        $this->assertFalse($context->isFrontend());
        $this->assertTrue($context->isLight());
        $this->assertFalse($context->isQueue());
        $this->assertSame(Context::NAMESPACE_LIGHT, $context->getProjectType());
    }

    public function testWillDetectProjectTypeQueue(): void
    {
        $fileSystem = vfsStream::setup('root', 0644, [
            'composer.json' => '{
                "autoload": {
                    "psr-4": {
                        "Queue\\\\App\\\\": "src/Queue/src/",
                        "Other\\\\App\\\\": "src/Other/src/"
                    }
                }
            }',
        ]);

        $context = new Context($fileSystem->url());
        $this->assertFalse($context->isAdmin());
        $this->assertFalse($context->isApi());
        $this->assertFalse($context->isFrontend());
        $this->assertFalse($context->isLight());
        $this->assertTrue($context->isQueue());
        $this->assertSame(Context::NAMESPACE_QUEUE, $context->getProjectType());
    }
}
