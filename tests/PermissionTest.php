<?php

use \crazedsanity\core\ToolBox;
use \crazedsanity\permission\permission;

class TestOfDatabase extends crazedsanity\database\TestDbAbstract {
	
	
	public function __setup() {
		
	}
	
	
	public function test_create() {
		parent::internal_connect_db('mysql', 'root');
		$this->assertEquals('mysql', $this->type, "unexpected type, expected 'mysql', got '". $this->type ."'");
		$this->assertTrue(is_object($this->dbObj));
		$this->assertTrue($this->dbObj->is_connected());
		$this->assertEquals(1, parent::reset_db(__DIR__ .'/../setup/schema.my.sql'));
		$x = new permission($this->dbObj);
		
		$newId = $x->create(__METHOD__, 1, 2, 777);
		
		$this->assertTrue(is_numeric($newId));
		$this->assertTrue($newId > 0);
		
		// retrieve the information, see that we've got everything we need.
		$data = $x->get($newId);
		$this->assertTrue(is_array($data));
		$this->assertTrue(count($data) > 0);
		
		$this->assertTrue(isset($data['object']));
		$this->assertEquals($data['object'], __METHOD__);
		
		$this->assertTrue(isset($data['user_id']));
		$this->assertEquals($data['user_id'], 1);
		
		$this->assertTrue(isset($data['group_id']));
		$this->assertEquals($data['group_id'], 2);
		
		$this->assertTrue(isset($data['perms']));
		$this->assertEquals($data['perms'], '777');
		
		
		//retrieve based on name, make sure it matches what we got before.
		$this->assertEquals($data, $x->getObject(__METHOD__));
	}
	
	
	
	public function test_invalidCreate() {
		
	}
}