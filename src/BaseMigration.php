<?php

namespace Db;

/**
 * A migration for the `migrations` table.
 */
class BaseMigration extends Migration {

  /**
   * Override the default function to check that a table exists.
   * @return true if this migration is applied
   */
  function isApplied(Connection $db) {
    return $this->tableExists($db, "migrations");
  }

  /**
   * The BaseMigration has no parents.
   */
  function getParents() {
    return array();
  }

  /**
   * Apply only the current migration.
   * @return true on success or false on failure
   */
  function apply(Connection $db) {
    $q = $db->prepare("CREATE TABLE migrations (
      id int not null auto_increment primary key,
      name varchar(255) not null,
      created_at timestamp not null default current_timestamp,

      INDEX(name)
    );");
    return $q->execute();
  }

}
