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

  /**
   * @return true on success
   * @throws DbException on error with {@link PDOStatement#errorInfo()}.
   */
  function execute($args = array()) {
    $this->cursor = $this->connection->getPDO()->prepare($this->query);
    if ($this->cursor->execute($args)) {
      return true;
    } else {
      throw new DbException(implode(": ", $this->cursor->errorInfo()));
    }
  }

  function fetch() {
    if ($this->cursor === null) {
      throw new DbException("Query must be executed first");
    }
    return $this->cursor->fetch();
  }

  function fetchAll() {
    if ($this->cursor === null) {
      throw new DbException("Query must be executed first");
    }
    return $this->cursor->fetchAll();
  }

}
