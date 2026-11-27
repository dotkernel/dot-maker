# Create Entity

> This page assumes that you have created a Composer "make" script as described on the [Setup page](../setup.md#add-dot-maker-to-composerjson).

## Run the command

To create an Entity, use either of the following commands:

```shell
composer make entity
```

OR

```shell
./vendor/bin/dot-maker entity
```

## Identify the target module

`dot-maker` needs to know in which module you want to create the new Entity.
To determine this, it will prompt you to enter the name of an existing module:

> Existing module name:

If you input a module name which does not exist (like, "NonExistentModule"), an error will be thrown:

> Module "NonExistentModule" not found

and will keep prompting for a valid module name until you provide one.

Once an existing module name (like, "ExistingModule") is provided, `dot-maker` will output a success message:

> Found Module "ExistingModule"

## Name the Entity

Once the target module has been identified, you will be prompted to input a name for the Entity:

> Entity name:

**The name must start with a letter, contain only letters and numbers, and must not be a PHP reserved word (for example `List`, `Class` or `Int`), with or without the component suffix.**

> You don't have to append "Entity" to the name.
> It is automatically appended when necessary.
> See our [Naming Standards](../naming-standards.md) page for more information.

If you leave the name blank, the process will exit.

If you input an invalid name (like, "."), an error will be thrown:

> Invalid Entity name: "."

If you input the name of an existing Entity (like, "Existing"), an error will be thrown:

- for projects compatible with the Core architecture:

> Class "Existing" already exists at /path/to/project/src/Core/src/ExistingModule/src/Entity/Existing.php

- for projects which are NOT compatible with the Core architecture:

> Class "Existing" already exists at /path/to/project/src/ExistingModule/src/Entity/Existing.php

If you input a valid name (like, "Book"), `dot-maker` will create the Entity and output a success message.
The Entity class is named without a suffix, so "BookEntity" also creates `Book.php`:

- for projects compatible with the Core architecture:

> Created Entity: /path/to/project/src/Core/src/ExistingModule/src/Entity/Book.php
> Created Repository: /path/to/project/src/Core/src/ExistingModule/src/Repository/BookRepository.php

- for projects which are NOT compatible with the Core architecture:

> Created Entity: /path/to/project/src/ExistingModule/src/Entity/Book.php
> Created Repository: /path/to/project/src/ExistingModule/src/Repository/BookRepository.php

## Create multiple Entities

To allow the creation of multiple Entities, the process will loop until you leave the name blank.
Each iteration creates a new pair of Entity and Repository under the same module.

## Next steps

Once the Entity is created, `dot-maker` will display the tasks that need to be performed manually, for example:

```text
Next steps:
===========
- generate Doctrine migration:
  php ./vendor/bin/doctrine-migrations diff
```

When you create more than one Entity in the same run, each one is listed.
