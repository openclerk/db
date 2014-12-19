<?php

namespace Db;

/**
 * Represents an instance of a single database connection.
 */
class SoloConnection implements Connection {

  var $pdo = null;
  var $database = null;

  function __construct($database, $username, $password, $host = "localhost", $port = 3306, $timezone = false) {
    // lazily store these settings for later (in getPDO())
    $this->database = $database;
    $this->username = $username;
    $this->password = $password;
    $this->host = $host;
    $this->port = $port;
    $this->timezone = $timezone;
  }

  function prepare($query) {
    // TODO things
    return new Query($this, $query);
  }

  function getPDO() {
    if ($this->pdo === null) {
      $dsn = $this->getDSN();
      $this->pdo = new \PDO($dsn, $this->username, $this->password);

      $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

      // set timezone if set
      if ($this->timezone) {
        $q = $this->prepare("SET timezone=?");
        $q->execute(array($this->timezone));
      }
    }
    return $this->pdo;
  }

  function getDSN() {
    // TODO escape string
    // TODO add port number
    // TODO not assume that all Db's are MySQL
    return "mysql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->database;
  }

  function lastInsertId() {
    return $this->getPDO()->lastInsertId();
  }

  /**
   * We implement {@link Serializable} so that this can be used in a serialized
   * exception argument.
   */
  function serialize() {
    return serialize($this->getDSN());
  }

  /**
   * @throws Exception since unserialize() is not supported on this object
   */
  function unserialize($ser) {
    throw new \Exception("\Db\Connection can not be unserialized");
  }

  // TODO setAttribute()

}
