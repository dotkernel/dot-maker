<?php

declare(strict_types=1);

namespace Dot\Maker\Type;

use Dot\Maker\Component;
use Dot\Maker\Component\Import;
use Dot\Maker\ContextInterface;
use Dot\Maker\Exception\BadRequestException;
use Dot\Maker\Exception\DuplicateFileException;
use Dot\Maker\Exception\RuntimeException;
use Dot\Maker\FileSystem\File;
use Dot\Maker\IO\Input;
use Dot\Maker\IO\Output;
use Throwable;

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

            try {
                $this->create($name);
                break;
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
    public function create(string $name): ?File
    {
        if (! $this->isValid($name)) {
            throw new BadRequestException(sprintf('Invalid InputFilter name: "%s"', $name));
        }

        $inputFilter = $this->fileSystem->inputFilter($name);
        if ($inputFilter->exists()) {
            throw DuplicateFileException::create($inputFilter);
        }

        $inputFilter
            ->getComponent()
            ->useClass($this->getAbstractInputFilterFqcn());

        $content = '';

        try {
            $inputFilter->create($content);
            Output::info(sprintf('Created InputFilter "%s"', $inputFilter->getPath()));
        } catch (RuntimeException $exception) {
            Output::error($exception->getMessage());
        }

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
