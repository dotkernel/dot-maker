<?php

declare(strict_types=1);

namespace Dot\Maker\Command;

use Dot\Maker\Component\Entity\Relation\AbstractRelation;
use Dot\Maker\Component\Entity\Relations;
use Dot\Maker\Component\Entity\Type\AbstractField;
use Dot\Maker\Component\Entity\Types;
use Dot\Maker\Component\EntityComponent;
use Dot\Maker\Config\ComponentConfigInterface;
use Dot\Maker\Variable;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MakeEntityCommand extends Command implements MakeComponentCommandInterface
{
    protected static $defaultName = 'make:entity';
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
            ->setDescription('Create new entity')
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
            $entity = (new EntityComponent($this->componentConfig))->init(
                $input->getArgument('fqcn'),
                $input->getOption('annotated'),
            );

            if ($entity->exists()) {
                $io->info(
                    sprintf('%s already exists.', $entity->getRealPath())
                );
            } else {

            }

            $this->addProperties($io, $entity);

            if (!$entity->create()->exists()) {
                throw new Exception(
                    sprintf(
                        'Create entity <comment>%s</comment>: <error>FAILED</error>',
                        $entity->getFqcn()
                    )
                );
            }

//            if ($entity->hasRepository()) {
//                if (!$entity->getRepository()->create()->exists()) {
//                    throw new Exception(
//                        sprintf(
//                            'Create repository <comment>%s</comment>: <error>FAILED</error>',
//                            $entity->getRepository()->getFqcn()
//                        )
//                    );
//                }
//
//                if ($entity->getRepository()->hasFactory()) {
//                    if (!$entity->getRepository()->getFactory()->create()->exists()) {
//                        throw new Exception(
//                            sprintf(
//                                'Create repository factory <comment>%s</comment>: <error>FAILED</error>',
//                                $entity->getRepository()->getFactory()->getFqcn()
//                            )
//                        );
//                    }
//                }
//            }

            $this->printSummary($io, $entity);

            return Command::SUCCESS;
        } catch (Exception $exception) {
            $io->error($exception->getMessage());
        }

        return Command::FAILURE;
    }

    /**
     * @throws Exception
     */
    protected function addProperties(SymfonyStyle $io, EntityComponent $entity): void
    {
        $typeQuestionInfo = $this->getTypeQuestionInfo();
        while (true) {
            $name = $io->ask('Add property (<comment>enter property name or leave empty to stop</comment>)');
            if (empty($name)) {
                break;
            }

            try {
                $name = Variable::validateName($name);
                if ($entity->hasProperty($name)) {
                    throw new Exception(
                        sprintf('Property with name \'%s\' already exists', $name)
                    );
                }

                while (true) {
                    try {
                        $io->writeln('Specify property type by entering one of the following values:');
                        $io->listing($typeQuestionInfo);
                        $type = $io->ask('Property type');
                        if (!Types::exists($type) && !Relations::exists($type)) {
                            throw new Exception(
                                sprintf('Invalid property type specified: %s', $type)
                            );
                        }
                        break;
                    } catch (Exception $exception) {
                        $io->error($exception->getMessage());
                    }
                }

                $nullable = $io->confirm('Is this property nullable?', false);

                if (Relations::isRelation($type)) {
                    $relation = Relations::fromString($type);

                    while (true) {
                        try {
                            $targetEntity = $io->ask('Specify target entity FQCN');
                            if (empty($targetEntity)) {
                                throw new Exception('Target entity must be specified.');
                            }
                            if (!class_exists($targetEntity)) {
                                throw new Exception(
                                    sprintf('Target entity not found: %s', $targetEntity)
                                );
                            }

                            $relation->setTargetEntity($targetEntity);
                            break;
                        } catch (Exception $exception) {
                            $io->error($exception->getMessage());
                        }
                    }

                    $entity->addRelation(
                        $relation->setName($name)
                    );
                } else {
                    $entity->addField(
                        Types::fromString($type)->setName($name)->setNullable($nullable)
                    );
                }
            } catch (Exception $exception) {
                $io->error($exception->getMessage());
            }
        }
    }

    protected function addRelation(
        InputInterface $input,
        OutputInterface $output,
        QuestionHelper $questionHelper,
        string $name,
        string $type
    ) {
        $relation = (new Relation())
            ->setName($name)
            ->initVariable($name);

        return $relation;
    }

    protected function getTypeQuestionInfo(): array
    {
        return [
            sprintf(
                '<info>Relation types</info>: <comment>%s</comment>',
                implode('</comment>, <comment>', Relations::getRelations())
            ),
            sprintf(
                '<info>Numeric types</info>: <comment>%s</comment>',
                implode('</comment>, <comment>', Types::getNumericTypes())
            ),
            sprintf(
                '<info>String types</info>: <comment>%s</comment>',
                implode('</comment>, <comment>', Types::getStringTypes())
            ),
            sprintf(
                '<info>Bit types</info>: <comment>%s</comment>',
                implode('</comment>, <comment>', Types::getBitTypes())
            ),
            sprintf(
                '<info>Date and time types</info>: <comment>%s</comment>',
                implode('</comment>, <comment>', Types::getDateAndTimeTypes())
            ),
            sprintf(
                '<info>Array types</info>: <comment>%s</comment>',
                implode('</comment>, <comment>', Types::getArrayTypes())
            ),
            sprintf(
                '<info>Object types</info>: <comment>%s</comment>',
                implode('</comment>, <comment>', Types::getObjectTypes())
            ),
        ];
    }

    protected function printSummary(SymfonyStyle $io, EntityComponent $entity): void
    {
    }
}
