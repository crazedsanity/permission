<?php

namespace crazedsanity\permission;

use crazedsanity\database\Database;

class permission {
	
	protected $db;
	
	const TABLE = 'permission_table';
	const PKEY = 'permission_id';
	const SEQ = 'permission_table_permission_id_pkey';//only used for PostgreSQL
	
	const EXECUTE = 1;
	const WRITE = 2;
	const READ = 4;
	
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
		$sql = 'INSERT INTO '. self::TABLE .' (object, user_id, group_id, perms) 
			VALUES (:object, :uid, :gid, :perms)';
		
		
		$params = array(
			'object'	=> $object,
			'uid'		=> $uid,
			'gid'		=> $gid,
			'perms'		=> $perms,
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
	public function translate_perms($perms) {
		if(preg_match('/[0-7]{1,3}/', $perms) == 1) {
			$bits = explode('', $perms);
			
			$newPerms = "000";
			if(count($bits) === 3) {
				$newPerms = $perms;
			}
			elseif(count($bits == 2)) {
				$newPerms = "0". $perms;
			}
			else {
				$newPerms = "00". $perms;
			}
			
		}
		else {
			throw new InvalidArgumentException("invalid permission value ({$perms})");
		}
		return $newPerms;
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
		foreach($changes as $key=>$value) {
			switch($key) {
				case 'object':
				case 'user_id':
				case 'group_id':
					$params[$key] = $value;
					$sql .= "{$key}=:{$key}";
					break;
				case 'perms':
					try {
						$this->translate_perms($value);
						$params[$key] = $value;
						$sql .= "{$key}=:{$key}";
					} catch (Exception $ex) {
						throw new exception("unable to update: ". $ex->getMessage());
					}
					break;
				default:
					throw new InvalidArgumentException("unknown column '". $key ."'");
			}
		}
		
		$params['id'] = $id;
		$sql .= ' WHERE '. self::PKEY .' = :id';
		
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
}