<?php defined('SYSPATH') or die('No direct script access.');

class Model_OfficeAddress extends ORM {
	public $_table_name = 'office_address';

	protected $_table_columns = array(
			"id" => array("type" => "int"),
			"name" => array("type" => "string"),
			"address" => array("type" => "string"),
			"tel" => array("type" => "string"),
	);
	
	public function rules() {
		return array(
		);
	}

	public static function getOptions() {
		return ORM::factory('officeAddress')->order_by('name')->find_all()->as_array('id', 'name');
	}
}