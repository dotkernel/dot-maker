<?php

declare(strict_types=1);

namespace Dot\Maker\Command;

use Dot\Maker\Component\FactoryComponent;
use Dot\Maker\Config\ComponentConfigInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MakeFactoryCommand extends Command implements MakeComponentCommandInterface
{
    protected static $defaultName = 'make:factory';
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
            ->setDescription('Create new factory')
            ->addArgument(
                'fqcn',
                InputArgument::REQUIRED,
                'Fully qualified class name'
            );
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $command = (new FactoryComponent($this->componentConfig))->init(
                $input->getArgument('fqcn')
            );
        } catch (Exception $exception) {
            $output->writeln(
                sprintf('<bg=red;fg=bright-white>%s</>', $exception->getMessage())
            );

            return Command::FAILURE;
        }

        $this->addDependencies($io, $command);

        $output->writeln('');
        try {
            $factory = (new FactoryComponent($this->componentConfig))
                ->init($input->getArgument('fqcn'))
                ->create();

            if (!$factory->exists()) {
                throw new Exception(
                    sprintf(
                        'Create factory</> [<comment>%s</comment>]: <error>FAILED</error>',
                        $factory->getFqcn()
                    )
                );
            }

            $output->writeln(
                sprintf(
                    'Create factory</> [<comment>%s</comment>]: <info>SUCCESS</info> (<comment>%s</comment>)',
                    $factory->getFqcn(),
                    $factory->getPath()
                )
            );
        } catch (Exception $exception) {
            $output->writeln($exception->getMessage());

            return Command::FAILURE;
        }
        $output->writeln('');

        return Command::SUCCESS;
    }
}
