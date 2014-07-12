<?php defined('SYSPATH') or die('No direct script access.');

class Model_Profit extends ORM {
	public $_table_name = 'profit';
	
	const ID_JAPAN_DELIVERY_FEE = 1;
	
	protected $_table_columns = array(
			"id" => array("type" => "int"),
			"type" => array("type" => "string"),
			"value" => array("type" => "string"),
	);
	
	public function rules() {
		return array(
		);
	}

}