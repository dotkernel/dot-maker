<?php

declare(strict_types=1);

namespace Dot\Maker\Type;

use Dot\Maker\Component;
use Dot\Maker\Component\ClassFile;
use Dot\Maker\Component\Import;
use Dot\Maker\Exception\DuplicateFileException;
use Dot\Maker\Exception\RuntimeException;
use Dot\Maker\FileSystem\File;
use Dot\Maker\IO\Output;

use function implode;
use function sprintf;

use const PHP_EOL;

class OpenApi extends AbstractType implements FileInterface
{
    /**
     * @throws DuplicateFileException
     * @throws RuntimeException
     */
    public function create(string $name): File
    {
        $openApi = $this->fileSystem->openApi();
        if (! $this->context->isApi()) {
            throw new RuntimeException(
                'OpenApi can be created only in an API'
            );
        }

        if ($openApi->exists()) {
            throw DuplicateFileException::create($openApi);
        }

        $content = $this->render(
            $openApi->getComponent(),
            $this->fileSystem->collection($name)->getComponent(),
            $this->fileSystem->entity($name)->getComponent(),
            $this->fileSystem->apiDeleteResourceHandler($name),
            $this->fileSystem->apiGetResourceHandler($name),
            $this->fileSystem->apiGetCollectionHandler($name),
            $this->fileSystem->apiPatchResourceHandler($name),
            $this->fileSystem->apiPostResourceHandler($name),
            $this->fileSystem->apiPutResourceHandler($name),
        );

        $openApi->create($content);

        Output::success(sprintf('Created OpenAPI: %s', $openApi->getPath()));

        return $openApi;
    }

