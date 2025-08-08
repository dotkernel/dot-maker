# Create Collection

> This page assumes that you have created a Composer "make" script as described on the [Setup page](../setup.md#add-dot-maker-to-composerjson).

> Collections can be created only in APIs.

## Run the command

To create a Collection, use either of the following commands:

```shell
composer make collection
```

OR

```shell
./vendor/bin/dot-maker collection
```

## Identify the target module

`dot-maker` needs to know in which module you want to create the new Collection.
To determine this, it will prompt you to enter the name of an existing module:

> Existing module name:

If you input a module name which does not exist (like, "NonExistentModule"), an error will be thrown:

> Module "NonExistentModule" not found

and will keep prompting for a valid module name until you provide one.

Once an existing module name (like, "ExistingModule") is provided, `dot-maker` will output a success message:

> Found Module "ExistingModule"

## Name the Collection

Once the target module has been identified, you will be prompted to input a name for the Collection:

> Collection name:

**The name must contain only letters and numbers.**

> You don't have to append "Collection" to the name. It is automatically appended. See our [Naming Standards](../naming-standards.md) page for more information.

If you leave the name blank, the process will exit.

If you input an invalid name (like, "."), an error will be thrown:

> Invalid Collection name: "."

If you input the name of an existing Collection (like, "ExistingCollection"), an error will be thrown:

> Class "ExistingCollection" already exists at /path/to/project/src/ExistingModule/src/Collection/ExistingCollection.php

If you input a valid name (like, "NewCollection"), `dot-maker` will create the Collection and output a success message:

> Created Collection: /path/to/project/src/ExistingModule/src/Collection/NewCollection.php

## Create multiple Collections

To allow the creation of multiple Collections, the process will loop until you leave the name blank.
Each iteration creates a new Collection under the same module.
