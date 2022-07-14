# An IdentityProvider OpenIDConnect bundle
[![Latest Version](http://img.shields.io/packagist/v/coddin-web/idp-openid-connect-bundle.svg?style=flat-square)](https://github.com/coddin-web/idp-openid-connect-bundle/releases)
![Build](https://github.com/coddin-web/idp-openid-connect-bundle/actions/workflows/ci.yml/badge.svg?event=push)
[![codecov](https://codecov.io/gh/coddin-web/idp-openid-connect-bundle/branch/main/graph/badge.svg?token=BRH4XEU1VK)](https://codecov.io/gh/coddin-web/idp-openid-connect-bundle)

A Symfony bundle to set up an IdentityProvider with OpenID Connect implemented

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
----------------------------------------

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

### Step 3: Templates

For this template to work out-of-the-box webpack needs to be installed and a build needs to have taken place. This can be done with `npm install && npm run ENVIRONMENT`

### Step 4: Database

This bundle needs specific tables to exist for the OAuth flow to work. They can either be "brute forced" in your application by running `bin/console doctrine:schema:update --force` (which I do not recommend) or within your application you can run `bin/console doctrine:migrations:diff` to create the needed migrations to update your application with the needed tables.

### Step 5: Cache

The Symfony cache needs to be cleared before this module will be fully operational. This can be done with `bin/console cache:clear` If errors keep popping up try removing the cache by hand `rm -rf var/cache/*`

***Please be careful when doing this in a production environment***

### Environment variables

This bundle uses environment variables to configure certain aspects:

|    Variable     |                                                 Description                                                 |
|:---------------:|:-----------------------------------------------------------------------------------------------------------:|
| `TRUSTED_HOSTS` | This variable is used to protect the [`introspect`](https://datatracker.ietf.org/doc/html/rfc7662) endpoint |
| `COMPANY_NAME`  |                          This variable is used to customize the default templates                           |

There are also a few environment variables that are needed out of the box:

|         Variable          |                           Description                           |
|:-------------------------:|:---------------------------------------------------------------:|
|       `MAILER_DSN`        |              This is needed for the password reset              |
| `MESSENGER_TRANSPORT_DSN` | This is needed for the asynchronous processes this bundle uses  |

### Message Queue / Supervisor

This bundle uses asynchronous events to not block the end-user with possible hiccups of certain processes. Therefor it is needed to run a message queue.
It is recommended to use e.g. [supervisor](http://supervisord.org/) to run Symfony's Messenger queue like so: `bin/console messenger:consume async` 

By importing this bundle's configuration (see [Step 2](#step-2-include-default-configs)) the Messages will be configured for you.

## Final thoughts

This bundle comes with keys (which are needed by OAuth2 to sign the requests) located in the `config/openidconnect/keys` directory of the bundle.
*DO NOT* use these keys on a production environment but replace them during your build.

## Additional Resources

[https://github.com/steverhoades/oauth2-openid-connect-server](https://github.com/steverhoades/oauth2-openid-connect-server) - The core of this bundle
[https://github.com/thephpleague/oauth2-server](https://github.com/thephpleague/oauth2-server) - The base of the OpenIDConnect server library
[https://tailwindcss.com/](https://tailwindcss.com/) - Used as base for of the default templates
