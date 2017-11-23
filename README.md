# mid

[![Build Status](https://secure.travis-ci.org/danizord/mid.svg?branch=master)](https://secure.travis-ci.org/danizord/mid)
[![Coverage Status](https://coveralls.io/repos/github/danizord/mid/badge.svg?branch=master)](https://coveralls.io/github/danizord/mid?branch=master)

This library provides a set of utility functions that helps working with
[PSR-15](https://github.com/php-fig/fig-standards/blob/master/proposed/http-handlers/request-handlers.md) interfaces.

## Installation

Run the following to install this library:

```bash
$ composer require danizord/mid
```

## Documentation

Coming soon...

For now you can check [mid.php](src/mid.php). Each function has a docblock containing description
and usage examples.

## PSR-15 draft version support

The 0.1 version of this library supports both http-interop/http-middleware ^0.4 and
http-interop/http-middleware ^0.5, but the `MiddlewarePipeline` class and `pipeline()` function
is only supported by http-interop/http-middleware ^0.5.

The support for http-interop/http-server-middleware and http-interop/http-server-handler 1.0 will
be shipped as soon as Zend\Expressive starts supporting it.
