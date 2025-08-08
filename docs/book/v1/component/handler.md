# Create Handler

> This page assumes that you have created a Composer "make" script as described on the [Setup page](../setup.md#add-dot-maker-to-composerjson).

> `dot-maker` will look for a matching ServiceInterface in the module (e.g.: BookServiceInterface — BookHandler).
> If it finds one, it will automatically inject it into the Handler.

To create a Handler, use either of the following commands:

## Run the command

```shell
composer make handler
```

OR

```shell
./vendor/bin/dot-maker handler
```

## Identify the target module

`dot-maker` needs to know in which module you want to create the new Handler.
To determine this, it will prompt you to enter the name of an existing module:

> Existing module name:

If you input a module name which does not exist (like, "NonExistentModule"), an error will be thrown:

> Module "NonExistentModule" not found

and will keep prompting for a valid module name until you provide one.

Once an existing module name (like, "ExistingModule") is provided, `dot-maker` will output a success message:

> Found Module "ExistingModule"

## Name the Handler

Once the target module has been identified, you will be prompted to input a name for the Handler:

> Handler name:

**The name must contain only letters and numbers.**

> You don't have to append "Handler" to the name.
> It is automatically appended when necessary.
> See our [Naming Standards](../naming-standards.md) page for more information.

If you leave the name blank, the process will exit.

If you input an invalid name (like, "."), an error will be thrown:

> Invalid Handler name: "."

If you input the name of an existing Handler (like, "ExistingHandler"), an error will be thrown:

> Class "ExistingHandler" already exists at /path/to/project/src/ExistingModule/src/Handler/ExistingHandler.php

Once you input a valid name (like, "Book"), the process will iterate over a list of CRUD operations, asking you to confirm whether the Module needs a request Handler for each operation.

### API projects

#### List resources

The prompt asks you whether you want to list resources:

> Allow listing Resources? [Y(es)/n(o)]:

On confirmation, the process will create the following files:

- `src/<ModuleName>/src/Collection/<ModuleName>Collection.php`: describes a resource-specific collection
- `src/<ModuleName>/src/Handler/Get<ModuleName>CollectionHandler.php`: handles the resource collection representation

The matching Collection and ServiceInterface will be automatically injected into the Handler.

Without confirmation, the process will skip to the next component.

#### View resources

The prompt asks you whether you want to view resources:

> Allow viewing Resources? [Y(es)/n(o)]:

On confirmation, the process will create the following file:

- `src/<ModuleName>/src/Handler/Get<ModuleName>ResourceHandler.php`: handles the resource representation

The matching ServiceInterface will be automatically injected into the Handler.

Without confirmation, the process will skip to the next component.

#### Create resources

The prompt asks you whether you want to create resources:

> Allow creating Resources? [Y(es)/n(o)]:

On confirmation, the process will create the following files:

- `src/<ModuleName>/src/Handler/Post<ModuleName>ResourceHandler.php`: handles the resource creation
- `src/<ModuleName>/src/InputFilter/Create<ModuleName>InputFilter.php`: request payload validators

The matching InputFilter and ServiceInterface will be automatically injected into the Handler.

Without confirmation, the process will skip to the next component.

#### Delete resources

The prompt asks you whether you want to delete resources:

> Allow deleting Resources? [Y(es)/n(o)]:

On confirmation, the process will create the following files:

- `src/<ModuleName>/src/Handler/Delete<ModuleName>ResourceHandler.php`: handles the resource deletion

The matching ServiceInterface will be automatically injected into the Handler.

Without confirmation, the process will skip to the next component.

#### Edit resources

The prompt asks you whether you want to edit resources:

> Allow editing Resources? [Y(es)/n(o)]:

On confirmation, the process will create the following files:

- `src/<ModuleName>/src/Handler/Patch<ModuleName>ResourceHandler.php`: handles the resource update
- `src/<ModuleName>/src/InputFilter/Edit<ModuleName>InputFilter.php`: request payload validators

The matching InputFilter and ServiceInterface will be automatically injected into the Handler.

Without confirmation, the process will skip to the next component.

#### Replace resources

The prompt asks you whether you want to replace resources:

> Allow replacing Resources? [Y(es)/n(o)]:

On confirmation, the process will create the following files:

- `src/<ModuleName>/src/Handler/Put<ModuleName>ResourceHandler.php`: handles the resource replacement
- `src/<ModuleName>/src/InputFilter/Replace<ModuleName>InputFilter.php`: request payload validators

The matching InputFilter and ServiceInterface will be automatically injected into the Handler.

Without confirmation, the process will skip to the next component.

### Non-API projects

#### List resources

The prompt asks you whether you want to list resources:

> Allow listing Resources? [Y(es)/n(o)]:

On confirmation, the process will create the following files:

- `src/<ModuleName>/src/Handler/Get<ModuleName>ListHandler.php`: renders the resource list page
- `src/<ModuleName>/templates/<ModuleName>/<ModuleName>-list.html.twig`: renders the resource list

The matching ServiceInterface will be automatically injected into the Handler.

Without confirmation, the process will skip to the next component.

#### View resources

The prompt asks you whether you want to view resources:

> Allow viewing Resources? [Y(es)/n(o)]:

On confirmation, the process will create the following files:

- `src/<ModuleName>/src/Handler/Get<ModuleName>ViewHandler.php`: renders the resource page
- `src/<ModuleName>/templates/<ModuleName>/<ModuleName>-view.html.twig`: renders the resource

The matching ServiceInterface will be automatically injected into the Handler.

Without confirmation, the process will skip to the next component.

#### Create resources

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

#### Delete resources

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

#### Edit resources

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

## Create multiple Handlers

To allow the creation of multiple Handlers, the process will loop until you leave the name blank.
Each iteration creates a new set of Handlers(, Forms), InputFilters and Inputs under the same module.
