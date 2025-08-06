# Create Command

To create a Command, use either of the following commands:

```shell
composer make command
```

OR

```shell
./vendor/bin/dot-maker command
```

`dot-maker` needs to determine in which module you want to create the new Command.
To determine this, it will prompt you to enter the name of an existing module:

> Existing module name:

If you input a module name which does not exist (like, "NonExistentModule"), an error will be thrown:

> Module "NonExistentModule" not found

and will keep prompting for a valid module name until you provide one.

---

Once the target module has been identified, you will be prompted to input a name for the Command:

> Command name:

**The name must contain only letters and numbers.**

If you leave the name blank, the process will exit.

If you input an invalid name (like, "."), an error will be thrown:

> Invalid Command name: "."

If you input the name of an existing Command (like, "ExistingCommand"), an error will be thrown:

> Class "ExistingCommand" already exists at /path/to/project/src/ExistingModule/src/Command/ExistingCommand.php

If you input a valid name, `dot-maker` will create the Command and output a success message:

> Created Collection: /path/to/project/src/ExistingModule/src/Command/NewCommand.php

To allow the creation of multiple Commands, the process will loop until you leave the name blank.

> dot-maker will look for a matching ServiceInterface in the module (e.g.: BookServiceInterface — BookCommand).
> If it finds one, it will automatically inject it into the Command.
