<?php

declare(strict_types=1);

namespace Dot\Maker\Command;

use Dot\Maker\Component\HandlerComponent;
use Dot\Maker\Config\ComponentConfigInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MakeHandlerCommand extends Command implements MakeComponentCommandInterface
{
    protected static $defaultName = 'make:handler';
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
            ->setDescription('Create new handler')
            ->addArgument(
                'fqcn',
                InputArgument::REQUIRED,
                'Fully qualified class name'
            )
            ->addOption(
                'annotated',
                'a',
                InputOption::VALUE_NONE,
                ''
            );
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $handler = (new HandlerComponent($this->componentConfig))->init(
                $input->getArgument('fqcn'),
                $input->getOption('annotated'),
            );

            $this->addDependencies($io, $handler);

            if (!$handler->create()->exists()) {
                throw new Exception(
                    sprintf('Create handler [%s]: FAILED', $handler->getFqcn())
                );
            }

            if ($handler->hasFactory()) {
                if (!$handler->getFactory()->create()->exists()) {
                    throw new Exception(
                        sprintf('Create factory [%s]: FAILED', $handler->getFactory()->getFqcn())
                    );
                }
            }

            $this->printSummary($io, $handler);

            return Command::SUCCESS;
        } catch (Exception $exception) {
            $io->error($exception->getMessage());
        }

        return Command::FAILURE;
    }

    protected function printSummary(SymfonyStyle $io, HandlerComponent $handler): void
    {
        $created = [$handler->getRealPath()];
        if ($handler->hasFactory()) {
            $created[] = $handler->getFactory()->getRealPath();
        }

        $nextSteps = [];
        $nextSteps[] = sprintf(
            'Open <comment>%s</comment> and locate the <info>getDependencies</info> method.',
            $handler->getConfigProviderRealPath()
        );
        $nextSteps[] = 'Append the following line to the <info>factories</info> array:';
        if ($handler->isAnnotated()) {
            $nextSteps[] = sprintf(
                '    <comment>%s::class => %s,</comment>',
                $handler->getFqcn(),
                $handler->getComponentConfig()->getAnnotatedServiceFactory()
            );
        } else {
            $nextSteps[] = sprintf(
                '    <comment>%s::class => %s::class,</comment>',
                $handler->getFqcn(),
                $handler->getFactory()->getFqcn()
            );
        }
        $nextSteps[] = 'Save and close the file.';
        $nextSteps[] = '';

        $nextSteps[] = sprintf('Implement your logic inside <info>%s</info>.', $handler->getName());

        $io->title('Created:');
        $io->listing($created);
        $io->title('Next steps:');
        $io->listing($nextSteps);
    }
}
