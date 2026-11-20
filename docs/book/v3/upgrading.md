# Upgrading from 2.x to 3.x

`dot-maker` 3.x supports PHP 8.3, 8.4, 8.5 and 8.6.
Update the constraint in your project's `composer.json`:

```shell
composer require --dev dotkernel/dot-maker:^3.0
```

## Changes that can affect you

| Change | Symptom | Fix |
| --- | --- | --- |
| Stricter names | `Invalid ... name` for names such as `1Book`, `List`, `Class` or `ListEntity` | Use a name that starts with a letter and is not a PHP reserved word, with or without the component suffix |
| A name that is only the suffix keeps it | `Entity` creates the Entity `Entity` instead of a file called `.php` | Nothing to do; the old behaviour produced a broken file |
| End of input at the module prompt | Piped input that ends before the module name stops with "No module name given before the end of input." and exit code 1 | Provide the module name in the input |
| Exit codes | Scripts that ignored failures now fail, because `dot-maker` exits with 1 on errors | Check the exit code, or fix the reported error |
| Command console name | A Command whose name contains `Command` before the end (for example `MyCommandHandler`) gets a different `name:` | Rename the generated name only if you depend on the old one |
| Template variable in GET form handlers | Generated `GetEdit...FormHandler` and `GetDelete...FormHandler` pass the entity as `bookStore` instead of `book-store` | Use the camelCase variable in your templates, as the POST handlers already do |
| Generated array alignment | Generated arrays are aligned to the Laminas Coding Standard | Nothing to do; `composer cs-check` passes on generated files |
| Forms in API projects | `dot-maker form` in an API project stops before the module prompt, with exit code 1 | Nothing to do; Forms are not created in APIs |
| `composer.json` detection | `RuntimeException` when `autoload.psr-4` has no namespace outside `Core\` | Register your application module under `autoload.psr-4` |

## Changes for code that extends dot-maker

These only matter if you wrote your own classes on top of `dot-maker`.

| Change | Symptom | Fix |
| --- | --- | --- |
| `FileInterface::create()` returns `File` instead of `?File` | Fatal error in a class that implements `FileInterface` and declares `?File` | Declare and return `File` |
| `Context::getRootNamespace()` and `Context::getProjectType()` return `string` | Static analysis reports always-true null checks | Remove the null checks |
| `Module::getMessages()` returns `array<int, array<string, string>>` | Code that reads the messages gets arrays instead of strings | Iterate the nested arrays (priority, then message) |
| `Component` rejects an empty class name | `InvalidArgumentException` | Pass a non-empty class name |
| `FileSystem::getModuleName()` before `setModuleName()` | `RuntimeException('No module selected.')` instead of an engine `Error` | Call `setModuleName()` first |
| `Input::isExhausted()` added | None | Use it to detect the end of input in custom prompts |
| `Entity::getNumericIdentifierTraitFqcn()` removed (it was never called) | Fatal error "Call to undefined method" | Use `sprintf(Import::ROOT_APP_ENTITY_NUMERICIDENTIFIERTRAIT, $namespace)` |
| `FileSystem::coreModule()` removed (it was never called) | Fatal error "Call to undefined method" | Create the `Directory` yourself: `new Directory($name, $projectPath . '/src/Core/src')` |
