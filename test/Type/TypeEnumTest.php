<?php

declare(strict_types=1);

namespace DotTest\Maker\Type;

use Dot\Maker\Type\Collection;
use Dot\Maker\Type\Command;
use Dot\Maker\Type\Entity;
use Dot\Maker\Type\Form;
use Dot\Maker\Type\Handler;
use Dot\Maker\Type\Help;
use Dot\Maker\Type\Input;
use Dot\Maker\Type\InputFilter;
use Dot\Maker\Type\Middleware;
use Dot\Maker\Type\Module;
use Dot\Maker\Type\Repository;
use Dot\Maker\Type\Service;
use Dot\Maker\Type\ServiceInterface;
use Dot\Maker\Type\TypeEnum;
use PHPUnit\Framework\TestCase;

class TypeEnumTest extends TestCase
{
    public function testWillMatchCorrectly(): void
    {
        $this->assertSame(Help::class, TypeEnum::getClass(''));
        $this->assertSame(Collection::class, TypeEnum::getClass('collection'));
        $this->assertSame(Command::class, TypeEnum::getClass('command'));
        $this->assertSame(Entity::class, TypeEnum::getClass('entity'));
        $this->assertSame(Form::class, TypeEnum::getClass('form'));
        $this->assertSame(Handler::class, TypeEnum::getClass('handler'));
        $this->assertSame(Input::class, TypeEnum::getClass('input'));
        $this->assertSame(InputFilter::class, TypeEnum::getClass('input-filter'));
        $this->assertSame(Middleware::class, TypeEnum::getClass('middleware'));
        $this->assertSame(Module::class, TypeEnum::getClass('module'));
        $this->assertSame(Repository::class, TypeEnum::getClass('repository'));
        $this->assertSame(Service::class, TypeEnum::getClass('service'));
        $this->assertSame(ServiceInterface::class, TypeEnum::getClass('service-interface'));
        $this->assertNull(TypeEnum::getClass('test'));
    }
}
