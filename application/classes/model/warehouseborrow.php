<?php defined('SYSPATH') or die('No direct script access.');

class Model_WarehouseBorrow extends ORM {
	public $_table_name = 'warehouse_borrow';

	protected $_table_columns = array(
			"id" => array("type" => "int"),
			"order_product_id" => array("type" => "int"),
			"container_id" => array("type" => "int"),
			"borrow_date" => array("type" => "date"),
			"qty" => array("type" => "int"),
			"remark" => array("type" => "string"),
			"created_by" => array("type" => "string"),
			"create_date" => array("type" => "timestamp"),
	);

	public function rules() {
		return array(
		);
	}
}