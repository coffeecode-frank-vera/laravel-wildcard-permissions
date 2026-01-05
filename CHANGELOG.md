# Changelog

All notable changes to this project will be documented in this file.

## [Version 0.1.0] - 2026-01-05

### Added

- Laravel 12.x support
- PHP 8.2, 8.3, and 8.4 support
- Requirements section in README

### Changed

- Updated all illuminate dependencies to support Laravel 12 (`^12.0`)
- Updated PHP requirement to support versions 8.1 through 8.4
- Improved migration file to use `config()` helper instead of `Config::` facade for consistency
- Simplified installation instructions in README (removed unnecessary service provider registration steps)

### Fixed

- Migration file now consistently uses `config()` helper in both `up()` and `down()` methods

## [Version 0.0.2] - 2024-02-03

### Added

- Add Middlewares


## [Version 0.0.1]  - 2024-02-03

### Added

- Ability for users or other models to assign, unassign and search for Roles and Permissions
- Roles group Permissions
- You can search for permission using wildcards with 3 operators Any (*), AND (,), OR (|)
- Compatible with Gate for Authorizable models

### Changed

- Nothing

### Deprecated

- Nothing

### Removed

- Nothing 

### Fixed

- Nothing

