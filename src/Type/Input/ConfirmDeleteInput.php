<?php

declare(strict_types=1);

namespace Dot\Maker\Type\Input;

use Dot\Maker\Component;
use Dot\Maker\Component\ClassFile;
use Dot\Maker\Component\Import;
use Dot\Maker\Component\Method\Constructor;
use Dot\Maker\Component\Parameter;
use Dot\Maker\Exception\BadRequestException;
use Dot\Maker\Exception\DuplicateFileException;
use Dot\Maker\Exception\RuntimeException;
use Dot\Maker\FileSystem\File;
use Dot\Maker\IO\Output;
use Dot\Maker\Type\AbstractType;
use Dot\Maker\Type\FileInterface;

use function sprintf;

class ConfirmDeleteInput extends AbstractType implements FileInterface
{
    /**
     * @throws BadRequestException
     * @throws DuplicateFileException
     * @throws RuntimeException
     */
    public function create(string $name): File
    {
        if (! $this->isValid($name)) {
            throw new BadRequestException(sprintf('Input name: "%s"', $name));
        }

        $input = $this->fileSystem->confirmDeleteInput($name);
        if ($input->exists()) {
            throw DuplicateFileException::create($input);
        }

        $content = $this->render($input->getComponent());

        $input->create($content);

        Output::success(sprintf('Created Input: %s', $input->getPath()));

        return $input;
    }

    public function render(Component $input): string
    {
        $class = (new ClassFile($input->getNamespace(), $input->getClassName()))
            ->setExtends('Input')
            ->useClass(Import::LAMINAS_INPUTFILTER_INPUT)
            ->useClass(Import::LAMINAS_VALIDATOR_INARRAY)
            ->useClass(Import::LAMINAS_VALIDATOR_NOTEMPTY);

        $message = sprintf('Please confirm the %s deletion.', $this->fileSystem->getModuleName());

        $constructor = (new Constructor())
            ->addParameter(
                new Parameter('name', 'string', true, 'null')
            )
            ->addParameter(
                new Parameter('isRequired', 'bool', false, 'true')
            )->setBody(<<<BODY
        parent::__construct(\$name);

        \$this->setRequired(\$isRequired);

        \$this->getValidatorChain()
            ->attachByName(NotEmpty::class, [
                'message' => '$message',
            ], true)
            ->attachByName(InArray::class, [
                'message'  => '$message',
                'haystack' => [
                    'yes',
                ],
            ], true);
BODY);
        $class->addMethod($constructor);

        return $class->render();
    }
}
