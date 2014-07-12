<?php defined('SYSPATH') or die('No direct script access.');

class Model_S1_BenSaleProd extends ORM {
	protected $_table_name = 'ben_sale_prod';
	protected $_db_group = 's1';
	
	protected $_primary_key = 'sprod_no';
	
	protected $_table_columns = array(
			"sprod_no" => array("type" => "int"),
			"sprod_ref" => array("type" => "string"),
			"sprod_id" => array("type" => "string"),
			"sprod_name" => array("type" => "string"),
			"sprod_price" => array("type" => "double"),
			"sprod_unit" => array("type" => "int"),
	);

	public function rules() {
		return array(
		);
	}
}