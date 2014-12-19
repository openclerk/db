<?php

namespace Db;

/**
 * Represents an instance of a database connection, which can be used to
 * prepare and execute queries.
 * TODO master/slave switch
 * TODO everything else
 * TODO maybe create a subclass SwitchingConnection that can switch as necessary?
 *   we can then put initialisation in the __construct
 */
interface Connection extends \Serializable {

  /**
   * @return a {@link Query} ready to be used
   */
  function prepare($query);

  // TODO setAttribute()

  /**
   * @return the last inserted auto_increment id, if there is any
   */
  function lastInsertId();

}
