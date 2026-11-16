# Changelog

All notable changes to this project are documented in this file, newest first.

## 3.0.0 - TBD

### Added

- PHP 8.6 support.
- "Next steps" are printed after `make command`, `make middleware` and `make entity`, not only after `make module`, and every message is kept when several components are created in one run.
- `Input::isExhausted()` to detect the end of input.
- `.gitattributes`, so the Composer package no longer ships tests, docs and tooling files.
- Upgrade guide in the documentation.

### Changed

- PHPUnit 12 and PHPStan level 8.
- Names must start with a letter and must not be PHP reserved words, with or without the component suffix.
- A name that is only the component suffix (for example `Entity`) is kept as the class name.
- `bin/dot-maker` exits with code 1 when the command fails.
- `make form` in an API project fails before asking for a module.
- Generated arrays are aligned to the Laminas Coding Standard.
- Generated `GetEdit...FormHandler` and `GetDelete...FormHandler` pass the entity to the template under its camelCase name, like the POST handlers.
- `Context::getRootNamespace()` and `Context::getProjectType()` return `string`, and `Context` requires a non-Core namespace under `autoload.psr-4`.
- `FileInterface::create()` returns `File` instead of `?File`.
- `Module::getMessages()` returns the messages grouped by priority.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Infinite loop when the input ends at the "Existing module name" prompt.
- Wrong console name for Commands whose name contains "Command" before the end.
- Generated files that failed `php -l` for names starting with a digit or matching a PHP reserved word.
- Empty class name (`.php` file) when the name was only the component suffix.
- `TypeError` for an invalid `composer.json` or `config/autoload/maker.local.php`.
- `File::create('')` reported a failure after writing the file.
- `TypeError` when writing to a closed output stream.

### Security

- Nothing.

## 2.1.0 - 2026-10-13

### Added

- `operationId` in generated OpenAPI operations (#46).

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- PHP 8.2 support (#47).

### Fixed

- Nothing.

### Security

- Nothing.

## 2.0.0 - 2025-12-15

### Added

- Nothing.

### Changed

- Generated code adapted for Headless Platform 7 (#44, #45).

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

### Security

- Nothing.

## 1.1.0 - 2025-10-29

### Added

- PHP 8.4 and 8.5 support (#42).

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- PHP 8.1 support (#42).

### Fixed

- Invalid array type assignations reported by PHPStan (#40).

### Security

- Nothing.

## 1.0.5 - 2025-09-25

### Added

- Link to the tutorial video in the documentation (#38).

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

### Security

- Nothing.

## 1.0.4 - 2025-09-10

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Installation instructions in the documentation (#36).

### Security

- Nothing.

## 1.0.3 - 2025-09-09

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Package installation command (#34).

### Security

- Nothing.

## 1.0.2 - 2025-09-02

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Resources in routes must be singular (#19).
- InputFilters initialised via `init` in Admin and via the constructor in API (#21).
- Form factory used in ConfigProvider (#23).
- Handler naming pattern for non-API projects (#25).
- Invalid `CsrfInput` name in non-API InputFilters (#27).
- Template naming pattern (#28).
- Route name used in Form action attributes (#29).

### Security

- Nothing.

## 1.0.1 - 2025-08-22

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Collection path generated in RoutesDelegator (#8).
- Invalid property initialisations (#10).
- `(Core)ConfigProvider` and Middleware related messages after `make module` (#12).

### Security

- Nothing.

## 1.0.0 - 2025-08-21

### Added

- Initial release.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

### Security

- Nothing.
