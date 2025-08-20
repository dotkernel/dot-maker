<?php

declare(strict_types=1);

namespace DotTest\Maker\Type;

use Dot\Maker\ColorEnum;
use Dot\Maker\Config;
use Dot\Maker\Context;
use Dot\Maker\FileSystem;
use Dot\Maker\IO\Output;
use Dot\Maker\Type\Module;
use Dot\Maker\Type\OpenApi;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

use function fclose;
use function fopen;
use function rewind;
use function sprintf;
use function stream_get_contents;

use const PHP_EOL;

class OpenApiTest extends TestCase
{
    private Config $config;
    private Context $context;
    private FileSystem $fileSystem;
    private Module $module;
    private string $moduleName = 'ModuleName';

    /** @var resource $outputStream */
    private $outputStream;

    protected function setUp(): void
    {
        $this->outputStream = fopen('php://memory', 'w+');
        Output::setOutputStream($this->outputStream);
    }

    protected function tearDown(): void
    {
        fclose($this->outputStream);
    }

    public function testCallToCreateWillEarlyReturnWhenProjectTypeIsApi(): void
    {
        $root = vfsStream::setup('root', 0644, [
            'composer.json' => '{
                "autoload": {
                    "psr-4": {
                        "Admin\\\\App\\\\": "src/App/src/"
                    }
                }
            }',
        ]);

        $this->config     = new Config($root->url());
        $this->context    = new Context($root->url());
        $this->fileSystem = (new FileSystem($this->context))->setModuleName($this->moduleName);
        $this->module     = new Module($this->fileSystem, $this->context, $this->config);

        $this->expectExceptionMessage('OpenApi can be created only in an API');

        $openApi = new OpenApi($this->fileSystem, $this->context, $this->config, $this->module);
        $openApi->create($this->moduleName);
    }

    public function testCallToCreateWillFailWhenFileAlreadyExists(): void
    {
        $root = vfsStream::setup('root', 0644, [
            'composer.json' => '{
                "autoload": {
                    "psr-4": {
                        "Api\\\\ModuleName\\\\": "src/ModuleName/src/"
                    }
                }
            }',
            'src'           => [
                'ModuleName' => [
                    'src' => [
                        'OpenAPI.php' => 'some content',
                    ],
                ],
            ],
        ]);

        $this->config     = new Config($root->url());
        $this->context    = new Context($root->url());
        $this->fileSystem = (new FileSystem($this->context))->setModuleName($this->moduleName);
        $this->module     = new Module($this->fileSystem, $this->context, $this->config);

        $file = $this->fileSystem->openApi();

        $this->expectExceptionMessage(
            sprintf('Class "OpenAPI" already exists at %s', $file->getPath())
        );

        $openApi = new OpenApi($this->fileSystem, $this->context, $this->config, $this->module);
        $openApi->create($this->moduleName);
    }

    public function testWillCreateFile(): void
    {
        $root = vfsStream::setup('root', 0644, [
            'composer.json' => '{
                "autoload": {
                    "psr-4": {
                        "Api\\\\App\\\\": "src/App/src/"
                    }
                }
            }',
        ]);

        $this->config     = new Config($root->url());
        $this->context    = new Context($root->url());
        $this->fileSystem = (new FileSystem($this->context))->setModuleName($this->moduleName);
        $this->module     = new Module($this->fileSystem, $this->context, $this->config);

        $this->fileSystem->collection($this->moduleName)->create('...');
        $this->fileSystem->entity($this->moduleName)->create('...');
        $this->fileSystem->apiDeleteResourceHandler($this->moduleName)->create('...');
        $this->fileSystem->apiGetResourceHandler($this->moduleName)->create('...');
        $this->fileSystem->apiGetCollectionHandler($this->moduleName)->create('...');
        $this->fileSystem->apiPatchResourceHandler($this->moduleName)->create('...');
        $this->fileSystem->apiPostResourceHandler($this->moduleName)->create('...');
        $this->fileSystem->apiPutResourceHandler($this->moduleName)->create('...');

        $openApi = new OpenApi($this->fileSystem, $this->context, $this->config, $this->module);
        $file    = $openApi->create($this->moduleName);

        rewind($this->outputStream);
        $this->assertFileExists($file->getPath());
        $this->assertTrue($file->exists());
        $this->assertSame($this->dataProviderRenderedOpenApi(), $file->read());
        $this->assertSame(
            ColorEnum::colorize(
                'Created OpenAPI: vfs://root/src/ModuleName/src/OpenAPI.php',
                ColorEnum::ForegroundBrightGreen
            ) . PHP_EOL,
            stream_get_contents($this->outputStream)
        );
    }

    private function dataProviderRenderedOpenApi(): string
    {
        return <<<BODY
<?php

declare(strict_types=1);

namespace Api\ModuleName;

use Api\ModuleName\Collection\ModuleNameCollection;
use Api\ModuleName\Entity\ModuleName;
use Api\ModuleName\Handler\ModuleName\DeleteModuleNameResourceHandler;
use Api\ModuleName\Handler\ModuleName\GetModuleNameResourceHandler;
use Api\ModuleName\Handler\ModuleName\PatchModuleNameResourceHandler;
use Api\ModuleName\Handler\ModuleName\PostModuleNameResourceHandler;
use Api\ModuleName\Handler\ModuleName\PutModuleNameResourceHandler;
use DateTimeImmutable;
use Fig\Http\Message\StatusCodeInterface;
use OpenApi\Attributes as OA;

/**
 * @see DeleteModuleNameResourceHandler::handle()
 */
#[OA\Delete(
    path: '/module-name/{uuid}',
    description: 'Authenticated (super)admin deletes resource of type ModuleName, identified by its UUID',
    summary: 'Admin deletes a resource of type ModuleName',
    security: [['AuthToken' => []]],
    tags: ['ModuleName'],
    parameters: [
        new OA\Parameter(
            name: 'uuid',
            description: 'ModuleName UUID',
            in: 'path',
            required: true,
            schema: new OA\Schema(type: 'string'),
        ),
    ],
    responses: [
        new OA\Response(
            response: StatusCodeInterface::STATUS_NO_CONTENT,
            description: 'ModuleName has been deleted',
        ),
        new OA\Response(
            response: StatusCodeInterface::STATUS_NOT_FOUND,
            description: 'Not Found',
        ),
    ],
)]

