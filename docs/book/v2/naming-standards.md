# Naming Standards

Inside the Dotkernel ecosystem, we strictly follow the following standards to make sure that we provide clean and easy-to-follow code:

- [PSR-4](https://www.php-fig.org/psr/psr-4/): the autoloading standard—for easy autoloading of classes.
- [PSR-12](https://www.php-fig.org/psr/psr-12/): the extended coding style—for suggestive file names.

## Collections

> Can only be created in API projects.

These are files that are used to contain a set of resources of the same type.

- location: `src/<ModuleName>/src/Collection`
- pattern: _Name_ + `Collection`
- example: `src/<ModuleName>/src/Collection/ExampleCollection.php`

## Commands

These are CLI scripts that are used to perform a specific task.

- location: `src/<ModuleName>/src/Command`
- pattern: _Name_ + `Command`
- example: `src/<ModuleName>/src/Command/ExampleCommand.php`

Core-compatible projects MAY also have Core Commands:

- location: `src/Core/src/<ModuleName>/src/Command`
- pattern: _Name_ + `Command`
- example: `src/Core/src/<ModuleName>/src/Command/ExampleCommand.php`

## ConfigProvider

These are configuration classes where aliases, delegators, factories and template paths are registered.

- location: `src/<ModuleName>/src`
- pattern: `ConfigProvider`
- example: `src/<ModuleName>/src/ConfigProvider.php`

Core-compatible projects also have a Core ConfigProvider:

- location: `src/Core/src/<ModuleName>/src`
- pattern: `ConfigProvider`
- example: `src/Core/src/<ModuleName>/src/ConfigProvider.php`

When this file exists, Doctrine entity configurations are stored here.
Else, those too are stored in `src/<ModuleName>/src/ConfigProvider.php`.

## Entities

These are classes that represent a single resource and are used to store and retrieve data in a database.

- location: `src/Core/src/ModuleName/Entity`
- pattern: _Name_
- example: `src/Core/src/ModuleName/Entity/Example.php`

### Legacy projects

- location: `src/<ModuleName>/src/Entity`
- pattern: _Name_
- example: `src/<ModuleName>/src/Entity/Example.php`

## Factories

These are classes responsible for instantiating specific objects.

- location: `src/<ModuleName>/src/Factory`
- pattern: _Name_ + `Factory`
- example: `src/<ModuleName>/src/Factory/ExampleFactory.php`

Core-compatible projects MAY also have Core Factories:

- location: `src/Core/src/<ModuleName>/src/Factory`
- pattern: _Name_ + `Factory`
- example: `src/Core/src/<ModuleName>/src/Factory/ExampleFactory.php`

## Fixtures

These are classes used to populate the database with test data.

- location: `src/Core/src/App/src/Fixture`
- pattern: _Name_ + `Loader`
- example: `src/Core/src/App/src/Fixture/ExampleLoader.php`

## Forms

> Cannot be created in API projects.

These are objectual representations of an HTML form.

- location: `src/<ModuleName>/src/Form`
- pattern: _Name_ + `Form`
- example: `src/<ModuleName>/src/Form/ExampleForm.php`

## Handlers

These are classes responsible for handling a specific HTTP request.

- location: `src/<ModuleName>/src/Handler/<ResourceName>`
- pattern: _Verb_ + _Name_ + _Action_ + `Handler`
- example: `src/<ModuleName>/src/Handler/<ResourceName>Post<ResourceName>CreateHandler.php`

### API projects

- location: `src/<ModuleName>/src/Handler/<ResourceName>`
- pattern:
    - resources: _Verb_ + _Name_ + `ResourceHandler`
    - collections: _Verb_ + _Name_ + `CollectionHandler`
- example:
    - resources: `src/<ModuleName>/src/Handler/<ResourceName>/Create<ResourceName>ResourceHandler.php`
    - collections: `src/<ModuleName>/src/Handler/<ResourceName>/Get<ResourceName>CollectionHandler.php`

## Inputs

These are objectual representations of an HTML form input.

- location: `src/<ModuleName>/src/InputFilter/Input`
- pattern: _Name_ + `Input`
- example: `src/<ModuleName>/src/InputFilter/Input/ExampleInput.php`

Core-compatible projects MAY also have Core Inputs:

- location: `src/Core/src/<ModuleName>/src/InputFilter/Input`
- pattern: _Name_ + `Input`
- example: `src/Core/src/<ModuleName>/src/InputFilter/Input/ExampleInput.php`

## InputFilters

These are classes that are used to validate and filter form data.

- location: `src/<ModuleName>/src/InputFilter`
- pattern: _Name_ + `InputFilter`
- example: `src/<ModuleName>/src/InputFilter/ExampleInputFilter.php`

Core-compatible projects MAY also have Core InputFilters:

- location: `src/Core/src/<ModuleName>/src/InputFilter`
- pattern: _Name_ + `InputFilter`
- example: `src/Core/src/<ModuleName>/src/InputFilter/ExampleInputFilter.php`

## Middleware

These are classes that can be used to perform actions before and after a request has been handled.

- location: `src/<ModuleName>/src/Middleware`
- pattern: _Name_ + `Middleware`
- example: `src/<ModuleName>/src/Middleware/ExampleMiddleware.php`

Core-compatible projects MAY also have Core Middleware:

- location: `src/Core/src/<ModuleName>/src/Middleware`
- pattern: _Name_ + `Middleware`
- example: `src/Core/src/<ModuleName>/src/Middleware/ExampleMiddleware.php`

## Migrations

These are classes used to modify the database schema.

- location: `src/Core/src/App/src/Migration`
- pattern: `Version` + _Timestamp_
- example: `src/Core/src/App/src/Migration/Version20250407142911.php`

## OpenApi

> Can only be created in API projects.

These are configuration classes where OpenApi specifications are registered.

- location: `src/<ModuleName>/src`
- pattern: `OpenApi`
- example: `src/<ModuleName>/src/OpenApi.php`

## Repositories

These are classes that represent an intermediary layer between the database and the entities.

- location: `src/Core/src/ModuleName/Repository`
- pattern: _Name_ + `Repository`
- example: `src/Core/src/ModuleName/Repository/ExampleRepository.php`

### Legacy projects

- location: `src/<ModuleName>/src/Repository`
- pattern: _Name_ + `Repository`
- example: `src/<ModuleName>/src/Repository/ExampleRepository.php`

## RoutesDelegator

These are configuration classes where HTTP request routes are registered.

- location: `src/<ModuleName>/src`
- pattern: `RoutesDelegator`
- example: `src/<ModuleName>/src/RoutesDelegator.php`

## Services

These are classes that contain task-specific methods to be reused across the application.

- location: `src/<ModuleName>/src/Service`
- pattern: _Name_ + `Service`
- example: `src/<ModuleName>/src/Service/ExampleService.php`

Core-compatible projects MAY also have Core Services:

- location: `src/Core/src/<ModuleName>/src/Service`
- pattern: _Name_ + `Service`
- example: `src/Core/src/<ModuleName>/src/Service/ExampleService.php`

## ServiceInterfaces

These are interfaces that serve as contracts for services.

- location: `src/<ModuleName>/src/ServiceInterface`
- pattern: _Name_ + `ServiceInterface`
- example: `src/<ModuleName>/src/Service/ExampleServiceInterface.php`

Core-compatible projects MAY also have Core ServiceInterfaces:

- location: `src/Core/src/<ModuleName>/src/Service`
- pattern: _Name_ + `Service`
- example: `src/Core/src/<ModuleName>/src/Service/ExampleServiceInterface.php`
