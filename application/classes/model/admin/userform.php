<?php
class Model_Admin_UserForm {
	const ACTION_ADD = 'add';
	const ACTION_EDIT = 'edit';
	
	public $username;
	public $password;
	public $role_code;
	public $users;
	
	public $action;
	public $errors;
	
	public function populate($post) {
		$this->action = isset($post['action']) ? $post['action'] : NULL;
		$this->username = isset($post['username']) ? $post['username'] : NULL;
		$this->password = isset($post['password']) ? $post['password'] : NULL;
		$this->role_code = isset($post['role_code']) ? $post['role_code'] : NULL;
	}
	
	public function searchAction() {
		// Retrieve all users
		$this->users = ORM::factory('user')
						->with('role')
						->select('role_name')
						->order_by('username')->find_all();
	}
	
	public function retrieve($username) {
		$user = new Model_User($username);
		if (!$user->loaded()) {
			return false;
		} else {
			$this->username = $user->username;
			$this->role_code = $user->role_code;
			return true;
		}
	}
	
	public function addAction() {
		$result = $this->addUser();
		
		if ($result) {
			$this->searchAction();
		}
		
		return $result;
	}
	
	public function editAction() {
		$result = $this->updateUser();
		
		if ($result) {
			$this->searchAction();
		}
		
		return $result;
	}
	
	public function deleteAction() {
		$result = $this->deleteUser();
		$this->searchAction();
		
		return $result;
	}
	
	private function addUser() {
		
		$user = new Model_User($this->username);
		if ($user->loaded()) {
			$this->errors[] = 'User name has already been used by other. Please change the user name.';
			return false;
		}
		
		$user = new Model_User();
		$user->username = $this->username;
		$user->password = DB::expr('password(\''.$this->password.'\')');
		$user->role_code = $this->role_code;
		$user->create_date = DB::expr('current_timestamp');
		
		$db = Database::instance();
		$db->begin();
		
		try {
			$user->save();
		} catch (ORM_Validation_Exception $e) {
			$errors = $e->errors('admin');
			$db->rollback();
			return false;
		}
		
		$db->commit();
		
		// Clear form
		$this->username = '';
		$this->password = '';
		$this->role_code = '';
		
		
		return true;
	}
	
	private function updateUser() {
		$user = new Model_User($this->username);
		if ($user->loaded()) {
			if ($this->password != '') {
				$user->password = DB::expr('password(\''.$this->password.'\')');
			}
			$user->role_code = $this->role_code;
			
			$db = Database::instance();
			$db->begin();
			try {
				$user->save();
			} catch (ORM_Validation_Exception $e) {
				$errors = $e->errors('admin');
				$db->rollback();
				return false;
			}
			$db->commit();
		} else {
			$this->errors[] = 'User does not exist.';
			return false;
		}
		
		return true;
	}
	
	private function deleteUser() {
		$db = Database::instance();
		$db->begin();
		
		try {
			$user = new Model_User($this->username);
			$user->delete();
		} catch (Exception $e) {
			$db->rollback();
			$this->errors[] = $e->getMessage();
			return false;
		}
		
		$db->commit();
		
		return true;
	}
}