    public function render(
        Component $openApi,
        Component $collection,
        Component $entity,
        File $apiDeleteResourceHandler,
        File $apiGetResourceHandler,
        File $apiGetCollectionHandler,
        File $apiPatchResourceHandler,
        File $apiPostResourceHandler,
        File $apiPutResourceHandler,
    ): string {
        $class = (new ClassFile($openApi->getNamespace(), $openApi->getClassName()))
            ->useClass(Import::DATETIMEIMMUTABLE)
            ->useClass(Import::OPENAPI_ATTRIBUTES, 'OA')
            ->useClass(Import::FIG_HTTP_MESSAGE_STATUSCODEINTERFACE)
            ->useClass($entity->getFqcn());

        $comments = [];

        if ($apiDeleteResourceHandler->exists()) {
            $class->useClass($apiDeleteResourceHandler->getComponent()->getFqcn());

            // phpcs:disable Generic.Files.LineLength.TooLong
            $comments[] = <<<COMM
/**
 * @see {$apiDeleteResourceHandler->getComponent()->getClassName()}::handle()
 */
#[OA\Delete(
    path: '/{$entity->toKebabCase()}/{uuid}',
    description: 'Authenticated (super)admin deletes resource of type {$entity->getClassName()}, identified by its UUID',
    summary: 'Admin deletes a resource of type {$entity->getClassName()}',
    security: [['AuthToken' => []]],
    tags: ['{$entity->getClassName()}'],
    parameters: [
        new OA\Parameter(
            name: 'uuid',
            description: '{$entity->getClassName()} UUID',
            in: 'path',
            required: true,
            schema: new OA\Schema(type: 'string'),
        ),
    ],
    responses: [
        new OA\Response(
            response: StatusCodeInterface::STATUS_NO_CONTENT,
            description: '{$entity->getClassName()} has been deleted',
        ),
        new OA\Response(
            response: StatusCodeInterface::STATUS_NOT_FOUND,
            description: 'Not Found',
        ),
    ],
)]
COMM;
            // phpcs:enable Generic.Files.LineLength.TooLong
        }

        if ($apiGetResourceHandler->exists()) {
            $class->useClass($apiGetResourceHandler->getComponent()->getFqcn());

            // phpcs:disable Generic.Files.LineLength.TooLong
            $comments[] = <<<COMM
/**
 * @see {$apiGetResourceHandler->getComponent()->getClassName()}::handle()
 */
#[OA\Get(
    path: '/{$entity->toKebabCase()}/{uuid}',
    description: 'Authenticated (super)admin fetches a resource of type {$entity->getClassName()}, identified by its UUID',
    summary: 'Admin fetches a resource of type {$entity->getClassName()}',
    security: [['AuthToken' => []]],
    tags: ['{$entity->getClassName()}'],
    parameters: [
        new OA\Parameter(
            name: 'uuid',
            description: '{$entity->getClassName()} UUID',
            in: 'path',
            required: true,
            schema: new OA\Schema(type: 'string'),
        ),
    ],
    responses: [
        new OA\Response(
            response: StatusCodeInterface::STATUS_OK,
            description: '{$entity->getClassName()} account',
            content: new OA\JsonContent(ref: '#/components/schemas/{$entity->getClassName()}'),
        ),
        new OA\Response(
            response: StatusCodeInterface::STATUS_NOT_FOUND,
            description: 'Not Found',
        ),
    ],
)]
COMM;
            // phpcs:enable Generic.Files.LineLength.TooLong
        }

        if ($apiGetCollectionHandler->exists()) {
            $class
                ->useClass($apiGetCollectionHandler->getComponent()->getFqcn())
                ->useClass($collection->getFqcn());

            // phpcs:disable Generic.Files.LineLength.TooLong
            $comments[] = <<<COMM
/**
 * @see {$apiGetCollectionHandler->getComponent()->getClassName()}::handle()
 */
#[OA\Get(
    path: '/{$entity->toKebabCase()}',
    description: 'Authenticated (super)admin fetches a list of {$entity->toPlural()}',
    summary: 'Admin lists {$entity->toPlural()}',
    security: [['AuthToken' => []]],
    tags: ['{$entity->getClassName()}'],
    parameters: [
        new OA\Parameter(
            name: 'page',
            description: 'Page number',
            in: 'query',
            required: false,
            schema: new OA\Schema(type: 'integer'),
            example: 1,
        ),
        new OA\Parameter(
            name: 'limit',
            description: 'Limit',
            in: 'query',
            required: false,
            schema: new OA\Schema(type: 'integer'),
            example: 10,
        ),
        new OA\Parameter(
            name: 'order',
            description: 'Sort by field',
            in: 'query',
            required: false,
            schema: new OA\Schema(type: 'string'),
            examples: [
                new OA\Examples(example: '{$entity->toCamelCase()}.created', summary: 'Created', value: '{$entity->toCamelCase()}.created'),
                new OA\Examples(example: '{$entity->toCamelCase()}.updated', summary: 'Updated', value: '{$entity->toCamelCase()}.updated'),
            ],
        ),
        new OA\Parameter(
            name: 'dir',
            description: 'Sort direction',
            in: 'query',
            required: false,
            schema: new OA\Schema(type: 'string'),
            examples: [
                new OA\Examples(example: 'desc', summary: 'Sort descending', value: 'desc'),
                new OA\Examples(example: 'asc', summary: 'Sort ascending', value: 'asc'),
            ],
        ),
    ],
    responses: [
        new OA\Response(
            response: StatusCodeInterface::STATUS_OK,
            description: 'List of {$entity->toPlural()}',
            content: new OA\JsonContent(ref: '#/components/schemas/{$collection->getClassName()}'),
        ),
        new OA\Response(
            response: StatusCodeInterface::STATUS_BAD_REQUEST,
            description: 'Bad Request',
            content: new OA\JsonContent(ref: '#/components/schemas/ErrorMessage'),
        ),
        new OA\Response(
            response: StatusCodeInterface::STATUS_NOT_FOUND,
            description: 'Not Found',
        ),
    ],
)]
COMM;
            // phpcs:enable Generic.Files.LineLength.TooLong
        }

        if ($apiPatchResourceHandler->exists()) {
            $class->useClass($apiPatchResourceHandler->getComponent()->getFqcn());

            // phpcs:disable Generic.Files.LineLength.TooLong
            $comments[] = <<<COMM
/**
 * @see {$apiPatchResourceHandler->getComponent()->getClassName()}::handle()
 */
#[OA\Patch(
    path: '/{$entity->toKebabCase()}/{uuid}',
    description: 'Authenticated (super)admin updates an existing {$entity->getClassName()}',
    summary: 'Admin updates an existing {$entity->getClassName()}',
    security: [['AuthToken' => []]],
    requestBody: new OA\RequestBody(
        description: 'Update {$entity->getClassName()} request',
        required: true,
        content: new OA\JsonContent(
            properties: [],
            type: 'object',
        ),
    ),
    tags: ['{$entity->getClassName()}'],
    parameters: [
        new OA\Parameter(
            name: 'uuid',
            description: '{$entity->getClassName()} UUID',
            in: 'path',
            required: true,
            schema: new OA\Schema(type: 'string'),
        ),
    ],
    responses: [
        new OA\Response(
            response: StatusCodeInterface::STATUS_OK,
            description: '{$entity->getClassName()} updated',
            content: new OA\JsonContent(ref: '#/components/schemas/{$entity->getClassName()}'),
        ),
        new OA\Response(
            response: StatusCodeInterface::STATUS_BAD_REQUEST,
            description: 'Bad Request',
            content: new OA\JsonContent(ref: '#/components/schemas/ErrorMessage'),
        ),
        new OA\Response(
            response: StatusCodeInterface::STATUS_CONFLICT,
            description: 'Conflict',
            content: new OA\JsonContent(ref: '#/components/schemas/ErrorMessage'),
        ),
        new OA\Response(
            response: StatusCodeInterface::STATUS_NOT_FOUND,
            description: 'Not Found',
            content: new OA\JsonContent(ref: '#/components/schemas/ErrorMessage'),
        ),
    ],
)]
COMM;
            // phpcs:enable Generic.Files.LineLength.TooLong
        }

        if ($apiPostResourceHandler->exists()) {
            $class->useClass($apiPostResourceHandler->getComponent()->getFqcn());

            // phpcs:disable Generic.Files.LineLength.TooLong
            $comments[] = <<<COMM
/**
 * @see {$apiPostResourceHandler->getComponent()->getClassName()}::handle()
 */
#[OA\Post(
    path: '/{$entity->toKebabCase()}',
    description: 'Authenticated (super)admin creates a new {$entity->getClassName()}',
    summary: 'Admin creates a new {$entity->getClassName()}',
    security: [['AuthToken' => []]],
    requestBody: new OA\RequestBody(
        description: 'Create {$entity->getClassName()} request',
        required: true,
        content: new OA\JsonContent(
            required: [],
            properties: [],
            type: 'object',
        ),
    ),
    tags: ['{$entity->getClassName()}'],
    responses: [
        new OA\Response(
            response: StatusCodeInterface::STATUS_CREATED,
            description: '{$entity->getClassName()} created',
            content: new OA\JsonContent(ref: '#/components/schemas/{$entity->getClassName()}'),
        ),
        new OA\Response(
            response: StatusCodeInterface::STATUS_BAD_REQUEST,
            description: 'Bad Request',
            content: new OA\JsonContent(ref: '#/components/schemas/ErrorMessage'),
        ),
        new OA\Response(
            response: StatusCodeInterface::STATUS_CONFLICT,
            description: 'Conflict',
            content: new OA\JsonContent(ref: '#/components/schemas/ErrorMessage'),
        ),
        new OA\Response(
            response: StatusCodeInterface::STATUS_NOT_FOUND,
            description: 'Not Found',
            content: new OA\JsonContent(ref: '#/components/schemas/ErrorMessage'),
        ),
    ],
)]
COMM;
            // phpcs:enable Generic.Files.LineLength.TooLong
        }

        if ($apiPutResourceHandler->exists()) {
            $class->useClass($apiPutResourceHandler->getComponent()->getFqcn());

            // phpcs:disable Generic.Files.LineLength.TooLong
            $comments[] = <<<COMM
/**
 * @see {$apiPutResourceHandler->getComponent()->getClassName()}::handle()
 */
#[OA\Put(
    path: '/{$entity->toKebabCase()}/{uuid}',
    description: 'Authenticated (super)admin replaces an existing {$entity->getClassName()}',
    summary: 'Admin updates an existing {$entity->getClassName()}',
    security: [['AuthToken' => []]],
    requestBody: new OA\RequestBody(
        description: 'Replace {$entity->getClassName()} request',
        required: true,
        content: new OA\JsonContent(
            properties: [],
            type: 'object',
        ),
    ),
    tags: ['{$entity->getClassName()}'],
    parameters: [
        new OA\Parameter(
            name: 'uuid',
            description: '{$entity->getClassName()} UUID',
            in: 'path',
            required: true,
            schema: new OA\Schema(type: 'string'),
        ),
    ],
    responses: [
        new OA\Response(
            response: StatusCodeInterface::STATUS_OK,
            description: '{$entity->getClassName()} updated',
            content: new OA\JsonContent(ref: '#/components/schemas/{$entity->getClassName()}'),
        ),
        new OA\Response(
            response: StatusCodeInterface::STATUS_BAD_REQUEST,
            description: 'Bad Request',
            content: new OA\JsonContent(ref: '#/components/schemas/ErrorMessage'),
        ),
        new OA\Response(
            response: StatusCodeInterface::STATUS_CONFLICT,
            description: 'Conflict',
            content: new OA\JsonContent(ref: '#/components/schemas/ErrorMessage'),
        ),
        new OA\Response(
            response: StatusCodeInterface::STATUS_NOT_FOUND,
            description: 'Not Found',
            content: new OA\JsonContent(ref: '#/components/schemas/ErrorMessage'),
        ),
    ],
)]
COMM;
            // phpcs:enable Generic.Files.LineLength.TooLong
        }

        // phpcs:disable Generic.Files.LineLength.TooLong
        $comments[] = <<<COMM
