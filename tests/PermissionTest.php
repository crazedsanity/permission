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
	
	
	public function test_validPerms() {
		$o = new permission($this->dbObj);
		
		
		
		$allValidPermissions = array(
			'0', '00', '000',
			'1', '11', '111', '01', '001', '10', '010', '100', '011', '110',
			'2', '22', '222', '02', '002', '20', '020', '200', '022', '220',
			'3', '33', '333', '03', '003', '30', '030', '300', '033', '330',
			'4', '44', '444', '04', '004', '40', '040', '400', '044', '440',
			'5', '55', '555', '05', '005', '50', '050', '500', '055', '550',
			'6', '66', '666', '06', '006', '60', '060', '600', '066', '660',
			'7', '77', '777', '07', '007', '70', '070', '700', '077', '770',
			'12', '120', '012', '121', '122', '123', '124', '125', '126', '127',
			'13', '130', '013', '131', '132', '133', '134', '135', '136', '137',
			'14', '140', '014', '141', '142', '143', '144', '145', '146', '147',
			'15', '150', '015', '151', '152', '153', '154', '155', '156', '157',
			'16', '160', '016', '161', '162', '163', '164', '165', '166', '167',
			'17', '170', '017', '171', '172', '173', '174', '175', '176', '177',
			'21', '210', '021', '211', '212', '213', '214', '215', '216', '217',
			'23', '230', '023', '231', '232', '233', '234', '235', '236', '237',
			'24', '240', '024', '241', '242', '243', '244', '245', '246', '247',
			'25', '250', '025', '251', '252', '253', '254', '255', '256', '257',
			'26', '260', '026', '261', '262', '263', '264', '265', '266', '267',
			'27', '270', '027', '271', '272', '273', '274', '275', '276', '277',
			'31', '310', '031', '311', '312', '313', '314', '315', '316', '317',
			'32', '320', '032', '321', '322', '323', '324', '325', '326', '327',
			'34', '340', '034',
			'35', '350', '035',
			'36', '360', '036',
			'37', '370', '037',
			'41', '410', '041',
			'42', '420', '042',
			'43', '430', '043',
			'45', '450', '045',
			'46', '460', '046',
			'47', '470', '047',
			'51', '510', '051',
			'52', '520', '052',
			'53', '530', '053',
			'54', '540', '054', 
			'56', '560', '056',
			'57', '570', '057',
			'61', '610', '061',
			'62', '620', '062',
			'63', '630', '063',
			'64', '640', '064',
			'65', '650', '065',
			'67', '670', '067',
		);
		
		
		$didItAlready = array();
		$uniqPerms = array();
		
//		$this->assertEquals(73, count($allValidPermissions));
		
		foreach($allValidPermissions as $i => $tryThis) {
			$this->assertTrue(!isset($didItAlready[$tryThis]), "tried testing a permission twice (". $tryThis .")");
			$didItAlready[$tryThis] = $i;
			
			$translated = permission::translate_perms($tryThis);
			$this->assertEquals(intval($tryThis), intval($translated));
			$this->assertEquals(3, strlen($translated), "invalid translated length (". $translated .") from (". $tryThis .")");
			
			// if this already exists, we should get an exception... test for that.
			$newObjName = __METHOD__ ."__". $translated;
			if(isset($uniqPerms[$translated])) {
				try {
					$o->create($newObjName, $i, 2, $tryThis);
					$this->assertTrue(false, "created");
				} catch (Exception $ex) {
					// that's okay, it was not supposed to work.
				}
			}
			else {
				//
				$o->create($newObjName, $i, 2, $tryThis);
			}
			$uniqPerms[$translated] = $i;
		}

	}
	
}