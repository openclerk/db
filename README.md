openclerk/db
============

A library for Database management in Openclerk.

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
function db() {
  return new \Db\Connection(
    Openclerk\Config::get("database_name"),
    Openclerk\Config::get("database_username"),
    Openclerk\Config::get("database_password")
  );
}
```

Load them up and optionally install them at runtime:

```php
$logger = new \Db\Logger();

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
