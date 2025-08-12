<?php

declare(strict_types=1);

namespace Dot\Maker\Type;

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
use Throwable;

use function sprintf;
use function ucfirst;

class Input extends AbstractType implements FileInterface
{
    public function __invoke(): void
    {
        while (true) {
            $name = ucfirst(\Dot\Maker\IO\Input::prompt('Enter new Input name: '));
            if ($name === '') {
                return;
            }

            try {
                $this->create($name);
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
            throw new BadRequestException(sprintf('Invalid Input name: "%s"', $name));
        }

        $input = $this->fileSystem->input($name);
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
            ->useClass($this->import->getAppMessageFqcn())
            ->useClass(Import::LAMINAS_FILTER_STRINGTRIM)
            ->useClass(Import::LAMINAS_FILTER_STRIPTAGS)
            ->useClass(Import::LAMINAS_INPUTFILTER_INPUT)
            ->useClass(Import::LAMINAS_VALIDATOR_NOTEMPTY);

        $constructor = (new Constructor())
            ->addParameter(
                new Parameter('name', 'string', true, 'null')
            )
            ->addParameter(
                new Parameter('isRequired', 'bool', false, 'true')
            )->setBody(<<<BODY
        parent::__construct(\$name);

        \$this->setRequired(\$isRequired);
        \$this->getFilterChain()
            ->attachByName(StringTrim::class)
            ->attachByName(StripTags::class);

        // chain more validators below

        \$this->getValidatorChain()
            ->attachByName(NotEmpty::class, [
                'message' => Message::VALIDATOR_REQUIRED_FIELD,
            ], true);
BODY);
        $class->addMethod($constructor);

        return $class->render();
    }
}
