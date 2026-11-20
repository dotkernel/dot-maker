# Create Command

> This page assumes that you have created a Composer "make" script as described on the [Setup page](../setup.md#add-dot-maker-to-composerjson).

> `dot-maker` will look for a matching ServiceInterface in the module (e.g.: BookServiceInterface — BookCommand).
> If it finds one, it will automatically inject it into the Command.

To create a Command, use either of the following commands:

## Run the command

```shell
composer make command
```

OR

```shell
./vendor/bin/dot-maker command
```

## Identify the target module

`dot-maker` needs to know in which module you want to create the new Command.
To determine this, it will prompt you to enter the name of an existing module:

> Existing module name:

If you input a module name which does not exist (like, "NonExistentModule"), an error will be thrown:

> Module "NonExistentModule" not found

and will keep prompting for a valid module name until you provide one.

Once an existing module name (like, "ExistingModule") is provided, `dot-maker` will output a success message:

> Found Module "ExistingModule"

## Name the Command

Once the target module has been identified, you will be prompted to input a name for the Command:

> Command name:

**The name must start with a letter, contain only letters and numbers, and must not be a PHP reserved word (for example `List`, `Class` or `Int`), with or without the component suffix.**

> You don't have to append "Command" to the name.
> It is automatically appended when necessary.
> See our [Naming Standards](../naming-standards.md) page for more information.

If you leave the name blank, the process will exit.

If you input an invalid name (like, "."), an error will be thrown:

> Invalid Command name: "."

If you input the name of an existing Command (like, "ExistingCommand"), an error will be thrown:

> Class "ExistingCommand" already exists at /path/to/project/src/ExistingModule/src/Command/ExistingCommand.php

If you input a valid name (like, "NewCommand"), `dot-maker` will create the Command and output a success message:

> Created Command: /path/to/project/src/ExistingModule/src/Command/NewCommand.php

## Create multiple Commands

To allow the creation of multiple Commands, the process will loop until you leave the name blank.
Each iteration creates a new Command under the same module.

## Command name

The console name of the Command is built from its class name: the class name is converted to kebab-case, a trailing `-command` is removed and `:command` is appended.

| You input | Class | Console name |
| --- | --- | --- |
| `BookStore` | `BookStoreCommand` | `book-store:command` |
| `ImportCommands` | `ImportCommandsCommand` | `import-commands:command` |
| `MyCommandHandler` | `MyCommandHandlerCommand` | `my-command-handler:command` |

## Next steps

Once the Command is created, `dot-maker` will display the tasks that need to be performed manually, for example:

```text
Next steps:
===========
- add to config/autoload/cli.global.php under dot_cli.commands:
  <ProjectType>\ExistingModule\Command\NewCommand::getDefaultName() => <ProjectType>\ExistingModule\Command\NewCommand::class,
```

When you create more than one Command in the same run, each one is listed.
