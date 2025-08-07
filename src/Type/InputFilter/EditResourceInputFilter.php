<?php

declare(strict_types=1);

namespace Dot\Maker\Type\InputFilter;

use Dot\Maker\Component;
use Dot\Maker\Component\ClassFile;
use Dot\Maker\Component\Method\Constructor;
use Dot\Maker\Exception\BadRequestException;
use Dot\Maker\Exception\DuplicateFileException;
use Dot\Maker\Exception\RuntimeException;
use Dot\Maker\FileSystem\File;
use Dot\Maker\IO\Input;
use Dot\Maker\IO\Output;
use Dot\Maker\Type\AbstractType;
use Dot\Maker\Type\FileInterface;
use Throwable;

use function sprintf;
use function ucfirst;

class EditResourceInputFilter extends AbstractType implements FileInterface
{
    public function __invoke(): void
    {
        while (true) {
            $name = ucfirst(Input::prompt('Enter new InputFilter name: '));
            if ($name === '') {
                break;
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
    public function create(string $name): File
    {
        if (! $this->isValid($name)) {
            Output::error(sprintf('Invalid InputFilter name: "%s"', $name), true);
        }

        $inputFilter = $this->fileSystem->editResourceInputFilter($name);
        if ($inputFilter->exists()) {
            throw DuplicateFileException::create($inputFilter);
        }

        if ($this->context->isApi()) {
            $content = $this->renderApi($name, $inputFilter->getComponent());
        } else {
            $content = $this->render($name, $inputFilter->getComponent());
        }

        $inputFilter->create($content);

        Output::success(sprintf('Created InputFilter: %s', $inputFilter->getPath()));

        return $inputFilter;
    }

    public function render(string $name, Component $inputFilter): string
    {
        $class = (new ClassFile($inputFilter->getNamespace(), $inputFilter->getClassName()))
            ->setExtends('AbstractInputFilter')
            ->useClass($this->import->getAbstractInputFilterFqcn())
            ->useClass($this->import->getCsrfInputFqcn())
            ->setComment(<<<COMM
/**
 * @phpstan-type Edit{$name}DataType array{}
 * @extends AbstractInputFilter<Edit{$name}DataType>
 */
COMM);

        $init = (new Constructor())
            ->setBody(<<<BODY
        // chain inputs below

        return \$this
            ->add(new CsrfInput('{$name}EditCsrf', true));
BODY);
        $class->addMethod($init);

        return $class->render();
    }

    public function renderApi(string $name, Component $inputFilter): string
    {
        $class = (new ClassFile($inputFilter->getNamespace(), $inputFilter->getClassName()))
            ->setExtends('AbstractInputFilter')
            ->useClass($this->import->getAbstractInputFilterFqcn())
            ->setComment(<<<COMM
/**
 * @phpstan-type Edit{$name}DataType array{}
 * @extends AbstractInputFilter<Edit{$name}DataType>
 */
COMM);

        $init = (new Constructor())
            ->setBody(<<<BODY
        // chain inputs here
BODY);
        $class->addMethod($init);

        return $class->render();
    }
}
