Leapt IM Bundle
===============

[![Package version](https://img.shields.io/packagist/v/leapt/im-bundle.svg?style=flat-square)](https://packagist.org/packages/leapt/im-bundle)
[![Build Status](https://img.shields.io/github/actions/workflow/status/leapt/im-bundle/continuous-integration.yml?branch=5.x&style=flat-square)](https://github.com/leapt/im-bundle/actions?query=workflow%3A%22Continuous+Integration%22)
![PHP Version](https://img.shields.io/packagist/php-v/leapt/im-bundle/v5.0.0?branch=5.x&style=flat-square)
[![License](https://img.shields.io/badge/license-MIT-red.svg?style=flat-square)](LICENSE)
[![Code coverage](https://img.shields.io/codecov/c/github/leapt/im-bundle?style=flat-square)](https://codecov.io/gh/leapt/im-bundle/branch/5.x)

This bundle is a fork of the SnowcapImBundle.

Introduction
------------

Imagemagick wrapper for Symfony.

It's a general wrapper to access imagemagick command line functions, instead of using bindings like iMagick,
which doesn't cover all the imagemagick functionalities.

It allows you to use all the convert/mogrify power, from your controller or your views

See the [Documentation and examples](https://im-bundle.leapt.dev/)

Versions & dependencies
-----------------------

The current version (5.x) of the bundle works with Symfony 6.4 & Symfony 7.0+.
The project follows SemVer.

You can check the [changelog](CHANGELOG-5.x.md) for version 5 and the [upgrade document](UPGRADE-5.x.md) when upgrading
from 4.x bundle version.

| ImBundle version | Symfony version          | PHP version
|------------------|--------------------------| -----------
| 5.x              | ^6.4 \|\| ^7.0           | ^8.2
| 4.x              | ^5.4 \|\| ^6.0           | ^8.0
| 3.x              | ^4.4 \|\| ^5.0           | ^7.2 \|\| ^8.0
| 2.1+             | ^3.3 \|\| ^4.0           | >=5.5
| 2.0, < 2.1       | ^2.7 \|\| ^3.0 \|\| ^4.0 | >=5.4
| 1.x              | ^2.7                     | >=5.3.3

Contributing
------------

Feel free to contribute, like sending [pull requests](https://github.com/leapt/im-bundle/pulls) to add features/tests
or [creating issues](https://github.com/leapt/im-bundle/issues) :)

Note there are a few helpers to maintain code quality, that you can run using these commands:

```bash
composer cs:dry # Code style check
composer phpstan # Static analysis
vendor/bin/phpunit # Run tests
```

Docs are built using mkdocs. To launch the docs server locally, run `make docs-start` & open http://127.0.0.1:8000/.
