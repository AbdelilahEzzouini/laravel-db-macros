# Laravel DB Macros

A Laravel package that provides useful DB macros for query building.
## Overview

The `binding` macro extends Laravel's database functionality by providing a more intuitive way to work with SQL queries using named parameters. This approach offers several advantages:

### Key Features

- **Named Parameters**: Use descriptive parameter names (`:param_name`) instead of positional placeholders (`?`), making your queries more readable and maintainable.
- **Array Parameter Support**: Easily include arrays in your IN clauses with the special `[:array_param]` syntax.
- **Flexible Statement Types**: Execute different types of SQL operations (SELECT, INSERT, UPDATE, DELETE) using the same consistent syntax.
- **SQL Injection Protection**: All parameters are properly escaped and bound, maintaining the security of your database operations.
- **Simplified Complex Queries**: Write complex SQL queries with multiple parameters without losing track of parameter order.

This macro bridges the gap between raw SQL flexibility and Laravel's query builder safety, giving you the best of both worlds for situations where the query builder might be too limiting.


## Installation

You can install the package via composer:

```bash
composer require AbdelilahEzzouini/db-macros:dev-main
```

## Configuration

After installing the package, the service provider will be automatically registered thanks to Laravel's package auto-discovery.

If you're using Laravel without auto-discovery, add the service provider to the `providers` array in `config/app.php`:

```php
'providers' => [
    // ...
    AbdelilahEzzouini\DbMacros\DbMacrosServiceProvider::class
    // ...
]
```

## Usage

The package provides a `binding` macro for the DB facade that allows you to use named parameters in your SQL queries.

### Basic Usage

```php
use Illuminate\Support\Facades\DB;

$results = DB::binding('SELECT * FROM users WHERE id = :id', [
    'id' => 1
]);
```

### Array Parameters

You can also use array parameters with the `[:param]` syntax:

```php
$results = DB::binding('SELECT * FROM users WHERE id IN ([:ids])', [
    'ids' => [1, 2, 3]
]);
```
### Statement Type

By default, the `binding` macro uses the `select` statement type based on the DB Facades. You can specify a different statement type as the third parameter:

```php
$results = DB::binding('UPDATE users SET name = :name WHERE id IN :id', [
    'name' => 'test user',
    'id' => 1
],'affectingStatement');
```

## Testing

To run the tests for this package, you can use PHPUnit:
```bash
vendor/bin/phpunit
```


## License

The MIT License (MIT)

Copyright (c) Abdelilah Ezzouini
