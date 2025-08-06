<?php

declare(strict_types=1);

namespace Dot\Maker\Type;

use Dot\Maker\Component;
use Dot\Maker\Component\ClassFile;
use Dot\Maker\Component\Import;
use Dot\Maker\Component\Inject;
use Dot\Maker\Component\Method;
use Dot\Maker\Component\Method\Constructor;
use Dot\Maker\Component\Parameter;
use Dot\Maker\Component\Property;
use Dot\Maker\Exception\BadRequestException;
use Dot\Maker\Exception\DuplicateFileException;
use Dot\Maker\Exception\RuntimeException;
use Dot\Maker\FileSystem\File;
use Dot\Maker\IO\Input;
use Dot\Maker\IO\Output;
use Dot\Maker\Message;
use Dot\Maker\VisibilityEnum;
use Throwable;

use function sprintf;
use function str_replace;
use function ucfirst;

class Command extends AbstractType implements FileInterface
{
    public function __invoke(): void
    {
        while (true) {
            $name = ucfirst(Input::prompt('Enter new Command name: '));
            if ($name === '') {
                return;
            }

            try {
                $this->create($name);
            } catch (Throwable $exception) {
                Output::error($exception->getMessage());
            }
        }
    }

    /**
     * @throws BadRequestException
     * @throws DuplicateFileException
     * @throws RuntimeException
     */
    public function create(string $name): File
    {
        if (! $this->isValid($name)) {
            throw new BadRequestException(sprintf('Invalid Command name: "%s"', $name));
        }

        $command = $this->fileSystem->command($name);
        if ($command->exists()) {
            throw new DuplicateFileException(sprintf(
                'Command "%s" already exists at %s',
                $command->getComponent()->getClassName(),
                $command->getPath()
            ));
        }

        $content = $this->render(
            $command->getComponent(),
            $this->fileSystem->serviceInterface($this->fileSystem->getModuleName()),
        );

        $command->create($content);

        $this->addMessage(Message::addCommandToConfig($command->getComponent()->getFqcn()));

        Output::success(sprintf('Created Command "%s"', $command->getPath()));

        return $command;
    }

    public function render(Component $command, File $serviceInterface): string
    {
        $defaultName = $this->getDefaultName($command);

        $class = (new ClassFile($command->getNamespace(), $command->getClassName()))
            ->setExtends('Command')
            ->useClass(Import::SYMFONY_COMPONENT_CONSOLE_ATTRIBUTE_ASCOMMAND)
            ->useClass(Import::SYMFONY_COMPONENT_CONSOLE_COMMAND_COMMAND)
            ->useClass(Import::SYMFONY_COMPONENT_CONSOLE_INPUT_INPUTINTERFACE)
            ->useClass(Import::SYMFONY_COMPONENT_CONSOLE_OUTPUT_OUTPUTINTERFACE)
            ->useClass(Import::SYMFONY_COMPONENT_CONSOLE_STYLE_SYMFONYSTYLE)
            ->addInject(
                (new Inject('AsCommand'))
                    ->addArgument(self::wrap($defaultName), 'name')
                    ->addArgument('\'Command description.\'', 'description')
            )
            ->addProperty(
                (new Property('defaultName', ''))
                    ->setDefault(self::wrap($defaultName))
                    ->setComment('/** @var string $defaultName */')
                    ->setStatic(true)
            );

        $constructor = (new Constructor())
            ->setBody('        parent::__construct(self::$defaultName);');
        if ($serviceInterface->exists()) {
            $class
                ->useClass(Import::DOT_DEPENDENCYINJECTION_ATTRIBUTE_INJECT)
                ->useClass($serviceInterface->getComponent()->getFqcn());

            $constructor
                ->addInject(
                    (new Inject())->addArgument($serviceInterface->getComponent()->getClassString())
                )
                ->addPromotedPropertyFromComponent($serviceInterface->getComponent());
        }
        $class->addMethod($constructor);

        $configure = (new Method('configure'))
            ->setVisibility(VisibilityEnum::Protected)
            ->setBody(<<<BODY
        \$this
            ->setName(self::\$defaultName)
            ->setDescription('Command description.');
BODY);
        $class->addMethod($configure);

        $execute = (new Method('execute'))
            ->setVisibility(VisibilityEnum::Protected)
            ->setReturnType('int')
            ->addParameter(
                new Parameter('input', 'InputInterface')
            )
            ->addParameter(
                new Parameter('output', 'OutputInterface')
            )
            ->setBody(<<<BODY
        \$io = new SymfonyStyle(\$input, \$output);
        \$io->info('{$command->getClassName()} default output');

        return Command::SUCCESS;
BODY);
        $class->addMethod($execute);

        return $class->render();
    }

    public function getDefaultName(Component $command): string
    {
        $defaultName = sprintf('%s:command', $command->toKebabCase());

        return str_replace('-command', '', $defaultName);
    }
}
