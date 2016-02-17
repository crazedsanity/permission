<?php

namespace crazedsanity\permission;

use crazedsanity\database\Database;
use crazedsanity\permission\permission;

class user {
	
	protected $p;
	protected $uid;
	protected $gid;
	
	public function __construct(Database $db, $userId, $groupId=0) {
		$this->p = new permission($db);
		$this->uid = intval($userId);
		$this->gid = intval($groupId);
	}
	
	
	public function create($object, $perms) {
		return $this->p->create($object, $this->uid, $this->gid, $perms);
	}
	
	
	public function get($objId) {
		return $this->p->get($objId);
	}
	
	
	public function getObject($object) {
		return $this->p->getObject($object);
	}
	
	
	public function canRead($object) {
		$data = $this->p->getObject($object);
		$theBit = permission::getPermissionBit($data['perms'], permission::USER);
		return permission::canRead($theBit);
	}
	
	
	public function canWrite($object) {
		$data = $this->p->getObject($object);
		$theBit = permission::getPermissionBit($data['perms'], permission::USER);
		return permission::canWrite($theBit);
	}
	
	
	public function canExecute($object) {
		$data = $this->p->getObject($object);
		$theBit = permission::getPermissionBit($data['perms'], permission::USER);
		return permission::canExecute($theBit);
	}
}
