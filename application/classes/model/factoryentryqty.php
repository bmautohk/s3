<?php defined('SYSPATH') or die('No direct script access.');

class Model_FactoryEntryQty extends ORM {
	public $_table_name = 'factory_entry_qty';
	
	protected $_table_columns = array(
		"id" => array("type" => "int"),
		"order_product_id" => array("type" => "int"),
		"factory_entry_qty" => array("type" => "int"),
		"created_by" => array("type" => "string"),
		"create_date" => array("type" => "timestamp"),
	);
	
	public function rules() {
		return array(
			'factory_entry_qty' => array(
					array('not_empty'),
					array('digit'),
					array('CustomValidation::positive')
			),
		);
	}
}