<?php

declare(strict_types=1);

namespace Dot\Maker\Command;

use Dot\Maker\Component\ServiceComponent;
use Dot\Maker\Config\ComponentConfigInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MakeServiceCommand extends Command implements MakeComponentCommandInterface
{
    protected static $defaultName = 'make:service';
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
            ->setDescription('Create new service')
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
            $service = (new ServiceComponent($this->componentConfig))->init(
                $input->getArgument('fqcn'),
                $input->getOption('annotated'),
            );

            $this->addDependencies($io, $service);

            if (!$service->create()->exists()) {
                throw new Exception(
                    sprintf('Create service [%s]: FAILED', $service->getFqcn())
                );
            }

            if ($service->hasInterface()) {
                if (!$service->getInterface()->create()->exists()) {
                    throw new Exception(
                        sprintf('Create interface [%s]: FAILED', $service->getInterface()->getFqcn())
                    );
                }
            }

            if ($service->hasFactory()) {
                if (!$service->getFactory()->create()->exists()) {
                    throw new Exception(
                        sprintf('Create factory [%s]: FAILED', $service->getFactory()->getFqcn())
                    );
                }
            }

            $this->printSummary($io, $service);

            return Command::SUCCESS;
        } catch (Exception $exception) {
            $io->error($exception->getMessage());
        }

        return Command::FAILURE;
    }

    protected function printSummary(SymfonyStyle $io, ServiceComponent $service): void
    {
        $created = [$service->getRealPath()];
        if ($service->hasInterface()) {
            $created[] = $service->getInterface()->getRealPath();
        }
        if ($service->hasFactory()) {
            $created[] = $service->getFactory()->getRealPath();
        }

        $nextSteps = [];
        $nextSteps[] = sprintf(
            'Open <comment>%s</comment> and locate the <info>getDependencies</info> method.',
            $service->getConfigProviderRealPath()
        );
        $nextSteps[] = 'Append the following line to the <info>factories</info> array:';
        if ($service->isAnnotated()) {
            $nextSteps[] = sprintf(
                '    <comment>%s::class => %s,</comment>',
                $service->getFqcn(),
                $service->getComponentConfig()->getAnnotatedServiceFactory()
            );
        } else {
            $nextSteps[] = sprintf(
                '    <comment>%s::class => %s::class,</comment>',
                $service->getFqcn(),
                $service->getFactory()->getFqcn()
            );
        }
        $nextSteps[] = 'Append the following line to the <info>aliases</info> array:';
        $nextSteps[] = sprintf(
            '    <comment>%s::class => %s::class,</comment>',
            $service->getInterface()->getFqcn(),
            $service->getFqcn()
        );
        $nextSteps[] = 'Save and close the file.';
        $nextSteps[] = '';

        $nextSteps[] = sprintf('Implement your logic inside <info>%s</info>.', $service->getName());

        $io->title('Created:');
        $io->listing($created);
        $io->title('Next steps:');
        $io->listing($nextSteps);
    }
}
