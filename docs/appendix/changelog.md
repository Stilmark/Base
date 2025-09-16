# Changelog

All notable changes to this project will be documented in this file.

## [Unreleased]

## [1.6.9] - 2025-09-16
### Documentation
- **Request class**: Expanded documentation with a detailed API reference and usage examples.

## [1.6.8] - 2025-09-15
### Added
- **Helper class**: New class with string case conversion utilities.

### Documentation
- Updated changelog for versions 1.6.4-1.6.7.

## [1.6.7] - 2025-09-15
### Added
- **Logger class**: Complete Rollbar integration with error tracking and monitoring
- **Logger::init()**: Static method for initializing error reporting and Rollbar configuration
- **Logger::log()**: Full logging functionality with 8 PSR-3 compatible log levels
- **Automatic user context**: Session user data automatically included in Rollbar logs
- **Environment-based error handling**: Rollbar takes over PHP error handling when enabled

### Enhanced
- **Auth documentation**: Added Google OAuth2 repository links and credential setup instructions
- **Logger documentation**: Comprehensive documentation with Rollbar setup, API reference, and usage examples

### Changed
- **Test initialization**: Simplified using `Logger::init()` instead of inline Rollbar setup

## [1.6.6] - 2025-09-15
### Added
- **CORS support**: Complete CORS configuration and handling with environment variables
- **CodeFactor badge**: Added code quality badge to README

### Enhanced
- **Documentation**: Updated URLs to use stilmark-dev domain
- **Environment documentation**: Expanded with detailed variable descriptions and examples
- **Core API documentation**: Added comprehensive documentation for components and middleware

### Changed
- **Documentation structure**: Reorganized files into docs directory
- **GitBook integration**: Added gitbook config and improved structure

## [1.6.5] - 2025-09-12
### Added
- **Multi-provider OAuth**: Support for multiple OAuth providers with initial Google implementation

### Enhanced
- **Documentation**: Added comprehensive documentation structure and placeholder files
- **Environment management**: Added example configuration files and session management docs

## [1.6.4] - Initial Release
- First release of Base
- Includes Env, Request, Router, Controller, Render, Auth, AuthMiddleware, Logger (stub)
