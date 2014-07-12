<?php defined('SYSPATH') or die('No direct script access.');

class Model_Supplier extends ORM {
	public $_table_name = 'supplier';
	
	protected $_table_columns = array(
			"supplier_code" => array("type" => "string"),
			"supplier_name" => array("type" => "string"),
	);
	
	public function rules() {
		return array(
		);
	}

}