/**
 * @see GetModuleNameResourceHandler::handle()
 */
#[OA\Get(
    path: '/module-name/{uuid}',
    description: 'Authenticated (super)admin fetches a resource of type ModuleName, identified by its UUID',
    summary: 'Admin fetches a resource of type ModuleName',
    security: [['AuthToken' => []]],
    tags: ['ModuleName'],
    parameters: [
        new OA\Parameter(
            name: 'uuid',
            description: 'ModuleName UUID',
            in: 'path',
            required: true,
            schema: new OA\Schema(type: 'string'),
        ),
    ],
    responses: [
        new OA\Response(
            response: StatusCodeInterface::STATUS_OK,
            description: 'ModuleName account',
            content: new OA\JsonContent(ref: '#/components/schemas/ModuleName'),
        ),
        new OA\Response(
            response: StatusCodeInterface::STATUS_NOT_FOUND,
            description: 'Not Found',
        ),
    ],
)]

/**
 * @see DeleteModuleNameResourceHandler::handle()
 */
#[OA\Get(
    path: '/module-name',
    description: 'Authenticated (super)admin fetches a list of ModuleNames',
    summary: 'Admin lists ModuleNames',
    security: [['AuthToken' => []]],
    tags: ['ModuleName'],
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
                new OA\Examples(example: 'moduleName.created', summary: 'Created', value: 'moduleName.created'),
                new OA\Examples(example: 'moduleName.updated', summary: 'Updated', value: 'moduleName.updated'),
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
            description: 'List of ModuleNames',
            content: new OA\JsonContent(ref: '#/components/schemas/ModuleNameCollection'),
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

/**
 * @see PatchModuleNameResourceHandler::handle()
 */
#[OA\Patch(
    path: '/module-name/{uuid}',
    description: 'Authenticated (super)admin updates an existing ModuleName',
    summary: 'Admin updates an existing ModuleName',
    security: [['AuthToken' => []]],
    requestBody: new OA\RequestBody(
        description: 'Update ModuleName request',
        required: true,
        content: new OA\JsonContent(
            properties: [],
            type: 'object',
        ),
    ),
    tags: ['ModuleName'],
    parameters: [
        new OA\Parameter(
            name: 'uuid',
            description: 'ModuleName UUID',
            in: 'path',
            required: true,
            schema: new OA\Schema(type: 'string'),
        ),
    ],
    responses: [
        new OA\Response(
            response: StatusCodeInterface::STATUS_OK,
            description: 'ModuleName updated',
            content: new OA\JsonContent(ref: '#/components/schemas/ModuleName'),
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

/**
 * @see PostModuleNameResourceHandler::handle()
 */
#[OA\Post(
    path: '/module-name',
    description: 'Authenticated (super)admin creates a new ModuleName',
    summary: 'Admin creates a new ModuleName',
    security: [['AuthToken' => []]],
    requestBody: new OA\RequestBody(
        description: 'Create ModuleName request',
        required: true,
        content: new OA\JsonContent(
            required: [],
            properties: [],
            type: 'object',
        ),
    ),
    tags: ['ModuleName'],
    responses: [
        new OA\Response(
            response: StatusCodeInterface::STATUS_CREATED,
            description: 'ModuleName created',
            content: new OA\JsonContent(ref: '#/components/schemas/ModuleName'),
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

/**
 * @see PutModuleNameResourceHandler::handle()
 */
#[OA\Put(
    path: '/module-name/{uuid}',
    description: 'Authenticated (super)admin replaces an existing ModuleName',
    summary: 'Admin updates an existing ModuleName',
    security: [['AuthToken' => []]],
    requestBody: new OA\RequestBody(
        description: 'Replace ModuleName request',
        required: true,
        content: new OA\JsonContent(
            properties: [],
            type: 'object',
        ),
    ),
    tags: ['ModuleName'],
    parameters: [
        new OA\Parameter(
            name: 'uuid',
            description: 'ModuleName UUID',
            in: 'path',
            required: true,
            schema: new OA\Schema(type: 'string'),
        ),
    ],
    responses: [
        new OA\Response(
            response: StatusCodeInterface::STATUS_OK,
            description: 'ModuleName updated',
            content: new OA\JsonContent(ref: '#/components/schemas/ModuleName'),
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

/**
 * @see ModuleName
 */
#[OA\Schema(
    schema: 'ModuleName',
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
                            example: 'https://example.com/module-name/1234abcd-abcd-4321-12ab-123456abcdef',
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

/**
 * @see ModuleNameCollection
 */
#[OA\Schema(
    schema: 'ModuleNameCollection',
    properties: [
        new OA\Property(
            property: '_embedded',
            properties: [
                new OA\Property(
                    property: 'ModuleNames',
                    type: 'array',
                    items: new OA\Items(
                        ref: '#/components/schemas/ModuleName',
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
class OpenAPI
{
}

BODY;
    }
}
