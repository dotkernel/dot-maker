# Create Input

> This page assumes that you have created a Composer "make" script as described on the [Setup page](../setup.md#add-dot-maker-to-composerjson).

To create an Input, use either of the following commands:

## Run the command

```shell
composer make input
```

OR

```shell
./vendor/bin/dot-maker input
```

## Identify the target module

`dot-maker` needs to know in which module you want to create the new Input.
To determine this, it will prompt you to enter the name of an existing module:

> Existing module name:

If you input a module name which does not exist (like, "NonExistentModule"), an error will be thrown:

> Module "NonExistentModule" not found

and will keep prompting for a valid module name until you provide one.

Once an existing module name (like, "ExistingModule") is provided, `dot-maker` will output a success message:

> Found Module "ExistingModule"

## Name the Input

Once the target module has been identified, you will be prompted to input a name for the Input:

> Input name:

**The name must contain only letters and numbers.**

> You don't have to append "Input" to the name.
> It is automatically appended when necessary.
> See our [Naming Standards](../naming-standards.md) page for more information.

If you leave the name blank, the process will exit.

If you input an invalid name (like, "."), an error will be thrown:

> Invalid Input name: "."

If you input the name of an existing Input (like, "ExistingInput"), an error will be thrown:

> Class "ExistingInput" already exists at /path/to/project/src/ExistingModule/src/InputFilter/Input/ExistingInput.php

If you input a valid name (like, "NewInput"), `dot-maker` will create the Input and output a success message:

> Created Input: /path/to/project/src/ExistingModule/src/InputFilter/Input/NewInput.php

## Create multiple Inputs

To allow the creation of multiple Inputs, the process will loop until you leave the name blank.
Each iteration creates a new Input under the same module.
