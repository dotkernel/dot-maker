<?php

declare(strict_types=1);

namespace Dot\Maker\Type;

use Dot\Maker\Component;
use Dot\Maker\Component\Import;
use Dot\Maker\Component\Inject;
use Dot\Maker\Component\Method;
use Dot\Maker\Component\Method\Constructor;
use Dot\Maker\Component\Parameter;
use Dot\Maker\FileSystem\File;
use Dot\Maker\IO\Input;
use Dot\Maker\IO\Output;
use Dot\Maker\VisibilityEnum;

use function array_shift;
use function count;
use function implode;
use function preg_replace;
use function preg_split;
use function sprintf;
use function strtolower;
use function ucfirst;

use const PHP_EOL;

class Command extends AbstractType implements FileInterface
{
    public function __invoke(): void
    {
        while (true) {
            $name = ucfirst(Input::prompt('Enter new Command name: '));
            if ($name === '') {
                return;
            }

            if (! $this->isValid($name)) {
                Output::error(sprintf('Invalid Command name: "%s"', $name));
                continue;
            }

            $command = $this->fileSystem->command($name);
            if ($command->exists()) {
                Output::error(
                    sprintf(
                        'Command "%s" already exists at %s',
                        $command->getComponent()->getClassName(),
                        $command->getPath()
                    )
                );
                continue;
            }

            $command
                ->ensureParentDirectoryExists()
                ->getComponent()
                    ->useClass(Import::SYMFONY_COMPONENT_CONSOLE_ATTRIBUTE_ASCOMMAND)
                    ->useClass(Import::SYMFONY_COMPONENT_CONSOLE_COMMAND_COMMAND)
                    ->useClass(Import::SYMFONY_COMPONENT_CONSOLE_INPUT_INPUTINTERFACE)
                    ->useClass(Import::SYMFONY_COMPONENT_CONSOLE_OUTPUT_OUTPUTINTERFACE)
                    ->useClass(Import::SYMFONY_COMPONENT_CONSOLE_STYLE_SYMFONYSTYLE);

            $content = $this->render($command->getComponent());
            if (! $command->create($content)) {
                Output::error(sprintf('Could not create Command "%s"', $command->getPath()), true);
            }
            Output::info(sprintf('Created Command "%s"', $command->getPath()));
        }
    }

    public function create(string $name): File
    {
        if (! $this->isValid($name)) {
            Output::error(sprintf('Invalid Command name: "%s"', $name), true);
        }

        $command = $this->fileSystem->command($name);
        if ($command->exists()) {
            Output::error(
                sprintf(
                    'Command "%s" already exists at %s',
                    $command->getComponent()->getClassName(),
                    $command->getPath()
                ),
                true
            );
        }

        $command
            ->ensureParentDirectoryExists()
            ->getComponent()
                ->useClass(Import::SYMFONY_COMPONENT_CONSOLE_ATTRIBUTE_ASCOMMAND)
                ->useClass(Import::SYMFONY_COMPONENT_CONSOLE_COMMAND_COMMAND)
                ->useClass(Import::SYMFONY_COMPONENT_CONSOLE_INPUT_INPUTINTERFACE)
                ->useClass(Import::SYMFONY_COMPONENT_CONSOLE_OUTPUT_OUTPUTINTERFACE)
                ->useClass(Import::SYMFONY_COMPONENT_CONSOLE_STYLE_SYMFONYSTYLE);

        $serviceInterface = $this->fileSystem->serviceInterface($name);
        if ($serviceInterface->exists()) {
            $command
                ->getComponent()
                    ->useClass(Import::DOT_DEPENDENCYINJECTION_ATTRIBUTE_INJECT)
                    ->useClass($serviceInterface->getComponent()->getFqcn());
        }

        $content = $this->render(
            $command->getComponent(),
            $serviceInterface->exists() ? $serviceInterface->getComponent() : null,
        );
        if (! $command->create($content)) {
            Output::error(sprintf('Could not create Command "%s"', $command->getPath()), true);
        }
        Output::info(sprintf('Created Command "%s"', $command->getPath()));

        return $command;
    }

    public function getDefaultName(string $className): string
    {
        $className = preg_replace('/Command$/', '', $className);

        $parts  = preg_split('/(?<!^)(?=[A-Z])/', $className);
        $module = array_shift($parts);

        if (count($parts) === 0) {
            $parts[] = 'action';
        }

        return strtolower(sprintf('%s:%s', $module, implode('-', $parts)));
    }

    public function render(Component $command, ?Component $serviceInterface = null): string
    {
        $methods = [];

        $constructor = (new Constructor())->addBodyLine('parent::__construct(self::$defaultName);');
        if ($serviceInterface !== null) {
            $constructor->addInject(
                (new Inject())->addArgument($serviceInterface->getClassString())
            )
            ->addPromotedPropertyFromComponent($serviceInterface);
        }
        $methods[] = $constructor;

        $methods[] = (new Method('configure'))
            ->setVisibility(VisibilityEnum::Protected)
            ->setBody(<<<BODY
        \$this
            ->setName(self::\$defaultName)
            ->setDescription('Command description.');
BODY);

        $methods[] = (new Method('execute'))
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

        return $this->stub->render('command.stub', [
            'COMMAND_CLASS_NAME'   => $command->getClassName(),
            'COMMAND_DEFAULT_NAME' => $this->getDefaultName($command->getClassName()),
            'COMMAND_NAMESPACE'    => $command->getNamespace(),
            'METHODS'              => implode(PHP_EOL . PHP_EOL . '    ', $methods),
            'USES'                 => $command->getImport()->render(),
        ]);
    }
}
