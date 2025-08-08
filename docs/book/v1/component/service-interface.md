# Create ServiceInterface

> This page assumes that you have created a Composer "make" script as described on the [Setup page](../setup.md#add-dot-maker-to-composerjson).

> `dot-maker` will look for a matching Repository in the module (e.g.: BookRepository — BookService).
> If it finds one, it will automatically inject it into the Service.
> Also, some common methods will be added to the Service and the ServiceInterface.

## Run the command

To create a ServiceInterface, use either of the following commands:

```shell
composer make service-interface
```

OR

```shell
./vendor/bin/dot-maker service-interface
```

## Identify the target module

`dot-maker` needs to know in which module you want to create the new ServiceInterface.
To determine this, it will prompt you to enter the name of an existing module:

> Existing module name:

If you input a module name which does not exist (like, "NonExistentModule"), an error will be thrown:

> Module "NonExistentModule" not found

and will keep prompting for a valid module name until you provide one.

Once an existing module name (like, "ExistingModule") is provided, `dot-maker` will output a success message:

> Found Module "ExistingModule"

## Name the ServiceInterface

Once the target module has been identified, you will be prompted to input a name for the ServiceInterface:

> ServiceInterface name:

**The name must contain only letters and numbers.**

> You don't have to append "ServiceInterface" to the name. It is automatically appended. See our [Naming Standards](../naming-standards.md) page for more information.

If you leave the name blank, the process will exit.

If you input an invalid name (like, "."), an error will be thrown:

> Invalid ServiceInterface name: "."

If you input the name of an existing ServiceInterface (like, "ExistingServiceInterface"), an error will be thrown:

> Class "ExistingServiceInterface" already exists at /path/to/project/src/ExistingModule/src/Service/ExistingServiceInterface.php

If you input a valid name (like, "NewServiceInterface"), `dot-maker` will create the ServiceInterface and output a success message:

> Created ServiceInterface: /path/to/project/src/ExistingModule/src/Service/NewServiceInterface.php
> Created Service: /path/to/project/src/ExistingModule/src/Service/NewService.php

## Create multiple ServiceInterfaces

To allow the creation of multiple ServiceInterfaces, the process will loop until you leave the name blank.
Each iteration creates a new pair of Service and ServiceInterface under the same module.
