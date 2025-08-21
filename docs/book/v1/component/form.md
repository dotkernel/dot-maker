# Create Form

> This page assumes that you have created a Composer "make" script as described on the [Setup page](../setup.md#add-dot-maker-to-composerjson).

> Forms cannot be created in APIs.

To create a Form, use either of the following commands:

## Run the command

```shell
composer make form
```

OR

```shell
./vendor/bin/dot-maker form
```

## Identify the target module

`dot-maker` needs to know in which module you want to create the new Form.
To determine this, it will prompt you to enter the name of an existing module:

> Existing module name:

If you input a module name which does not exist (like, "NonExistentModule"), an error will be thrown:

> Module "NonExistentModule" not found

and will keep prompting for a valid module name until you provide one.

Once an existing module name (like, "ExistingModule") is provided, `dot-maker` will output a success message:

> Found Module "ExistingModule"

## Name the Form

Once the target module has been identified, you will be prompted to input a name for the Form:

> Form name:

**The name must contain only letters and numbers.**

> You don't have to append "Form" to the name.
> It is automatically appended when necessary.
> See our [Naming Standards](../naming-standards.md) page for more information.

If you leave the name blank, the process will exit.

If you input an invalid name (like, "."), an error will be thrown:

> Invalid Form name: "."

If you input the name of an existing Form (like, "ExistingForm"), an error will be thrown:

> Class "ExistingForm" already exists at /path/to/project/src/ExistingModule/src/Form/ExistingForm.php

Once you input a valid name (like, "Book"), `dot-maker` will prompt you to confirm which CRUD operations will be performed on the resource:

### Create resources

The prompt asks you whether you want to create resources:

> Allow creating Resources? [Y(es)/n(o)]:

On confirmation, the process will create the following files:

- `src/ExistingModule/src/Form/CreateBookForm.php`
- `src/ExistingModule/src/InputFilter/CreateBookInputFilter.php`

Without confirmation, the process will skip to the next component.

### Delete resources

The prompt asks you whether you want to delete resources:

> Allow deleting Resources? [Y(es)/n(o)]:

On confirmation, the process will create the following files:

- `src/ExistingModule/src/Form/DeleteBookForm.php`
- `src/ExistingModule/src/InputFilter/DeleteBookInputFilter.php`
- `src/ExistingModule/src/InputFilter/Input/ConfirmDeleteBookInput.php`

Without confirmation, the process will skip to the next component.

### Edit resources

The prompt asks you whether you want to edit resources:

> Allow editing Resources? [Y(es)/n(o)]:

On confirmation, the process will create the following files:

- `src/ExistingModule/src/Form/EditBookForm.php`
- `src/ExistingModule/src/InputFilter/EditBookInputFilter.php`

Without confirmation, the process will skip to the next component.

## Create multiple Forms

To allow the creation of multiple Forms, the process will loop until you leave the name blank.
Each iteration creates a new set of Forms, InputFilter and Inputs under the same module.
