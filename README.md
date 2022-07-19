# An IdentityProvider OpenIDConnect bundle
[![Latest Version](http://img.shields.io/packagist/v/coddin-web/idp-openid-connect-bundle.svg?style=flat-square)](https://github.com/coddin-web/idp-openid-connect-bundle/releases)
![Build](https://github.com/coddin-web/idp-openid-connect-bundle/actions/workflows/ci.yml/badge.svg?event=push)
[![codecov](https://codecov.io/gh/coddin-web/idp-openid-connect-bundle/branch/main/graph/badge.svg?token=BRH4XEU1VK)](https://codecov.io/gh/coddin-web/idp-openid-connect-bundle)

A Symfony bundle to set up an IdentityProvider with OpenID Connect implemented

__Small disclaimer__ This is a work-in-progress and may contain bugs (as of july 2022)

Installation
============
Make sure Composer is installed globally, as explained in the
[installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Applications that use Symfony Flex
----------------------------------
Open a command console, enter your project directory and execute:

```console
$ composer require coddin-web/idp-openid-connect-bundle
```

Applications that don't use Symfony Flex
----------------------------------------
### Step 1: Download the Bundle
Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require coddin-web/idp-openid-connect-bundle
```

### Step 2: Enable the Bundle
Then, enable the bundle by adding it to the list of registered bundles
in the `config/bundles.php` file of your project:

```php
// config/bundles.php

return [
    // ...
    Coddin\IdentityProvider\CoddinIdentityProviderBundle::class => ['all' => true],
    // ...
];
```

___
Please note that this bundle can provide default configuration for other bundles (like doctrine and messenger). To make this work this bundle should be registered before other bundles.

This bundle also comes with a fully configured security config. Please make sure this does not conflict with your own security configuration or skip using the provided config and manually import what you need.
___

Configuring this bundle
-----------------------
### Step 1: Include routes
This bundle provides routes needed for the OpenIDConnect flow to work, you can import them like so:

```yaml
# config/routes.yaml
idp:
  resource: "@CoddinIdentityProviderBundle/config/routes.yaml"
  prefix: /
```

### Step 2: Include default configs:

___Note: this step can be skipped if you decide to configure certain bundles (like DoctrineBundle and SecurityBundle, etc) yourself.___

```yaml
# config/packages/coddin_identity_provider.yaml
imports:
  - { resource: "@CoddinIdentityProviderBundle/config/config.yaml" }
```

___
Please note that when including this configuration you need to remove your application security.yaml file.
___

Or you could import the individual configuration files like so:

```yaml
# config/packages/coddin_identity_provider.yaml
imports:
  # ...
  - { resource: "@CoddinIdentityProviderBundle/config/packages/messenger.yaml" }
```

### Step 3: Templates
The templates need the assets provided by the bundle, they can be installed via:

`bin/console assets:install --symlink`

### Step 4: Database
This bundle needs specific tables to exist for the OAuth flow to work. They can either be "brute forced" in your application by running `bin/console doctrine:schema:update --force` (which I do not recommend) or within your application you can run `bin/console doctrine:migrations:diff` to create the needed migrations to update your application with the needed tables.

### Step 5: Cache
The Symfony cache needs to be cleared before this module will be fully operational. This can be done with `bin/console cache:clear` If errors keep popping up try removing the cache by hand `rm -rf var/cache/*`

***Please be careful when doing this in a production environment***

### Environment variables
This bundle uses environment variables to configure certain aspects:

|    Variable     |                                                          Description                                                           |
|:---------------:|:------------------------------------------------------------------------------------------------------------------------------:|
| `IDP_HOST_URL`  |                 This variable is used by the router to determine the default host (e.g. for e-mail templates)                  |
|  `IDP_SCHEME`   |    This variable is used by the router to determine the default scheme (which should be `https`, which is also the default)    |
| `TRUSTED_HOSTS` |          This variable is used to protect the [`introspect`](https://datatracker.ietf.org/doc/html/rfc7662) endpoint           |
| `COMPANY_NAME`  |                                    This variable is used to customize the default templates                                    |
| `IDP_MAIL_FROM` |                                  This variable is used as the global "from" header for emails                                  |

There are also a few environment variables that are needed out of the box:

|         Variable          |                           Description                           |
|:-------------------------:|:---------------------------------------------------------------:|
|       `MAILER_DSN`        |              This is needed for the password reset              |
| `MESSENGER_TRANSPORT_DSN` | This is needed for the asynchronous processes this bundle uses  |

### OAuth2 keys
This bundle comes with keys (which are needed by OAuth2 to sign the requests) located in the `config/openidconnect/keys` directory of the bundle.
*DO NOT* use these keys on a production environment but replace them during your build.

TODO: Explain creation/usage of keys...

Message Queue / Supervisor
--------------------------
This bundle uses asynchronous events to not block the end-user with possible hiccups of certain processes. Therefor it is needed to run a message queue.
It is recommended to use e.g. [supervisor](http://supervisord.org/) to run Symfony's Messenger queue like so: `bin/console messenger:consume async` 

By importing this bundle's configuration (see [Step 2](#step-2-include-default-configs)) the Messages will be configured for you.

Supported languages
-------------------
Out of the box this module supports detection of the locale via the browser.

Two languages are supported: Dutch and English

Feel free to contribute other languages by submitting a Pull Request!

Testing
-------
This bundle uses [PHPUnit](https://phpunit.de) for unit and integration tests.

It can be run standalone by `composer phpunit` or within the complete checkup by `composer checkup`

__NB__ The integration tests need a database to be run against. See the .env.test (which can be overruled by .env.test.local) for the needed configuration.

### Checkup
The above-mentioned checkup runs multiple analyses of the bundle's code. This includes [Squizlab's Codesniffer](https://github.com/squizlabs/PHP_CodeSniffer), [PHPStan](https://phpstan.org) and a [coverage check](https://github.com/richardregeer/phpunit-coverage-check).

Continuous Integration
----------------------
[GitHub actions](https://github.com/features/actions) are used for continuous integration. Check out the [configuration file](https://github.com/coddin-web/idp-openid-connect-bundle/blob/main/.github/workflows/ci.yml) if you'd like to know more.

Changelog
---------
See the [project changelog](https://github.com/coddin-web/idp-openid-connect-bundle/blob/main/CHANGELOG.md)

Contributing
------------
Contributions are always welcome. Please see [CONTRIBUTING.md](https://github.com/coddin-web/idp-openid-connect-bundle/blob/main/CONTRIBUTING.md) and [CODE_OF_CONDUCT.md](https://github.com/coddin-web/idp-openid-connect-bundle/blob/main/CODE_OF_CONDUCT.md) for details.

License
-------
The MIT License (MIT). Please see [License File](https://github.com/coddin-web/idp-openid-connect-bundle/blob/main/LICENSE) for more information.

Credits
-------
This code is principally developed and maintained by [Marius Posthumus](https://github.com/MJTheOne) for usage by several clients of [Coddin](https://coddin.nl)

Additional Resources
--------------------
[https://github.com/steverhoades/oauth2-openid-connect-server](https://github.com/steverhoades/oauth2-openid-connect-server) - The core of this bundle
[https://github.com/thephpleague/oauth2-server](https://github.com/thephpleague/oauth2-server) - The base of the OpenIDConnect server library
[https://tailwindcss.com/](https://tailwindcss.com/) - Used as base for of the default templates
