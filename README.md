# dot-maker

Dotkernel library for programmatically generating structured code files.

![OSS Lifecycle](https://img.shields.io/osslifecycle/dotkernel/dot-maker)
![PHP from Packagist (specify version)](https://img.shields.io/packagist/php-v/dotkernel/dot-maker/1.0)

[![GitHub issues](https://img.shields.io/github/issues/dotkernel/dot-maker)](https://github.com/dotkernel/dot-maker/issues)
[![GitHub forks](https://img.shields.io/github/forks/dotkernel/dot-maker)](https://github.com/dotkernel/dot-maker/network)
[![GitHub stars](https://img.shields.io/github/stars/dotkernel/dot-maker)](https://github.com/dotkernel/dot-maker/stargazers)
[![GitHub license](https://img.shields.io/github/license/dotkernel/dot-maker)](https://github.com/dotkernel/dot-maker/blob/1.0/LICENSE.md)

## Installation

Run the following command in your terminal:

```shell
compose require-dev dotkernel/dot-maker
```

## Setup

TODO: document stub publishing and (optional) config creation

## Usage

### Create Module

```shell
./vendor/bin/dot-maker module
```

TODO: add documentation for all commands that create types defined in src/Type/TypeEnum.php

Dependency tree:

- Module
  - *
- Collection (API only)
- Command
  - Service
- ConfigProvider
  - Handler
  - Service
  - Entity
- Entity
  - Repository
- Form
  - InputFilter
    - Input
- Handler
  - Form
  - Service
  - config
- Input (just the directory?)
  - InputFilter
- InputFilter
- Middleware
  - Service
- Repository
  - Entity
- RoutesDelegator
  - Handler
- Service
  - Repository
  - config
- ServiceInterface
  - Service
- test (?)
