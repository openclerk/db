<?php

namespace Db;

/**
 * Represents an instance of a query, which can be executed and results fetched.
 */
class Query {

  var $connection;
  var $query;

  var $cursor = null;

  function __construct(Connection $connection, $query) {
    $this->connection = $connection;
    $this->query = $query;
  }

  function execute($args = array()) {
    $this->cursor = $this->connection->getPDO()->prepare($this->query);
    $this->cursor->execute($args);
  }

  function fetch() {
    return $this->cursor->fetch();
  }

  function fetchAll() {
    return $this->cursor->fetchAll();
  }

}
