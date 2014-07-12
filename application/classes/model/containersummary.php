<?php defined('SYSPATH') or die('No direct script access.');

class Model_ContainerSummary extends ORM {
	public $_table_name = 'container_summary';

	protected $_table_columns = array(
			"id" => array("type" => "int"),
			"order_product_id" => array("type" => "int"),
			"container_no_list" => array("type" => "string"),
			"delivery_date_list" => array("type" => "string"),
			"container_input_date_list" => array("type" => "string"),
			"delivery_qty_list" => array("type" => "string"),
	);

}