<?php

use \crazedsanity\core\ToolBox;
use \crazedsanity\permission\permission;

class TestOfDatabase extends crazedsanity\database\TestDbAbstract {
	
	
	public function setUp() {
		parent::internal_connect_db('mysql', 'root');
		$this->assertEquals('mysql', $this->type, "unexpected type, expected 'mysql', got '". $this->type ."'");
		$this->assertTrue(is_object($this->dbObj));
		$this->assertTrue($this->dbObj->is_connected());
		$this->assertEquals(1, parent::reset_db(__DIR__ .'/../setup/schema.my.sql'));
	}
	
	
	public function test_create() {
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
	
	
	/**
	 * @expectedException InvalidArgumentException
	 */
	public function test_invalidCreate() {
		$x = new permission($this->dbObj);
		$x->create(__METHOD__, 1, 2, 9999);
	}
	
	
	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage user id must be zero or greater
	 */
	public function test_invalidUid() {
		$x = new permission($this->dbObj);
		$x->create(__METHOD__, null, 0, 777);
	}
	
	
	public function test_zeroPadding() {
		$this->assertEquals('000', permission::translate_perms('0'));
		$this->assertEquals('000', permission::translate_perms(0));
		
		$this->assertEquals('000', permission::translate_perms('00'));
		$this->assertEquals('000', permission::translate_perms(00));
		
		$this->assertEquals('000', permission::translate_perms('000'));
		$this->assertEquals('000', permission::translate_perms(000));
	}
	
	
	
	
	public function test_translateNullPermission() {
		$this->assertEquals('000', permission::translate_perms(null));
	}
	
	
	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage invalid permission
	 */
	public function test_translateInvalidPermission() {
		permission::translate_perms(999);
	}
	
	
	/**
	 * Make sure that a permission that *contains* a permission isn't evaluated 
	 * as valid (the whole thing must be valid)
	 * 
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage invalid permission
	 */
	public function test_translateInvalidLongPermission() {
		permission::translate_perms(7777);
	}
	
	
	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage group id must be zero or greater
	 */
	public function test_invalidGid() {
		$x = new permission($this->dbObj);
		$x->create(__METHOD__, 0, null, 777);
	}
	
	
	public function test_crud() {
		$o = new permission($this->dbObj);
		
		$theId = $o->create(__METHOD__, 1, 2, 123);
		$this->assertEquals(1, $theId);
		
		$updateRes = $o->update($theId, array(
			'user_id'	=> 7,
			'group_id'	=> 8,
			'perms'		=> 456,
		));
		$this->assertEquals(1, $updateRes);
		
		//see that we get 0 from MySQL for trying to do an identical update again.
		$this->assertEquals('mysql', $this->dbObj->get_dbtype());
		$dupe = $o->update($theId, array(
			'user_id'	=> 7,
			'group_id'	=> 8,
			'perms'		=> 456,
		));
		$this->assertEquals(0, $dupe);
		
		$delRes = $o->delete($theId);
		$this->assertEquals(1, $delRes);
		$this->assertEquals(array(), $o->get($theId));
	}
	
	
	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage invalid perm
	 */
	public function test_updateWithInvalidPerm() {
		$o = new permission($this->dbObj);
		$theId = $o->create(__METHOD__, 1, 2, 123);
		$this->assertEquals(1, $theId);
		
		$updateRes = $o->update($theId, array(
			'user_id'	=> 7,
			'group_id'	=> 8,
			'perms'		=> 77777,
		));
		
	}
	
	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage unknown column 'uid'
	 */
	public function test_updateWithInvalidColumn() {
		$o = new permission($this->dbObj);
		$theId = $o->create(__METHOD__, 1, 2, 123);
		$this->assertEquals(1, $theId);
		
		// here's the exception causer:
		$o->update($theId, array('uid'	=> 7,));
	}
	
	
	public function test_validPerms() {
		$o = new permission($this->dbObj);
		
		//NOTE: this seems overly manual... 
		$i=0;
		$total = 1; // start at one instead of zero, because we have to create the "000" permission first.
		$lastId = $o->create(__METHOD__ .'-'. $i, 1, 2, $i);
		while($i < 777) {
			$testPerm = $i;
			$this->assertTrue(is_numeric($testPerm));
			$this->assertEquals(intval($i), intval(permission::translate_perms($i)));
			$this->assertEquals(strval($i), intval(permission::translate_perms($i)));
			
			$permBits = str_split(permission::translate_perms($testPerm));
			
			if(intval($permBits[2]) < 7) {
				$permBits[2] = intval($permBits[2]) +1;
			}
			else {
				$permBits[2] = 0;
				if(intval($permBits[1]) < 7) {
					$permBits[1] = intval($permBits[1]) +1;
				}
				else {
					$permBits[1] = 0;
					if(intval($permBits[0]) < 7) {
						$permBits[0] = intval($permBits[0]) +1;
					}
					else {
						throw new LogicException(__METHOD__ ." - the cuckoo flew the nest ({$testPerm})... ". print_r($permBits, true));
					}
				}
			}
			
			foreach($permBits as $k=>$v) {
				$this->assertTrue(permission::is_valid_perm($v));
			}
			
			$i = intval(implode('', $permBits));
			
			$testId = $o->create(__METHOD__ ."-". $i, 1, 2, $i);
			$this->assertTrue(is_numeric($testId));
			$this->assertTrue($testId > 0);
			$this->assertNotEquals($lastId, $testId);
			$lastId = $testId;
			
			$total++;
		}
		
		$this->assertEquals(512, $total, "wrong number of total permissions created, expected 512, got (". $total .")");
	}
	
}