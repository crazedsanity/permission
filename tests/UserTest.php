<?php

use \crazedsanity\core\ToolBox;
use \crazedsanity\permission\user;
use \crazedsanity\permission\permission;

class TestOfUser extends crazedsanity\database\TestDbAbstract {
	
	
	public function setUp() {
		parent::internal_connect_db('mysql', 'root');
		$this->assertEquals('mysql', $this->type, "unexpected type, expected 'mysql', got '". $this->type ."'");
		$this->assertTrue(is_object($this->dbObj));
		$this->assertTrue($this->dbObj->is_connected());
		$this->assertEquals(1, parent::reset_db(__DIR__ .'/../setup/schema.my.sql'));
	}
	
	
	public function test_getObject() {
		$o = new user($this->dbObj, 2);
		$p = new permission($this->dbObj);
		
		$o->create(__METHOD__ ."1", "123");
		
		$this->assertEquals($p->getObject(__METHOD__ ."1"), $o->getObject(__METHOD__ ."1"));
		
		$p->create(__METHOD__ ."2", 2, 0, "123");
		
		$this->assertEquals($p->getObject(__METHOD__ ."2"), $o->getObject(__METHOD__ ."2"));
	}
	
	
	public function test_rwx() {
		$uid = 2;
		$gid = 30;
		$o = new user($this->dbObj, $uid, $gid);
		$p = new permission($this->dbObj);
		
		$objId = $o->create(__METHOD__, "777");
		$this->assertTrue(is_numeric($objId));
		$record = $p->get($objId);
		$this->assertTrue(is_array($record));
		$this->assertTrue(isset($record['perms']));
		
		$this->assertEquals($o->get($objId), $p->get($objId));
		
		$this->assertTrue($o->canRead(__METHOD__));
		$this->assertTrue($o->canWrite(__METHOD__));
		$this->assertTrue($o->canExecute(__METHOD__));
	}
}