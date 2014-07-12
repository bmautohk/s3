<?php defined('SYSPATH') or die('No direct script access.');

class Model_Gift extends ORM {
	public $_table_name = 'gift';
	
	const STATUS_INIT = 10;
	const STATUS_ACCOUNTANT= 70;
	const STATUS_DELIVERY_NOTE_GENERATED = 71;
	const STATUS_INVOICE_GENERATED = 72;
	const STATUS_COMPLETE = 99;
	
	protected $_table_columns = array(
			"id" => array("type" => "int"),
			"factory" => array("type" => "string"),
			"customer_id" => array("type" => "int"),
			"delivery_date" => array("type" => "date"),
			"container_input_date" => array("type" => "date"),
			"container_no" => array("type" => "string"),
			"delivery_qty" => array("type" => "int"),
			"made" => array("type" => "string"),
			"model" => array("type" => "string"),
			"model_no" => array("type" => "string"),
			"colour" => array("type" => "string"),
			"colour_no" => array("type" => "string"),
			"qty" => array("type" => "int"),
			"product_cd" => array("type" => "string"),
			"product_desc" => array("type" => "string"),
			"material" => array("type" => "string"),
			"picture1" => array("type" => "string"),
			"picture2" => array("type" => "string"),
			"picture3" => array("type" => "string"),
			"cost" => array("type" => "double"),
			"status" => array("type" => "int"),
			"created_by" => array("type" => "string"),
			"create_date" => array("type" => "timestamp"),
			"last_updated_by" => array("type" => "string"),
			"last_update_date" => array("type" => "timestamp"),
	);
	
	public function rules() {
		return array(
		);
	}
	
	public static function getFactoryCode($name) {
		if ($name == 'ben') {
			return Model_OrderProduct::FACTORY_BEN;
		} else if ($name == 'gz') {
			return Model_OrderProduct::FACTORY_GZ;
		} else {
			return '';
		}
	}

}