PHP Odoo DBAL documentation
===========================

This library provides a record manager to manage the Odoo database models via the external API ORM methods.

Summary
-------

- [Getting started](#getting-started)
- [Repositories](./repositories.md)
- [Query builder](./query_builder.md)
  - [Expression builder](./expression_builder.md)

Getting started
---------------

First of all, create the client following the 
[dedicated documentation](https://github.com/Ang3/php-odoo-api-client#create-a-client).
Then, create a record manager with the client as first required argument:

```php
use Ang3\Component\Odoo\Client;
use Ang3\Component\Odoo\DBAL\RecordManager;

/** @var Ang3\Component\Odoo\Client $client */
$recordManager = new RecordManager($client);
```

### Find a record

The manager has a shortcut method to find a record by ID:

```php
$myCompany = $recordManager->find('res.company', 7); // array or NULL if company was not found.
```

For more advanced queries, please read the next section for repositories.

### Get a repository

Once the record manager created, you can retrieve a default repository for your model to query it:

```php
$companyRepository = $recordManager->getRepository('res.company');

// Retrieve all companies with all fields
$allCompanies = $companyRepository->findAll();
```

Please read the [documentation of repositories](./repositories.md) for more information about available methods.

### Add a custom repository

By default, the record manager will use the default repository 
`Ang3\Component\Odoo\DBAL\Repository\RecordRepository` provided by this package.

You can overwrite and provide your own repository. To do so, create your repository by implementing the interface 
`Ang3\Component\Odoo\DBAL\Repository\RecordRepositoryInterface`. Implement all required methods, then register 
your repository inside the record manager:

```php
/** @var \Ang3\Component\Odoo\DBAL\Repository\RecordRepositoryInterface $myRepository */
$recordManager->addRepostiory($myRepository);
```

The record manager will register/overwrite the repository for the target model.

### Create a query builder

You can create a query builder from the record manager. It helps you to create well-formed queries:

```php
// Create a query builder to create query for companies
$queryBuilder = $recordManager->createQueryBuilder('res.company');
```

Please read the [documentation of the query builder](./query_builder.md) for more information about usage.

Learn more
----------

- [Repositories](./repositories.md)
- [Query builder](./query_builder.md)
  - [Expression builder](./expression_builder.md)

Resources
---------

- [README.md](../README.md)