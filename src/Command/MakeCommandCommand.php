<?php

declare(strict_types=1);

namespace Dot\Maker\Command;

use Dot\Maker\Component\CommandComponent;
use Dot\Maker\Config\ComponentConfigInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MakeCommandCommand extends Command implements MakeComponentCommandInterface
{
    protected static $defaultName = 'make:command';
    protected ComponentConfigInterface $componentConfig;

    public function __construct(ComponentConfigInterface $componentConfig)
    {
        parent::__construct(self::$defaultName);
        $this->componentConfig = $componentConfig;
    }

    protected function configure()
    {
        $this
            ->setName(self::$defaultName)
            ->setDescription('Create new command')
            ->addArgument(
                'fqcn',
                InputArgument::REQUIRED,
                'Fully qualified class name'
            )
            ->addOption(
                'annotated',
                'a',
                InputOption::VALUE_NONE,
                'Use annotations / factories'
            );
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $command = (new CommandComponent($this->componentConfig))->initializeNewCommand(
                $input->getArgument('fqcn'),
                $input->getOption('annotated'),
            );
            if ($command->exists()) {
                $question = sprintf('Command [%s] already exists. Do you want to update it?', $command->getFqcn());
                if (! $io->confirm($question, true)) {
                    $io->info(
                        sprintf('No changes have been made to your existing command [%s]', $command->getFqcn())
                    );
                    return Command::SUCCESS;
                }

                $command->initializeExistingCommand($command->getFqcn(), $command->isAnnotated());
            } else {
                $command->initializeGenerator();
            }

            $command
//                ->addDependencies($io)
                ->create();

            if (!$command->exists()) {
                throw new Exception(
                    sprintf('Create command [%s]: FAILED', $command->getFqcn())
                );
            }

            if ($command->hasFactory()) {
                $command->getFactory()->create();
                if (!$command->getFactory()->exists()) {
                    throw new Exception(
                        sprintf('Create factory [%s]: FAILED', $command->getFactory()->getFqcn())
                    );
                }
            }

            $this->printSummary($io, $command);

            return Command::SUCCESS;
        } catch (Exception $exception) {
            $io->error($exception->getMessage());
        }

        return Command::FAILURE;
    }

    protected function printSummary(SymfonyStyle $io, CommandComponent $command): void
    {
        $created = [$command->getRealPath()];
        if ($command->hasFactory()) {
            $created[] = $command->getFactory()->getRealPath();
        }

        $nextSteps = [];
        $nextSteps[] = sprintf(
            'Open <comment>%s</comment> and locate the <info>getDependencies</info> method.',
            $command->getConfigProviderRealPath()
        );
        $nextSteps[] = 'Append the following line to the <info>factories</info> array:';
        if ($command->isAnnotated()) {
            $nextSteps[] = sprintf(
                '    <comment>%s::class => %s::class,</comment>',
                $command->getFqcn(),
                $command->getComponentConfig()->getAnnotatedServiceFactory()
            );
        } else {
            $nextSteps[] = sprintf(
                '    <comment>%s::class => %s::class,</comment>',
                $command->getFqcn(),
                $command->getFactory()->getFqcn()
            );
        }
        $nextSteps[] = 'Save and close the file.';
        $nextSteps[] = '';

        $nextSteps[] = sprintf('Open <comment>%s</comment>.', $command->getComponentConfig()->getCliConfigFile());
        $nextSteps[] = 'Append the following line to the <info>commands</info> array:';
        $nextSteps[] = sprintf(
            '    <comment>%s::getDefaultName() => %s::class,</comment>',
            $command->getFqcn(),
            $command->getFqcn()
        );
        $nextSteps[] = 'Save and close the file.';
        $nextSteps[] = '';

        $nextSteps[] = sprintf('Test %s by executing the following command in your terminal:', $command->getName());
        $nextSteps[] = sprintf('    <info>php bin/cli.php %s</info>', $command->getDefaultName());

        $io->title('Created:');
        $io->listing($created);
        $io->title('Next steps:');
        $io->listing($nextSteps);
    }
}
