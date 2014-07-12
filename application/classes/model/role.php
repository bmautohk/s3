<?php defined('SYSPATH') or die('No direct script access.');

class Model_Role extends ORM {
	
	const ROLE_CODE_ADMINISTRATOR = 'AD';
	const ROLE_CODE_SALES = 'SA';
	
	public $_table_name = 'role';
	public $_primary_key = 'role_code';
	
	protected $_table_columns = array(
			"role_code" => array("type" => "string"),
			"role_name" => array("type" => "string")
	);
	
	public function rules() {
		return array(
		);
	}
	
	public static function getRoleOptions() {
		$roles = ORM::factory('role')->order_by('role_Name')->find_all();
		
		$options = array();
		foreach ($roles as $role) {
			$options[$role->role_code] = $role->role_name;
		}
		
		return $options;
	}
}