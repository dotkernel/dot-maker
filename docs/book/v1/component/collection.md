# Create Collection

> Collections can be created only in APIs.

To create a collection, use either of the following commands:

```shell
composer make collection
```

OR

```shell
./vendor/bin/dot-maker collection
```

The command must identify in which module you want to create the new collection.
To determine this, it will prompt you to enter the name of an existing module:

> Existing module name:

If you input a module name which does not exist (like, "NonExistentModule"), the command throws an error:

> Module "NonExistentModule" not found

and will keep prompting for a valid module name until you provide one.

---

Once the target module has been identified, the command will prompt you to input a name for the collection:

> Collection name:

**The name must contain only letters and numbers.**

If you leave the name blank, the command will exit.

If you input an invalid name (like, "."), the command throws an error:

> Invalid Collection name: "."

If you input the name of an existing collection (like, "ExistingCollection"), the command throws an error:

> Class "ExistingCollection" already exists at /path/to/project/src/ExistingModule/src/Collection/ExistingCollection.php

If you input a valid name, the command will create the collection and output a success message:

> Created Collection: /path/to/project/src/ExistingModule/src/Collection/NewCollection.php

To allow the creation of multiple collections, the command will loop until you leave the name blank.
