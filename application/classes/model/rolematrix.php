<?php defined('SYSPATH') or die('No direct script access.');

class Model_RoleMatrix extends ORM {
	public $_table_name = 'role_matrix';
	
	protected $_table_columns = array(
			"id" => array("type" => "int"),
			"role_code" => array("type" => "string"),
			"category" => array("type" => "string"),
			"page" => array("type" => "string"),
			"permission" => array("type" => "string")
	);
	
	const PERMISSION_READ = 'R';
	const PERMISSION_WRITE = 'W';
	
	public function rules() {
		return array(
		);
	}
}