<?php

declare(strict_types=1);

namespace Dot\Maker\Command;

use Dot\Maker\Component\MiddlewareComponent;
use Dot\Maker\Config\ComponentConfigInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MakeMiddlewareCommand extends Command implements MakeComponentCommandInterface
{
    protected static $defaultName = 'make:middleware';
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
            ->setDescription('Create new middleware')
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
            $middleware = (new MiddlewareComponent($this->componentConfig))->init(
                $input->getArgument('fqcn'),
                $input->getOption('annotated'),
            );

            $this->addDependencies($io, $middleware);

            if (!$middleware->create()->exists()) {
                throw new Exception(
                    sprintf('Create middleware [%s]: FAILED', $middleware->getFqcn())
                );
            }

            if ($middleware->hasFactory()) {
                if (!$middleware->getFactory()->create()->exists()) {
                    throw new Exception(
                        sprintf('Create factory [%s]: FAILED', $middleware->getFactory()->getFqcn())
                    );
                }
            }

            $this->printSummary($io, $middleware);

            return Command::SUCCESS;
        } catch (Exception $exception) {
            $io->error($exception->getMessage());
        }

        return Command::FAILURE;
    }

    protected function printSummary(SymfonyStyle $io, MiddlewareComponent $middleware): void
    {
        $created = [$middleware->getRealPath()];
        if ($middleware->hasFactory()) {
            $created[] = $middleware->getFactory()->getRealPath();
        }

        $nextSteps = [];
        $nextSteps[] = sprintf(
            'Open <comment>%s</comment> and locate the <info>getDependencies</info> method.',
            $middleware->getConfigProviderRealPath()
        );
        $nextSteps[] = 'Append the following line to the <info>factories</info> array:';
        if ($middleware->isAnnotated()) {
            $nextSteps[] = sprintf(
                '    <comment>%s::class => %s,</comment>',
                $middleware->getFqcn(),
                $middleware->getComponentConfig()->getAnnotatedServiceFactory()
            );
        } else {
            $nextSteps[] = sprintf(
                '    <comment>%s::class => %s::class,</comment>',
                $middleware->getFqcn(),
                $middleware->getFactory()->getFqcn()
            );
        }
        $nextSteps[] = 'Save and close the file.';
        $nextSteps[] = '';

        $nextSteps[] = sprintf('Implement your logic inside <info>%s</info>.', $middleware->getName());

        $io->title('Created:');
        $io->listing($created);
        $io->title('Next steps:');
        $io->listing($nextSteps);
    }
}
