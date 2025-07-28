<?php

declare(strict_types=1);

namespace Dot\Maker\Type;

use Dot\Maker\Component;
use Dot\Maker\Component\Import;
use Dot\Maker\ContextInterface;
use Dot\Maker\FileSystem\File;
use Dot\Maker\IO\Input;
use Dot\Maker\IO\Output;

use function sprintf;
use function ucfirst;

class InputFilter extends AbstractType implements FileInterface
{
    public function __invoke(): void
    {
        while (true) {
            $name = ucfirst(Input::prompt('Enter new InputFilter name: '));
            if ($name === '') {
                return;
            }

            if (! $this->isValid($name)) {
                Output::error(sprintf('Invalid InputFilter name: "%s"', $name));
                continue;
            }

            $inputFilter = $this->fileSystem->inputFilter($name);
            if ($inputFilter->exists()) {
                Output::error(
                    sprintf(
                        'InputFilter "%s" already exists at %s',
                        $inputFilter->getComponent()->getClassName(),
                        $inputFilter->getPath()
                    )
                );
                continue;
            }

            $inputFilter
                ->ensureParentDirectoryExists()
                ->getComponent()
                    ->useClass($this->getAbstractInputFilterFqcn());

            $content = $this->render($inputFilter->getComponent());
            if (! $inputFilter->create($content)) {
                Output::error(sprintf('Could not create InputFilter "%s"', $inputFilter->getPath()), true);
            }
            Output::info(sprintf('Created InputFilter "%s"', $inputFilter->getPath()));
        }
    }

    public function create(string $name): ?File
    {
        if (! $this->isValid($name)) {
            Output::error(sprintf('Invalid InputFilter name: "%s"', $name), true);
        }

        $inputFilter = $this->fileSystem->inputFilter($name);
        if ($inputFilter->exists()) {
            Output::error(
                sprintf(
                    'InputFilter "%s" already exists at %s',
                    $inputFilter->getComponent()->getClassName(),
                    $inputFilter->getPath()
                ),
                true
            );
        }

        $inputFilter
            ->ensureParentDirectoryExists()
            ->getComponent()
                ->useClass($this->getAbstractInputFilterFqcn());

        $content = $this->render($inputFilter->getComponent());
        if (! $inputFilter->create($content)) {
            Output::error(sprintf('Could not create InputFilter "%s"', $inputFilter->getPath()), true);
        }
        Output::info(sprintf('Created InputFilter "%s"', $inputFilter->getPath()));

        return $inputFilter;
    }

    public function getAbstractInputFilterFqcn(): string
    {
        $format = Import::ROOT_APP_INPUTFILTER_ABSTRACTINPUTFILTER;

        if ($this->context->hasCore()) {
            return sprintf($format, ContextInterface::NAMESPACE_CORE);
        }

        return sprintf($format, $this->context->getRootNamespace());
    }

    public function render(Component $inputFilter): string
    {
        return $this->stub->render('input-filter.stub', [
            'INPUTFILTER_CLASS_NAME' => $inputFilter->getClassName(),
            'INPUTFILTER_NAMESPACE'  => $inputFilter->getNamespace(),
            'CONSTRUCTOR'            => $inputFilter->getConstructor()->render(),
            'USES'                   => $inputFilter->getImport()->render(),
        ]);
    }
}
