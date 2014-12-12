<?php

namespace Db;

/**
 * Represents an instance of a query, which can be executed and results fetched.
 */
class Query implements \Serializable {

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
      $errorInfo = $this->cursor->errorInfo();
      $errorResult = array();
      foreach ($errorInfo as $key) {
        if ($key) {
          $errorResult[] = $key;
        }
      }
      if (!$errorResult) {
        $errorResult = array("(no error code)");
      }
      throw new DbException(implode(": ", $errorResult));
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

  /**
   * We implement {@link Serializable} so that this can be used in a serialized
   * exception argument.
   */
  function serialize() {
    return serialize($this->query);
  }

  /**
   * @throws Exception since unserialize() is not supported on this object
   */
  function unserialize($ser) {
    throw new \Exception("\Db\Query can not be unserialized");
  }

}
