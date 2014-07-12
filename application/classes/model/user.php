<?php defined('SYSPATH') or die('No direct script access.');

class Model_User extends ORM {
	public $_table_name = 'user';
	public $_primary_key = 'username';
	
	protected $_table_columns = array(
			"username" => array("type" => "string"),
			"password" => array("type" => "string"),
			"role_code" => array("type" => "string"),
			"last_login_date" => array("type" => "timestamp"),
			"create_date" => array("type" => "timestamp"),
	);
	
	protected $_belongs_to = array('role' => array('model' => 'role', 'foreign_key' => 'role_code'));

	public function rules() {
		return array(
		);
	}
	
	public static function getOptions($hasAll = false) {
		$customers = ORM::factory('user')->order_by('username')->find_all();
		
		if ($hasAll) {
			return array(0=>'All') + $customers->as_array('username', 'username');
		} else {
			return $customers->as_array('username', 'username');
		}
	}
}