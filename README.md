PHP Odoo Database Abstraction Layer (DBAL)
==========================================

[![Code Quality](https://github.com/ang3/php-odoo-dbal/actions/workflows/php_lint.yml/badge.svg)](https://github.com/ang3/php-odoo-dbal/actions/workflows/php_lint.yml)
[![PHPUnit tests](https://github.com/ang3/php-odoo-dbal/actions/workflows/phpunit.yml/badge.svg)](https://github.com/ang3/php-odoo-dbal/actions/workflows/phpunit.yml)
[![Latest Stable Version](https://poser.pugx.org/ang3/php-odoo-dbal/v/stable)](https://packagist.org/packages/ang3/php-odoo-dbal) 
[![Latest Unstable Version](https://poser.pugx.org/ang3/php-odoo-dbal/v/unstable)](https://packagist.org/packages/ang3/php-odoo-dbal) 
[![Total Downloads](https://poser.pugx.org/ang3/php-odoo-dbal/downloads)](https://packagist.org/packages/ang3/php-odoo-dbal)

This component allows you to manage your Odoo instance as database like Doctrine by managing records. 
This library uses the [PHP Odoo API client](https://github.com/Ang3/php-odoo-api-client) `>=8.x` with JSON-RPC by default.

**Main features**

- Record manager
- Query builder
- Repositories
- Paginator

Installation
============

Open a command console, enter your project directory and execute the
following command to download the latest stable version of the client:

```console
$ composer require ang3/php-odoo-dbal
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Usage
=====

Please read the [documentation](/docs/index.md).

Tests
=====

To run tests:

```console
$ git clone git@github.com:Ang3/php-odoo-dbal.git
$ composer install
$ vendor/bin/simple-phpunit
```

License
=======

This software is published under the [MIT License](./LICENCE).
