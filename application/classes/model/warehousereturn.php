<?php defined('SYSPATH') or die('No direct script access.');

class Model_WarehouseReturn extends ORM {
	public $_table_name = 'warehouse_return';

	protected $_table_columns = array(
			"id" => array("type" => "int"),
			"order_product_id" => array("type" => "int"),
			"return_date" => array("type" => "date"),
			"qty" => array("type" => "int"),
			"container_no" => array("type" => "string"),
			"remark" => array("type" => "string"),
	);

	public function rules() {
		return array(
		);
	}
}