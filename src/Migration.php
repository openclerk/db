<?php

namespace Db;

/**
 * Represents a database "migration", which can be composed together with other
 * migrations across multiple components to initialise a database and update it
 * with updates.
 */
class Migration {

  /**
   * @return true if this migration is applied
   */
  function isApplied(Connection $db) {
    $q = $db->prepare("SELECT * FROM migrations WHERE name=?");
    $q->execute(array($this->getName()));

    if ($q->fetch()) {
      return true;
    } else {
      return false;
    }
  }

  function getName() {
    return get_class($this);
  }

  /**
   * Get all parent {@link Migration}s that this migration depends on, as a list
   */
  function getParents() {
    return array(new BaseMigration());
  }

  /**
   * Install the current migration and any parent migrations that this migration depends on.
   */
  function install(Connection $db, Logger $log) {
    // bail if we've already applied
    if ($this->isApplied()) {
      return;
    }

    // simply make sure all parent migrations are applied
    foreach ($this->getParents() as $migration) {
      $migration->install($db, $log);
    }

    // and then install our own
    if ($this->apply($db)) {
      $log->log("Applied migration " . $this->getName());
    } else {
      $log->error("Could not apply migration " . $this->getName());
      throw new DbException("Could not apply migration " . $this->getName());
    }

    // save migration status
    $q = $db->prepare("INSERT INTO migrations SET name=?");
    $q->execute(array($this->getName()));
  }

  /**
   * Apply only the current migration.
   * @return true on success or false on failure
   */
  function apply(Connection $db) {
    // empty by default
    return true;
  }

}
