<?php

namespace Db;

/**
 * Represents an instance of a query, which can be executed and results fetched.
 */
class Query {

  function __construct(Connection $connection, $query) {
    $this->connection = $connection;
    $this->query = $query;
  }

  function execute($args = array()) {
    // TODO
  }

  function fetch() {
    // TODO
  }

  function fetchAll() {
    // TODO
  }

}
