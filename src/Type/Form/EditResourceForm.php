<?php

declare(strict_types=1);

namespace Dot\Maker\Type\Form;

use Dot\Maker\Component;
use Dot\Maker\Component\ClassFile;
use Dot\Maker\Component\Import;
use Dot\Maker\Component\Method;
use Dot\Maker\Component\Method\Constructor;
use Dot\Maker\Component\Parameter;
use Dot\Maker\Exception\BadRequestException;
use Dot\Maker\Exception\DuplicateFileException;
use Dot\Maker\Exception\RuntimeException;
use Dot\Maker\FileSystem\File;
use Dot\Maker\IO\Input;
use Dot\Maker\IO\Output;
use Dot\Maker\Type\AbstractType;
use Dot\Maker\Type\FileInterface;
use Dot\Maker\Type\TypeEnum;
use Throwable;

use function sprintf;
use function ucfirst;

class EditResourceForm extends AbstractType implements FileInterface
{
    public function __invoke(): void
    {
        while (true) {
            $name = ucfirst(Input::prompt('Enter new Form name: '));
            if ($name === '') {
                break;
            }

            try {
                $this->create($name);
                $this->component(TypeEnum::InputFilterEditResource)->create($name);
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
            Output::error(sprintf('Invalid Form name: "%s"', $name), true);
        }

        $form = $this->fileSystem->editResourceForm($name);
        if ($form->exists()) {
            throw DuplicateFileException::create($form);
        }

        $content = $this->render(
            $form->getComponent(),
            $this->fileSystem->entity($name)->getComponent(),
            $this->fileSystem->editResourceInputFilter($name)->getComponent(),
        );

        $form->create($content);

        Output::success(sprintf('Created Form "%s"', $form->getPath()));

        return $form;
    }

    public function render(Component $form, Component $entity, Component $inputFilter): string
    {
        $class = (new ClassFile($form->getNamespace(), $form->getClassName()))
            ->setExtends('AbstractForm')
            ->useClass($this->import->getAbstractFormFqcn())
            ->useClass(Import::LAMINAS_FORM_ELEMENT_CSRF)
            ->useClass(Import::LAMINAS_FORM_ELEMENT_SUBMIT)
            ->useClass(Import::LAMINAS_FORM_EXCEPTION_EXCEPTIONINTERFACE)
            ->useClass(Import::LAMINAS_SESSION_CONTAINER)
            ->useClass($inputFilter->getFqcn())
            ->setComment(<<<COMM
/**
 * @phpstan-import-type Edit{$entity->getClassName()}DataType from {$inputFilter->getClassName()}
 * @extends AbstractForm<Edit{$entity->getClassName()}DataType>
 */
COMM);

        $constructor = (new Constructor())
            ->addParameter(
                new Parameter('name', 'string', true, 'null')
            )
            ->addParameter(
                new Parameter('options', 'array', false, '[]')
            )
            ->setComment(<<<COMM
/**
     * @throws ExceptionInterface
     */
COMM)
            ->setBody(<<<BODY
        parent::__construct(\$name, \$options);

        \$this->init();

        \$this->setAttribute('id', '{$entity->toKebabCase()}-form');
        \$this->setAttribute('class', 'row g-3 needs-validation');
        \$this->setAttribute('novalidate', 'novalidate');

        \$this->inputFilter = new {$inputFilter->getClassName()}();
        \$this->inputFilter->init();
BODY);
        $class->addMethod($constructor);

        $init = (new Method('init'))
            ->setComment(<<<COMM
/**
     * @throws ExceptionInterface
     */
COMM

            )
            ->setBody(<<<BODY
        // add more form elements

        \$this->add(
            (new Csrf('{$entity->toCamelCase()}EditCsrf'))
                ->setOptions([
                    'csrf_options' => ['timeout' => 3600, 'session' => new Container()],
                ])
                ->setAttribute('required', true)
        );
        \$this->add(
            (new Submit('submit'))
                ->setAttribute('type', 'submit')
                ->setAttribute('value', 'Save')
                ->setAttribute('class', 'btn btn-primary btn-color btn-sm')
        );
BODY);
        $class->addMethod($init);

        return $class->render();
    }
}
