# Create Repository

> This page assumes that you have created a Composer "make" script as described on the [Setup page](../setup.md#add-dot-maker-to-composerjson).

## Run the command

To create a Repository, use either of the following commands:

```shell
composer make repository
```

OR

```shell
./vendor/bin/dot-maker repository
```

## Identify the target module

`dot-maker` needs to know in which module you want to create the new Repository.
To determine this, it will prompt you to enter the name of an existing module:

> Existing module name:

If you input a module name which does not exist (like, "NonExistentModule"), an error will be thrown:

> Module "NonExistentModule" not found

and will keep prompting for a valid module name until you provide one.

Once an existing module name (like, "ExistingModule") is provided, `dot-maker` will output a success message:

> Found Module "ExistingModule"

## Name the Repository

Once the target module has been identified, you will be prompted to input a name for the Repository:

> Repository name:

**The name must contain only letters and numbers.**

> You don't have to append "Repository" to the name.
> It is automatically appended when necessary.
> See our [Naming Standards](../naming-standards.md) page for more information.

If you leave the name blank, the process will exit.

If you input an invalid name (like, "."), an error will be thrown:

> Invalid Repository name: "."

If you input the name of an existing Repository (like, "ExistingRepository"), an error will be thrown:

- for projects compatible with the Core architecture:

> Class "ExistingRepository" already exists at /path/to/project/src/Core/src/ExistingModule/src/Repository/ExistingRepository.php

- for projects which are NOT compatible with the Core architecture:

> Class "ExistingRepository" already exists at /path/to/project/src/ExistingModule/src/Repository/ExistingRepository.php

If you input a valid name (like, "NewRepository"), `dot-maker` will create the Repository and output a success message:

- for projects compatible with the Core architecture:

> Created Entity: /path/to/project/src/Core/src/ExistingModule/src/Entity/NewEntity.php
> Created Repository: /path/to/project/src/Core/src/ExistingModule/src/Repository/NewEntityRepository.php

- for projects which are NOT compatible with the Core architecture:

> Created Entity: /path/to/project/src/ExistingModule/src/Entity/NewEntity.php
> Created Repository: /path/to/project/src/ExistingModule/src/Repository/NewEntityRepository.php

## Create multiple Repositories

To allow the creation of multiple Repositories, the process will loop until you leave the name blank.
Each iteration creates a new pair of Entity and Repository under the same module.
