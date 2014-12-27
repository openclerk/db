<?php

use Db\Connection;
use Db\ReplicatedConnection;

class TestReplicatedConnection extends ReplicatedConnection {

  function __construct($master_host, $slave_host, $database, $username, $password, $port = 3306, $timezone = false) {
    $this->master = new TestConnection();
    $this->slave = new TestConnection();
  }

}

class TestConnection implements Connection {

  function prepare($query) {
    throw new \Exception("Should not call TestConnection::prepare()");
  }

  // TODO setAttribute()
  function getPDO() {
    return new TestPDO();
  }

  function lastInsertId() {

  }

  function serialize() {
    throw new \Exception("\Db\TestConnection can not be serialized");
  }

  function unserialize($serialized) {
    throw new \Exception("\Db\TestConnection can not be unserialized");
  }

}

class TestPDO {
  function prepare($query) {
    return new TestQuery();
  }
}

class TestQuery {
  function execute($args) {
    // always returns true
    return true;
  }
}

/**
 * Tests that we can correctly switch between master/slave
 * databases as necessary.
 */
class ReplicatedTest extends PHPUnit_Framework_TestCase {

  function setUp() {
    $this->db = new TestReplicatedConnection("master", "slave",
      "database", "user", "password");

    // also reset any session statuses
    $this->db->resetSessionData();
  }

  function selectUsesMaster($uses_master) {
    $q = $this->db->prepare("SELECT * FROM test_table");
    $q->execute();

    if ($uses_master) {
      $this->assertTrue($q->usedMaster(), "should have used master");
      $this->assertFalse($q->usedSlave(), "should have not used slave");
    } else {
      $this->assertFalse($q->usedMaster(), "should have not used master");
      $this->assertTrue($q->usedSlave(), "should have used slave");
    }
  }

  function testSelect() {
    $this->selectUsesMaster(false);
  }

  function testInsert() {
    $q = $this->db->prepare("INSERT INTO test_table SET id=1");
    $q->execute();

    $this->assertTrue($q->usedMaster());
    $this->assertFalse($q->usedSlave());
  }

  function testUpdate() {
    $q = $this->db->prepare("UPDATE test_table SET id=1 WHERE id=2");
    $q->execute();

    $this->assertTrue($q->usedMaster());
    $this->assertFalse($q->usedSlave());
  }

  function testDelete() {
    $q = $this->db->prepare("DELETE FROM test_table WHERE id=2");
    $q->execute();

    $this->assertTrue($q->usedMaster());
    $this->assertFalse($q->usedSlave());
  }

  function testSelectThenUpdate() {
    $this->selectUsesMaster(false);
    $this->testUpdate();
    $this->selectUsesMaster(true);
  }

  function testSelectThenSelect() {
    $this->selectUsesMaster(false);
    $this->selectUsesMaster(false);
  }

  function testUpdateOnAnotherTableThenSelect() {
    $q = $this->db->prepare("UPDATE another_table SET id=1 WHERE id=2");
    $q->execute();

    $this->assertTrue($q->usedMaster());
    $this->assertFalse($q->usedSlave());

    $this->selectUsesMaster(false);
  }

  function testUpdateOnAnotherTableThenSelectUsingThatTable() {
    $q = $this->db->prepare("UPDATE another_table SET id=1 WHERE id=2");
    $q->execute();

    $this->assertTrue($q->usedMaster());
    $this->assertFalse($q->usedSlave());

    $q = $this->db->prepare("SELECT * FROM test_table JOIN another_table ON test_table.id=another_table.another_id");
    $q->execute();

    $this->assertTrue($q->usedMaster(), "should have used master");
    $this->assertFalse($q->usedSlave(), "should have not used slave");
  }

  /**
   * Tests {@link ReplicatedConnection#getTable()}.
   */
  function testGetTable() {
    $this->assertEquals("test_table", $this->db->getTable("INSERT INTO test_table SET id=1"));
    $this->assertEquals("test_table", $this->db->getTable("UPDATE test_table SET id=1 WHERE id=2"));
    $this->assertEquals("test_table", $this->db->getTable("DELETE FROM test_table WHERE id=1"));
  }

  /**
   * Tests {@link ReplicatedConnection#getTable()}.
   */
  function testGetTableWhitespace() {
    $this->assertEquals("test_table", $this->db->getTable("INSERT INTO
        test_table
        SET id=1"));
    $this->assertEquals("test_table", $this->db->getTable("
        UPDATE test_table SET id=1 WHERE id=2"));
    $this->assertEquals("test_table", $this->db->getTable("DELETE FROM   test_table   WHERE id=1"));
  }

  /**
   * Tests {@link ReplicatedConnection#usesTable()}.
   */
  function queryUsesTable($query, $table) {
    $this->assertTrue($this->db->usesTable("SELECT * FROM test_table", "test_table"));
    $this->assertFalse($this->db->usesTable("SELECT * FROM test_table", "another_table"));
    $this->assertFalse($this->db->usesTable("SELECT * FROM another_table", "test_table"));

    $this->assertTrue($this->db->usesTable("SELECT * FROM test_table JOIN another_table ON test_table.id=another_table.another_id", "test_table"));
    $this->assertTrue($this->db->usesTable("SELECT * FROM test_table JOIN another_table ON test_table.id=another_table.another_id", "another_table"));

  }

  function testCreateThenSelect() {
    $q = $this->db->prepare("CREATE TABLE test_table (
      id int not null auto_increment primary key,
      name varchar(255) not null,
      created_at timestamp not null default current_timestamp,

      INDEX(name)
    );");
    $q->execute();

    $this->selectUsesMaster(true);
  }

  function testDropTableThenSelect() {
    $q = $this->db->prepare("DROP TABLE test_table;");
    $q->execute();

    $this->selectUsesMaster(true);
  }

  function testShowTablesLike() {
    $q = $this->db->prepare("SHOW TABLES LIKE ?");
    $q->execute();

    $this->selectUsesMaster(false);
  }

}
