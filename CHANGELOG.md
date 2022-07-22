# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

# [1.0.3] - 2022-07-22
### Changed
- Refactor the LeagueOAuthServer2 setup helper encryptionKey parameter to use a `Defuse\Crypto\Key` insteadof a string
  - this is a lot faster / safer than inputting a random cryptographic (password) string
  - @see explanation at [oauth2.thephpleague.com](https://oauth2.thephpleague.com/installation/#key-object)
- Update README.md accordingly

# [1.0.2] - 2022-07-21
### Changed
- Made all configuration file paths relative to the project directory
- Make environment variables an actual step to follow in the README.md

# [1.0.1] - 2022-07-21
### Changed
- Change the README.md to **actually** explain how to generate the public/private keys

# [1.0.0] - 2022-07-21
### Changed
- 1.0 release!
- Fix broken login flow (Created in 0.10.2)
- Better configuration handling for OAuth signing aspects

# [0.11.0] - 2022-07-19
### Added
- Reset password link generation + safely resolving that link

## [0.10.2] - 2022-07-15
### Added
- Better templating
- Password reset request splash page addition

## [0.10.1] - 2022-07-15
### Fixed
- Fix the changelog release links

## [0.10.0] - 2022-07-15
### Changed
- Better constraint exception handling
- Even better README.md
- Addition of CHANGELOG.md and CONTRIBUTING.md
- Stop pretending we actually have 100% coverage

## [0.9.7] - 2022-07-14
### Added
- User registration validation

## [0.9.7] - 2022-07-14
### Added
- Implement the message queue

## [0.9.6] - 2022-07-14
### Changed
- Keep on enhancing the README.md
- Route name fix in ForgottenPasswordController

## [0.9.5] - 2022-07-13
### Changed
- Keep on enhancing the README.md

## [0.9.4] - 2022-07-13
### Fixed
- Fixed configuration when requiring in a (clean) Symfony project

## [0.9.3] - 2022-07-13
### Changed
- Better README.md

## [0.9.2] - 2022-07-13
### Changed
- Refactor the unit tests to mirror the actual namespace

## [0.9.1] - 2022-07-13
### Changed
- Fix security and the doctrine User Entity
- General configuration namespacing
- Addition of all service declarations

## [0.9.0] - 2022-07-13
### Changed
I know, it's a big jump in versioning, but I want to make clear at this point the bundle is almost finished.

Many small fixes but still some TODO's left to address

- Complete overhaul as Symfony bundle
- Reinstate phpstan ignore errors
- Setup README.md
- Fix README badge and add gitignore values
- Fix templates and translations

## [0.1.3] - 2022-07-08
### Changed (still WIP)
- Fix bundle naming filename fix

## [0.1.2] - 2022-07-08
### Changed (still WIP)
- Fix bundle naming

## [0.1.1] - 2022-07-08
### Changed (still WIP)
- Fix bundle namespace

## 0.1.0 - 2022-07-08
### Added (still WIP)
- Initial setup of this bundle

[1.0.3]: https://github.com/coddin-web/idp-openid-connect-bundle/compare/1.0.2...1.0.3
[1.0.2]: https://github.com/coddin-web/idp-openid-connect-bundle/compare/1.0.1...1.0.2
[1.0.1]: https://github.com/coddin-web/idp-openid-connect-bundle/compare/1.0.0...1.0.1
[1.0.0]: https://github.com/coddin-web/idp-openid-connect-bundle/compare/0.11.0...1.0.0
[0.11.0]: https://github.com/coddin-web/idp-openid-connect-bundle/compare/0.10.2...0.11.0
[0.10.2]: https://github.com/coddin-web/idp-openid-connect-bundle/compare/0.10.1...0.10.2
[0.10.1]: https://github.com/coddin-web/idp-openid-connect-bundle/compare/0.10.0...0.10.1
[0.10.0]: https://github.com/coddin-web/idp-openid-connect-bundle/compare/0.9.7...0.10.0
[0.9.7]: https://github.com/coddin-web/idp-openid-connect-bundle/compare/0.9.6...0.9.7
[0.9.6]: https://github.com/coddin-web/idp-openid-connect-bundle/compare/0.9.5...0.9.6
[0.9.5]: https://github.com/coddin-web/idp-openid-connect-bundle/compare/0.9.4...0.9.5
[0.9.4]: https://github.com/coddin-web/idp-openid-connect-bundle/compare/0.9.3...0.9.4
[0.9.3]: https://github.com/coddin-web/idp-openid-connect-bundle/compare/0.9.2...0.9.3
[0.9.2]: https://github.com/coddin-web/idp-openid-connect-bundle/compare/0.9.1...0.9.2
[0.9.1]: https://github.com/coddin-web/idp-openid-connect-bundle/compare/0.9.0...0.9.1
[0.9.0]: https://github.com/coddin-web/idp-openid-connect-bundle/compare/0.1.3...0.9.0
[0.1.3]: https://github.com/coddin-web/idp-openid-connect-bundle/compare/0.1.2...0.1.3
[0.1.2]: https://github.com/coddin-web/idp-openid-connect-bundle/compare/0.1.1...0.1.2
[0.1.1]: https://github.com/coddin-web/idp-openid-connect-bundle/compare/0.1.0...0.1.1
