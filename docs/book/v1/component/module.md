# Create Module

> This page assumes that you have created a Composer "make" script as described on the [Setup page](../setup.md#add-dot-maker-to-composerjson).

To create a Module, use either of the following commands:

## Run the command

```shell
composer make module
```

OR

```shell
./vendor/bin/dot-maker module
```

## Name the Module

You will be prompted to input a name for the Module:

> New module name:

**The name must contain only letters and numbers.**

If you leave the name blank, the process will exit.

If you input an invalid name (like, "."), an error will be thrown:

> Invalid Module name: "."

If you input the name of an existing Module (like, "ExistingModule"), an error will be thrown:

> Module "ExistingModule" already exists at /path/to/project/src/ExistingModule

If you input a valid name (like, "NewModule"), `dot-maker` will create the Command and output a success message:

> Created directory: /path/to/project/src/NewModule

## Create Module files

From here, the process will run through a predefined set of files to create the Module components.

> The process will not ask for file names because they will be generated from the Module name.

### Entity and Repository

Next, you will be asked to confirm whether the Module needs an Entity (and its matching Repository):

> Create entity and repository? [Y(es)/n(o)]:

On confirmation, the process will create the following files:

- for projects compatible with the Core architecture:
    - `src/Core/src/<ModuleName>/src/Entity/<ModuleName>.php`
    - `src/Core/src/<ModuleName>/src/Repository/<ModuleName>Repository.php`
- for projects which are NOT compatible with the Core architecture:
    - `src/<ModuleName>/src/Entity/<ModuleName>.php`
    - `src/<ModuleName>/src/Repository/<ModuleName>Repository.php`

Without confirmation, the process will skip to the next component.

### Service and ServiceInterface

Next, you will be asked to confirm whether the Module needs a Service (and its matching ServiceInterface):

> Create service and service interface? [Y(es)/n(o)]:

On confirmation, the process will create the following files:

- `src/<ModuleName>/src/Service/<ModuleName>Service.php`
- `src/<ModuleName>/src/Service/<ModuleName>ServiceInterface.php`

If, at the previous step, you chose to create a Repository, it will be automatically injected into the Service.
Also, some common methods will be added to the Service and the ServiceInterface.

Without confirmation, the process will skip to the next component.

### Command

Next, you will be asked to confirm whether the Module needs a Command:

> Create command? [Y(es)/n(o)]:

On confirmation, the process will create the following file:

- `src/<ModuleName>/src/Command/<ModuleName>Command.php`

Without confirmation, the process will skip to the next component.

### Middleware

Next, you will be asked to confirm whether the Module needs a Middleware:

> Create middleware? [Y(es)/n(o)]:

On confirmation, the process will create the following file:

- `src/<ModuleName>/src/Middleware/<ModuleName>Middleware.php`

If, previously you chose to create a ServiceInterface, it will be automatically injected into the Middleware.

Without confirmation, the process will skip to the next component.

### Handler

Next, you will be asked to confirm whether the Module needs request Handlers:

> Create handler? [Y(es)/n(o)]:

On confirmation, depending on the project type, the process will iterate over a list of CRUD operations, asking you to confirm whether the Module needs a request Handler for each operation.

#### API projects

##### List resources

The prompt asks you whether you want to list resources:

> Allow listing Resources? [Y(es)/n(o)]:

On confirmation, the process will create the following files:

- `src/<ModuleName>/src/Collection/<ModuleName>Collection.php`: describes a resource-specific collection
- `src/<ModuleName>/src/Handler/Get<ModuleName>CollectionHandler.php`: handles the resource collection representation

The matching Collection and ServiceInterface will be automatically injected into the Handler.

Without confirmation, the process will skip to the next component.

##### View resources

The prompt asks you whether you want to view resources:

> Allow viewing Resources? [Y(es)/n(o)]:

On confirmation, the process will create the following file:

- `src/<ModuleName>/src/Handler/Get<ModuleName>ResourceHandler.php`: handles the resource representation

The matching ServiceInterface will be automatically injected into the Handler.

Without confirmation, the process will skip to the next component.

##### Create resources

The prompt asks you whether you want to create resources:

> Allow creating Resources? [Y(es)/n(o)]:

On confirmation, the process will create the following files:

- `src/<ModuleName>/src/Handler/Post<ModuleName>ResourceHandler.php`: handles the resource creation
- `src/<ModuleName>/src/InputFilter/Create<ModuleName>InputFilter.php`: request payload validators

The matching InputFilter and ServiceInterface will be automatically injected into the Handler.

Without confirmation, the process will skip to the next component.

##### Delete resources

The prompt asks you whether you want to delete resources:

> Allow deleting Resources? [Y(es)/n(o)]:

On confirmation, the process will create the following files:

- `src/<ModuleName>/src/Handler/Delete<ModuleName>ResourceHandler.php`: handles the resource deletion

The matching ServiceInterface will be automatically injected into the Handler.

Without confirmation, the process will skip to the next component.

##### Edit resources

The prompt asks you whether you want to edit resources:

> Allow editing Resources? [Y(es)/n(o)]:

On confirmation, the process will create the following files:

- `src/<ModuleName>/src/Handler/Patch<ModuleName>ResourceHandler.php`: handles the resource update
- `src/<ModuleName>/src/InputFilter/Edit<ModuleName>InputFilter.php`: request payload validators

The matching InputFilter and ServiceInterface will be automatically injected into the Handler.

Without confirmation, the process will skip to the next component.

##### Replace resources

The prompt asks you whether you want to replace resources:

> Allow replacing Resources? [Y(es)/n(o)]:

On confirmation, the process will create the following files:

- `src/<ModuleName>/src/Handler/Put<ModuleName>ResourceHandler.php`: handles the resource replacement
- `src/<ModuleName>/src/InputFilter/Replace<ModuleName>InputFilter.php`: request payload validators

The matching InputFilter and ServiceInterface will be automatically injected into the Handler.

Without confirmation, the process will skip to the next component.

#### Non-API projects

##### List resources

The prompt asks you whether you want to list resources:

> Allow listing Resources? [Y(es)/n(o)]:

On confirmation, the process will create the following files:

- `src/<ModuleName>/src/Handler/Get<ModuleName>ListHandler.php`: renders the resource list page
- `src/<ModuleName>/templates/<ModuleName>/<ModuleName>-list.html.twig`: renders the resource list

The matching ServiceInterface will be automatically injected into the Handler.

Without confirmation, the process will skip to the next component.

##### View resources

The prompt asks you whether you want to view resources:

> Allow viewing Resources? [Y(es)/n(o)]:

On confirmation, the process will create the following files:

- `src/<ModuleName>/src/Handler/Get<ModuleName>ViewHandler.php`: renders the resource page
- `src/<ModuleName>/templates/<ModuleName>/<ModuleName>-view.html.twig`: renders the resource

The matching ServiceInterface will be automatically injected into the Handler.

Without confirmation, the process will skip to the next component.

##### Create resources

The prompt asks you whether you want to create resources:

> Allow creating Resources? [Y(es)/n(o)]:

On confirmation, the process will create the following files:

- `src/<ModuleName>/src/Handler/Get<ModuleName>CreateFormHandler.php`: renders the resource creation form page
- `src/<ModuleName>/src/Handler/Post<ModuleName>CreateHandler.php`: handles the resource creation
- `src/<ModuleName>/src/Form/Create<ModuleName>Form.php`: form fields
- `src/<ModuleName>/src/InputFilter/Create<ModuleName>InputFilter.php`: form field validators
- `src/<ModuleName>/templates/<ModuleName>/<ModuleName>-view.html.twig`: renders the resource creation form

The matching Form and ServiceInterface will be automatically injected into the Handler.

Without confirmation, the process will skip to the next component.

##### Delete resources

The prompt asks you whether you want to delete resources:

> Allow deleting Resources? [Y(es)/n(o)]:

On confirmation, the process will create the following files:

- `src/<ModuleName>/src/Handler/Get<ModuleName>DeleteFormHandler.php`: renders the resource deletion form page
- `src/<ModuleName>/src/Handler/Post<ModuleName>DeleteHandler.php`: handles the resource deletion
- `src/<ModuleName>/src/Form/Delete<ModuleName>Form.php`: form fields
- `src/<ModuleName>/src/InputFilter/Delete<ModuleName>InputFilter.php`: form field validators
- `src/<ModuleName>/src/InputFilter/Input/ConfirmDelete<ModuleName>Input.php`: checkbox input for deletion confirmation
- `src/<ModuleName>/templates/<ModuleName>/<ModuleName>-delete-form.html.twig`: renders the resource deletion form

The matching ServiceInterface will be automatically injected into the Handler.

Without confirmation, the process will skip to the next component.

##### Edit resources

The prompt asks you whether you want to edit resources:

> Allow editing Resources? [Y(es)/n(o)]:

On confirmation, the process will create the following files:

- `src/<ModuleName>/src/Handler/Get<ModuleName>EditFormHandler.php`: renders the resource edit form page
- `src/<ModuleName>/src/Handler/Post<ModuleName>EditHandler.php`: handles the resource update
- `src/<ModuleName>/src/Form/Edit<ModuleName>Form.php`: form fields
- `src/<ModuleName>/src/InputFilter/Edit<ModuleName>InputFilter.php`: form field validators
- `src/<ModuleName>/templates/<ModuleName>/<ModuleName>-edit-form.html.twig`: renders the resource creation form

The matching InputFilter and ServiceInterface will be automatically injected into the Handler.

Without confirmation, the process will skip to the next component.

### RoutesDelegator

This component is created only if you chose to create request Handlers.
It is generated automatically and contains the basic CRUD operations based on the selected actions during Handler creation.

Location: `src/<ModuleName>/src/RoutesDelegator.php`

### OpenAPI

> This component is created only when a project type is API.

It is generated automatically and contains the basic description of the API endpoints found in this Module.
Once you extend the entities with additional fields, you will need to update the OpenAPI file manually.

Location: `src/<ModuleName>/src/RoutesDelegator.php`

### ConfigProvider

It is generated automatically and contains all the delegators, factories and service aliases for this Module.
Once you add new files that need dependency injection, you will need to update the ConfigProvider manually.

Location: `src/<ModuleName>/src/ConfigProvider.php`

### Core ConfigProvider

> This component is created only when a project uses the Core architecture.

It is generated automatically and contains all the delegators, factories and service aliases for this Module.
Once you add new files that need dependency injection, you will need to update the ConfigProvider manually.

Location: `src/Core/src/<ModuleName>/src/ConfigProvider.php`

### Templates

> This file structure is NOT created when the project type is API.

Location: `src/<ModuleName>/templates/<ModuleName>/*.html.twig`

## Next steps

Once the Module is created, `dot-maker` will display a list of tasks that need to be performed manually:

```text
Next steps:
===========
- add to config/config.php:
  <ProjectType>\<ModuleName>\ConfigProvider,
- add to config/config.php:
  Core\<ModuleName>\ConfigProvider,
- add to composer.json under autoload.psr-4:
  "<ProjectType>\\<ModuleName>\\": "src/<ModuleName>/src/"
- add to composer.json under autoload.psr-4:
  "Core\\<ModuleName>\\": "src/Core/src/<ModuleName>/src/"
- add to config/autoload/cli.global.php under dot_cli.commands:
  <ProjectType>\<ModuleName>\Command\<ModuleName>Command::getDefaultName() => <ProjectType>\<ModuleName>\Command\<ModuleName>Command::class,
- add to config/pipeline.php:
  $app->pipe(<ProjectType>\<ModuleName>\Middleware\<ModuleName>Middleware);
- dump Composer autoloader by executing this command:
  composer dump
- generate Doctrine migration:
  php ./vendor/bin/doctrine-migrations diff
- Start adding logic to the new module files.
```

where `<ProjectType>` is the project type (**API**, **Admin**, **Frontend**, **Light** or **Queue**) and `<ModuleName>` is the name of the Module.
