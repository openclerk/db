<?php

namespace Db;

/**
 * Represents an instance of a database connection, which can be used to
 * prepare and execute queries.
 * TODO master/slave switch
 * TODO query metrics
 * TODO everything else
 * TODO maybe create a subclass SwitchingConnection that can switch as necessary?
 *   we can then put initialisation in the __construct
 */
class Connection {

  function __construct($database, $username, $password, $host = "localhost", $port = 3306) {
    // TODO things, maybe lazily
  }

  function prepare($query) {
    // TODO things
    return new Query($this, $query);
  }

}
