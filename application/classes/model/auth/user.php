<?php
class Model_Auth_User {
	public $username;
	public $role_code;
	public $is_sales;
	public $privilegePages;
	
	public $mainMenu;
	
	/**
	 * Complete the login for a user by incrementing the logins and saving login timestamp
	 *
	 * @return void
	 */
	public function complete_login() {
		// Retrieve user's privilege
		$roleMatrixes = ORM::factory('roleMatrix')
						->where('role_code', '=', $this->role_code)
						->find_all();
			
		$this->privilegePages = array();
		foreach ($roleMatrixes as $roleMatrix) {
			$this->privilegePages[$roleMatrix->page] = $roleMatrix->permission;
		}
		
		// Retrieve main menu's privilege
		$query = DB::select(DB::expr('distinct category'))
						->from('role_matrix')
						->where('role_code', '=', $this->role_code);
		$result = $query->execute();
		
		$this->mainMenu = array();
		foreach($result as $value) {
			$this->mainMenu[$value['category']] = 'Y'; 
		}
	}
	
	public function hasPrivilege($page, $permission=NULL) {
		if ($permission == NULL) {
			return array_key_exists($page, $this->privilegePages);
		} else {
			return array_key_exists($page, $this->privilegePages) && $this->privilegePages[$page] == $permission;
		}
	}
	
	public function isAdmin() {
		return $this->role_code == Model_Role::ROLE_CODE_ADMINISTRATOR;
	}
	
	public function isSales() {
		return $this->is_sales == 'Y' ? true : false;
	}
}