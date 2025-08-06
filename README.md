# dot-maker

Dotkernel library for programmatically generating structured code files.

## Documentation

Documentation is available at: https://docs.dotkernel.org/dot-maker/.

## Badges

![OSS Lifecycle](https://img.shields.io/osslifecycle/dotkernel/dot-maker)
![PHP from Packagist (specify version)](https://img.shields.io/packagist/php-v/dotkernel/dot-maker/1.0)

[![GitHub issues](https://img.shields.io/github/issues/dotkernel/dot-maker)](https://github.com/dotkernel/dot-maker/issues)
[![GitHub forks](https://img.shields.io/github/forks/dotkernel/dot-maker)](https://github.com/dotkernel/dot-maker/network)
[![GitHub stars](https://img.shields.io/github/stars/dotkernel/dot-maker)](https://github.com/dotkernel/dot-maker/stargazers)
[![GitHub license](https://img.shields.io/github/license/dotkernel/dot-maker)](https://github.com/dotkernel/dot-maker/blob/1.0/LICENSE.md)

[![Build Static](https://github.com/dotkernel/dot-maker/actions/workflows/continuous-integration.yml/badge.svg?branch=1.0)](https://github.com/dotkernel/dot-maker/actions/workflows/continuous-integration.yml)
[![codecov](https://codecov.io/gh/dotkernel/dot-maker/graph/badge.svg?token=KT9UA402B4)](https://codecov.io/gh/dotkernel/dot-maker)
[![PHPStan](https://github.com/dotkernel/dot-maker/actions/workflows/static-analysis.yml/badge.svg?branch=1.0)](https://github.com/dotkernel/dot-maker/actions/workflows/static-analysis.yml)

## Installation

Run the following command in your terminal:

```shell
composer require-dev dotkernel/dot-maker
```

## Setup

Once installed, `dot-maker` is ready for usage, no need for extra configurations.

### (Optional) Add `dot-maker` to composer.json

Open `composer.json` and locate the `scripts` section.
If it does not exist, create it at the document's root level.

Register a new script by appending `"alias": "dot-maker"` to the `scripts` section, where **alias** can be any string you want; like, for example **make**.

```json
{
    "scripts": {
        "make": "dot-maker"
    }
}
```

## Usage

Invoke `dot-maker` by executing:

- the bin file in your vendor directory, located at `./vendor/bin/dot-maker <component>`
- the (optional) Composer script created at [Setup](#setup): `composer make <component>`

where `<component>` is one of the following strings:

- `collection`: Creates a resource Collection (only when API)
- `command`: Creates a CLI Command
- `entity`: Creates a Doctrine Entity and the matching Repository
- `form`: Creates a Laminas Form and the matching InputFilter (except when API)
- `handler`: Creates the specified request Handlers (and additional files, based on the project type)
- `input-filter`: Creates an InputFilter
- `middleware`: Creates a Middleware
- `module`: Creates an entire Module
- `repository`: Creates a Doctrine Repository and the matching Entity
- `service`: Creates a Service and the matching ServiceInterface

### Create Collection

```shell
./vendor/bin/dot-maker collection
```

### Create Command

```shell
./vendor/bin/dot-maker command
```

### Create Entity + Repository

```shell
./vendor/bin/dot-maker entity
```

### Create Form

```shell
./vendor/bin/dot-maker form
```

### Create Handler

```shell
./vendor/bin/dot-maker handler
```

### Create InputFilter

```shell
./vendor/bin/dot-maker input-filter
```

### Create Middleware

```shell
./vendor/bin/dot-maker middleware
```

### Create Module

```shell
./vendor/bin/dot-maker module
```

### Create Repository + Entity

```shell
./vendor/bin/dot-maker repository
```

### Create Service + ServiceInterface

```shell
./vendor/bin/dot-maker service
```