/**
 * @see {$entity->getClassName()}
 */
#[OA\Schema(
    schema: '{$entity->getClassName()}',
    properties: [
        new OA\Property(property: 'uuid', type: 'string', example: '1234abcd-abcd-4321-12ab-123456abcdef'),
        new OA\Property(property: 'created', type: 'object', example: new DateTimeImmutable()),
        new OA\Property(property: 'updated', type: 'object', example: new DateTimeImmutable()),
        new OA\Property(
            property: '_links',
            properties: [
                new OA\Property(
                    property: 'self',
                    properties: [
                        new OA\Property(
                            property: 'href',
                            type: 'string',
                            example: 'https://example.com/{$entity->toKebabCase()}/1234abcd-abcd-4321-12ab-123456abcdef',
                        ),
                    ],
                    type: 'object',
                ),
            ],
            type: 'object',
        ),
    ],
    type: 'object',
)]
COMM;
        // phpcs:enable Generic.Files.LineLength.TooLong

        $comments[] = <<<COMM
/**
 * @see {$collection->getClassName()}
 */
#[OA\Schema(
    schema: '{$collection->getClassName()}',
    properties: [
        new OA\Property(
            property: '_embedded',
            properties: [
                new OA\Property(
                    property: '{$entity->toPlural()}',
                    type: 'array',
                    items: new OA\Items(
                        ref: '#/components/schemas/{$entity->getClassName()}',
                    ),
                ),
            ],
            type: 'object',
        ),
    ],
    type: 'object',
    allOf: [
        new OA\Schema(ref: '#/components/schemas/Collection'),
    ],
)]
COMM;

        $class->setComment(implode(PHP_EOL . PHP_EOL, $comments));

        return $class->render();
    }
}
