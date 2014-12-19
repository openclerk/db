<?php

namespace Db;

use \Openclerk\Events;

/**
 * Represents a query which may have used a replicated connection.
 */
class ReplicatedQuery extends Query {

  var $is_master;

  function __construct(Connection $connection, $query, $is_master) {
    parent::__construct($connection, $query);
    $this->is_master = $is_master;
  }

  function usedMaster() {
    return $this->is_master;
  }

  function usedSlave() {
    return !$this->is_master;
  }

}
