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

**The name must contain only letters and numbers.**

> You don't have to append "Entity" to the name. See our [Naming Standards](../naming-standards.md) page for more information.

If you leave the name blank, the process will exit.

If you input an invalid name (like, "."), an error will be thrown:

> Invalid Entity name: "."

If you input the name of an existing Entity (like, "ExistingEntity"), an error will be thrown:

- for projects compatible with the Core architecture:

> Class "ExistingEntity" already exists at /path/to/project/src/Core/src/ExistingModule/src/Entity/ExistingEntity.php

- for projects which are NOT compatible with the Core architecture:

> Class "ExistingEntity" already exists at /path/to/project/src/ExistingModule/src/Entity/ExistingEntity.php

If you input a valid name (like, "NewEntity"), `dot-maker` will create the Entity and output a success message:

- for projects compatible with the Core architecture:

> Created Entity: /path/to/project/src/Core/src/ExistingModule/src/Entity/NewEntity.php
> Created Repository: /path/to/project/src/Core/src/ExistingModule/src/Repository/NewEntityRepository.php

- for projects which are NOT compatible with the Core architecture:

> Created Entity: /path/to/project/src/ExistingModule/src/Entity/NewEntity.php
> Created Repository: /path/to/project/src/ExistingModule/src/Repository/NewEntityRepository.php

## Create multiple Entities

To allow the creation of multiple Entities, the process will loop until you leave the name blank.
Each iteration creates a new pair of Entity and Repository under the same module.
