<?php

namespace crazedsanity\permission;

use crazedsanity\database\Database;
use crazedsanity\core\ToolBox;
use crazedsanity\bitwise \Bitwise;

use Exception;
use InvalidArgumentException;

class permission {
	
	protected $db;
	
	const TABLE = 'permission_table';
	const PKEY = 'permission_id';
	const SEQ = 'permission_table_permission_id_pkey';//only used for PostgreSQL
	
	const EXECUTE = 1;
	const WRITE = 2;
	const READ = 4;
	
	//zero-based position for each section of a permission string
	const USER = 0;
	const GROUP = 1;
	const OTHER = 2;
	

	public function __construct(Database $db) {
		$this->db = $db;
	}
	
	
	
	/**
	 * Create a new permission.
	 * 
	 * @param str $object	The object to create permissions for
	 * @param int $uid		The user_id value
	 * @param int $gid		The group_id value
	 * @param int $perms	The permission string; see translate_perms()
	 * @return int			The newly-created ID
	 */
	public function create($object, $uid, $gid, $perms) {
		
		//TODO: test that the "object" meets certain criteria.
		if(!is_numeric($uid) || $uid < 0) {
			throw new InvalidArgumentException("user id must be zero or greater");
		}
		if(!is_numeric($gid) || $gid < 0) {
			throw new InvalidArgumentException("group id must be zero or greater");
		}
		try {
			$newPerms = $this->translate_perms($perms);
		} catch (Exception $ex) {
			throw $ex;
		}
		
		$sql = 'INSERT INTO '. self::TABLE .' (object, user_id, group_id, perms) 
			VALUES (:object, :uid, :gid, :perms)';
		
		
		$params = array(
			'object'	=> $object,
			'uid'		=> $uid,
			'gid'		=> $gid,
			'perms'		=> $newPerms,
		);
		
		$newId = $this->db->run_insert($sql, $params, self::SEQ);
		return $newId;
	}
	
	
	
	/**
	 * Translate permission string; given a non-3-digit number, missing numbers 
	 * are assumed to be zero ("1" == "001", "12" == "012", and "123" == "123").
	 * 
	 * @param int $perms	1-3 digit numeric string, each number being 0-7.
	 * @return string		3-digit numeric string with prefixed zeroes.
	 * @throws InvalidArgumentException
	 */
	public static function translate_perms($perms) {
		
		if(strlen($perms) < 3 && is_int($perms)) {
			trigger_error("using shorthand permission strings can lead to accidental octal conversion - see http://php.net/manual/en/language.types.integer.php ");
		}//@codeCoverageIgnore
		
		// convert the number to a string ("000" becomes "0"; "070" becomes "70"; etc)
		$newPerms = strval(intval($perms));
		
		if(preg_match('/^[0-7]{1,3}$/', $newPerms) == 1) {
			if(strlen($newPerms) < 3) {
				$newPerms = str_repeat('0', 3 - strlen($newPerms)) . "$newPerms";
			}
		}
		else {
			throw new InvalidArgumentException("invalid permission value ({$newPerms})");
		}
		
		return strval($newPerms);
	}
	
	
	
	/**
	 * Tests if the given integer is valid (just one bit, not all three)
	 * 
	 * @param int $perm		Permission bit to test
	 * @return boolean
	 */
	public static function is_valid_perm($perm) {
		$isValid = false;
		if(is_numeric($perm) && $perm >= 0 && $perm <= 7) {
			$isValid = true;
		}
		return $isValid;
	}
	
	
	
	/**
	 * Update the permission value.
	 * 
	 * @param int $id			The ID of the record to update.
	 * @param array $changes	Field=>value array of changes.
	 * 
	 * @return int				Number of records updated; MySQL might return 0 if nothing about the record was actually changed.
	 * 
	 * @throws exception
	 * @throws InvalidArgumentException
	 */
	public function update($id, array $changes) {
		$sql = 'UPDATE '. self::TABLE .' SET ';
		$params = array();
		$changeList = "";
		foreach($changes as $key=>$value) {
			switch($key) {
				case 'object':
				case 'user_id':
				case 'group_id':
					$params[$key] = $value;
					$changeList = ToolBox::create_list($changeList, "{$key}=:{$key}");
					break;
				case 'perms':
					$this->translate_perms($value);
					$params[$key] = $value;
					$changeList = ToolBox::create_list($changeList, "{$key}=:{$key}");
					break;
				default:
					throw new InvalidArgumentException("unknown column '". $key ."'");
			}
		}
		
		$params['id'] = $id;
		$sql .= $changeList .' WHERE '. self::PKEY .' = :id';
		$result = $this->db->run_update($sql, $params);
		return $result;
	}
	
	
	
	/**
	 * Delete the given permission ID.
	 * 
	 * @param int $id	The ID to delete.
	 * @return int		The number of records deleted.
	 */
	public function delete($id) {
		$sql = 'DELETE FROM '. self::TABLE .' WHERE '. self::PKEY .' = :id';
		$params = array('id'=>$id);
		$result = $this->db->run_update($sql, $params);
		return $result;
	}
	
	
	/**
	 * Retrieves record based upon an ID.
	 * 
	 * @param int $id	The permission_id to retrieve
	 * @return array	The record: indexed by column name with corresponding values.
	 */
	public function get($id) {
		$sql = 'SELECT * FROM '. self::TABLE .' WHERE '. self::PKEY .' = :id';
		$params = array('id'=>$id);
		$this->db->run_query($sql, $params);
		
		$result = $this->db->get_single_record();
		
		return $result;
	}
	
	
	
	public function getObject($object) {
		$sql = 'SELECT * FROM '. self::TABLE .' WHERE object=:obj';
		$params = array('obj'=>$object);
		$this->db->run_query($sql, $params);
		
		$result = $this->db->get_single_record();
		
		return $result;
	}
	
	
	
	/**
	 * Determines if the given permission allows access at all.
	 * 
	 * @param int/string $perm	Permission to test (0-7)
	 * @return boolean			True only if it's valid and > 0, false otherwise
	 */
	public static function hasAccess($perm) {
		return (intval($perm) > 0);
	}
	
	
	
	public static function canRead($perm) {
		return Bitwise::canAccess(intval($perm), self::READ);
	}
	
	
	public static function canWrite($perm) {
		return Bitwise::canAccess(intval($perm), self::WRITE);
	}
	
	
	
	public static function canExecute($perm) {
		return Bitwise::canAccess(intval($perm), self::EXECUTE);
	}
	
	
	public static function getPermBits($perm) {
		return str_split(self::translate_perms($perm));
	}
	
	
	public static function getPermissionBit($perm, $bit=self::USER) {
		$permBits = self::getPermBits($perm);
		if(!isset($permBits[$bit])) {
			throw new \InvalidArgumentException("invalid permission bit requested (". $bit .")");
		}
		return $permBits[$bit];
	}
}
