Leapt IM Bundle
===============

[![Package version](https://img.shields.io/packagist/v/leapt/im-bundle.svg?style=flat-square)](https://packagist.org/packages/leapt/im-bundle)
[![Build Status](https://img.shields.io/github/workflow/status/leapt/im-bundle/Continuous%20Integration/3.x?style=flat-square)](https://github.com/leapt/im-bundle/actions?query=workflow%3A%22Continuous+Integration%22)
[![PHP Version](https://img.shields.io/packagist/php-v/leapt/im-bundle.svg?branch=3.x&style=flat-square)](https://travis-ci.org/leapt/im-bundle?branch=3.x)
[![License](https://img.shields.io/badge/license-MIT-red.svg?style=flat-square)](LICENSE)
[![Code coverage](https://img.shields.io/codecov/c/github/leapt/im-bundle?style=flat-square)](https://codecov.io/gh/leapt/im-bundle/branch/3.x)

This bundle is a fork of the SnowcapImBundle.

The current version (3.x) of the bundle works with Symfony 4.4 & Symfony 5.

For older versions of Symfony:

* Use version 2.1+ for Symfony between 3.3 and 4.4
* Use version < 2.1 for Symfony < 3.3

You can check the [changelog](CHANGELOG-3.x.md) for version 3 and the [upgrade document](UPGRADE-3.x.md) when upgrading
from 2.x bundle version.

Introduction
------------

Imagemagick wrapper for Symfony.

It's a general wrapper to access imagemagick command line functions, instead of using bindings like iMagick, 
which doesn't cover all the imagemagick functionalities.

It allows you to use all the convert/mogrify power, from your controller or your views

See the [Documentation and examples](https://github.com/leapt/im-bundle/tree/3.x/docs)

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
