# Create Middleware

To create a Middleware, use either of the following commands:

```shell
composer make middleware
```

OR

```shell
./vendor/bin/dot-maker middleware
```

`dot-maker` needs to determine in which module you want to create the new Middleware.
To determine this, it will prompt you to enter the name of an existing module:

> Existing module name:

If you input a module name which does not exist (like, "NonExistentModule"), an error will be thrown:

> Module "NonExistentModule" not found

and will keep prompting for a valid module name until you provide one.

---

Once the target module has been identified, you will be prompted to input a name for the Middleware:

> Middleware name:

**The name must contain only letters and numbers.**

If you leave the name blank, the process will exit.

If you input an invalid name (like, "."), an error will be thrown:

> Invalid Middleware name: "."

If you input the name of an existing Middleware (like, "ExistingMiddleware"), an error will be thrown:

> Class "ExistingMiddleware" already exists at /path/to/project/src/ExistingModule/src/Middleware/ExistingMiddleware.php

If you input a valid name, `dot-maker` will create the Command and output a success message:

> Created Middleware: /path/to/project/src/ExistingModule/src/Middleware/NewMiddleware.php

To allow the creation of multiple Middleware, the process will loop until you leave the name blank.
