<?php

namespace Db;

use \Openclerk\Events;

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
    Events::trigger('db_prepare_start', $this->query);
    $this->cursor = $this->connection->getPDO()->prepare($this->query);
    Events::trigger('db_prepare_end', $this->query);

    Events::trigger('db_execute_start', $this->query);
    $result = $this->cursor->execute($args);
    Events::trigger('db_execute_end', $this->query);
    if ($result) {
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

    Events::trigger('db_fetch_start', $this->query);
    $result = $this->cursor->fetch();
    Events::trigger('db_fetch_end', $this->query);

    return $result;
  }

  function fetchAll() {
    if ($this->cursor === null) {
      throw new DbException("Query must be executed first");
    }

    Events::trigger('db_fetch_all_start', $this->query);
    $result = $this->cursor->fetchAll();
    Events::trigger('db_fetch_all_end', $this->query);

    return $result;
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

  function getConnection() {
    return $this->connection;
  }

  function rowCount() {
    return $this->cursor->rowCount();
  }

}
