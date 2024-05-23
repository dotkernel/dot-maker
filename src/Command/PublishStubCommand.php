<?php

declare(strict_types=1);

namespace Dot\Maker\Command;

use Dot\Maker\Exception\DuplicateStubException;
use Dot\Maker\Service\StubServiceInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class PublishStubCommand extends Command
{
    protected static $defaultName = 'stub:publish';
    protected StubServiceInterface $stubService;

    public function __construct(StubServiceInterface $stubService)
    {
        parent::__construct(self::$defaultName);

        $this->stubService = $stubService;
    }

    protected function configure()
    {
        $this
            ->setName(self::$defaultName)
            ->setDescription('Publish component stubs')
            ->addOption(
                'overwrite',
                'o',
                InputOption::VALUE_NONE,
                'Overwrite already published stubs.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $this->stubService->publishStubs(
                $input->getOption('overwrite')
            );
            $io->success('Stubs have been published.');
            $io->writeln(
                sprintf(
                    'Location: <comment>%s</comment>',
                    $this->stubService->getComponentConfig()->getPublishedStubsDirRealPath()
                )
            );

            return Command::SUCCESS;
        } catch (DuplicateStubException $exception) {
            $io->warning('Stubs are already published. Use -o|--overwrite in order to overwrite stubs.');
            $io->writeln(
                sprintf(
                    'Location: <comment>%s</comment>',
                    $this->stubService->getComponentConfig()->getPublishedStubsDirRealPath()
                )
            );
        } catch (Exception $exception) {
            $io->error($exception->getMessage());
        }

        return Command::FAILURE;
    }
}
