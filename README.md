openclerk/db [![Build Status](https://travis-ci.org/openclerk/db.svg?branch=master)](https://travis-ci.org/openclerk/db)
============

A library for MySQL database management in Openclerk, supporting migrations, connection abstraction and automated replication switching.

## Installing

Include `openclerk/db` as a requirement in your project `composer.json`,
and run `composer update` to install it into your project:

```json
{
  "require": {
    "openclerk/db": "^0.1"
  }
}
```

## Using

Use [component-discovery](https://github.com/soundasleep/component-discovery) to enable
discovery of migrations across all of your dependencies. Update your `discovery.json`:

```json
{
  "components": {
    "migrations": "migrations.json"
  }
}
```

You can then define your own migrations using `migrations.json` in each component:

```json
{
  "my_migration_1": "\\My\\Migration"
}
```

Configure your database connection, optionally through a helper function `db()`
(see also [openclerk/config](https://github.com/openclerk/config) project):

```php
use \Openclerk\Config;

function db() {
  return new \Db\SoloConnection(
    Config::get("database_name"),
    Config::get("database_username"),
    Config::get("database_password")
  );
}
```

Load them up and optionally install them at runtime:

```php
$logger = new \Monolog\Logger('name');

class AllMigrations extends \Db\Migration {
  function getParents() {
    return array(new Db\BaseMigration()) + DiscoveredComponents\Migrations::getAllInstances();
  }
}

$migrations = new AllMigrations(db());
if ($migrations->hasPending(db())) {
  $migrations->install(db(), $logger);
}
```

Migrations can be [discovered and loaded at runtime](https://github.com/soundasleep/component-discovery) with a `migrations.json`.

You can also generate migrations at runtime, for example [generating a table for each Currency discovered at runtime](https://github.com/soundasleep/openclerk/blob/master/core/Migrations/BlockCountMigrationGenerator.php).

## Replication

You can also define replication connections which are selected based on the type of query,
and whether that table has recently been updated in the current $_SESSION:

```php
function db() {
  return new \Db\ReplicatedConnection(
    Config::get("database_host_master"),
    Config::get("database_host_slave"),
    Config::get("database_name"),
    Config::get("database_username"),
    Config::get("database_password")
  );
}
```

## Events

A number of [events](https://github.com/openclerk/events) are triggered by the library,
and can be captured for [metrics](https://github.com/openclerk/metrics):

* `db_prepare_start`, `db_prepare_end`, `db_prepare_master`, `db_prepare_slave`
* `db_execute_start`, `db_execute_end`
* `db_fetch_start`, `db_fetch_end`
* `db_fetch_all_start`, `db_fetch_all_end`
