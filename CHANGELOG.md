# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [3.1.0] - 2025-09-08

### Major Changes
- **BREAKING**: Complete refactor of JWT authentication system
- **BREAKING**: Removed deprecated CheckTokenInterface and related interfaces
- **BREAKING**: Updated to use readonly properties in JWT classes

### Added
- **JWT**: New `JwtConfig`, `TokenIssuer`, `TokenParser` and `BlacklistManager` classes for better token management
- **JWT**: Custom builder callback support for access and refresh tokens
- **JWT**: Enhanced blacklist functionality with `BlacklistConfig` and `BlackListConstraint`
- **Crontab**: Complete database-based crontab configuration and execution system
- **Support**: `ClientOsTrait` for operating system detection from user agent
- **Support**: `IpUtils` class for IP address handling and validation
- **Auth**: New `CurrentUserInterface` and `UserInterface` for user management
- **Core**: New subscriber system for application lifecycle events
- **Access**: Permission attribute system for access control
- **Casbin**: Database adapter and rule management for authorization

### Changed
- **JWT**: Refactored `AbstractJwt` class with improved token management
- **JWT**: Updated constraint validation logic (swapped access and refresh token constraints)
- **JWT**: Factory configuration merge order optimization
- **Upload**: Automatic deletion of temporary files after upload completion
- **AppStore**: Improved namespace formatting and plugin management
- **Request**: Standardized header names to lowercase in ClientIp handling
- **Dependencies**: Updated lcobucci/jwt to version ~5.5.0

### Fixed
- **JWT**: Token expiration time validation issues
- **Filesystem**: Copy method to prevent file overwrites in same-name folders
- **AppStore**: Plugin installation seeder directory matching
- **AppStore**: Database rollback for plugin uninstallation
- **Request**: ClientIp function IP detection accuracy
- **Validation**: Enhanced refresh token constraint validation

### Removed
- **JWT**: Deprecated `AbstractTokenMiddleware` CheckToken dependency
- **JWT**: `RequestScopedTokenTrait` (replaced with better implementation)
- **GeneratorCrud**: Removed entire CRUD generator component
- **Events**: Removed `LogoutEvent` and `UserLoginEvent` classes
- **Interfaces**: Removed deprecated JWT authentication interfaces

### Security
- **JWT**: Improved token validation and constraint checking
- **Auth**: Enhanced security in middleware token handling

### Development
- **Tests**: Added comprehensive unit tests for JWT constraints
- **Tests**: Added filesystem copy method tests
- **CI**: Enhanced release workflow with tag existence checks
- **Code Style**: Applied CS-fixer across entire codebase
- **Dependencies**: Updated development dependencies and fixed CI issues

### Documentation
- **README**: Updated project documentation
- **CodeRabbit**: Added automated code review configuration

---

## Migration Guide (2.0 → 3.1)

### JWT Authentication Changes
1. **Interfaces Removed**: `CheckTokenInterface`, `CurrentUserInterface`, `UserInterface` have been deprecated
2. **New Classes**: Use `TokenIssuer` and `TokenParser` instead of direct AbstractJwt calls
3. **Configuration**: Update JWT factory configurations to use new merge order
4. **Constraints**: Verify token constraint validation logic matches your requirements

### Crontab System
- New database-based crontab system available
- Migrate from file-based to database configuration if needed

### Plugin Development
- Updated namespace requirements for AppStore plugins
- Check plugin installation/uninstallation scripts for compatibility

---

*For detailed information about specific changes, see individual commit messages in git history.*