<?php defined('SYSPATH') or die('No direct script access.');

class Model_TempProductMaster extends ORM {
	
	const STATUS_ACTIVE = 'A';
	const STATUS_INACTIVE = 'I';
	
	protected $_table_name = 'temp_product_master';
	
	protected $_primary_key = 'no_jp';
	
	protected $_table_columns = array(
			"id" => array("type" => "int"),
			"order_product_id" => array("type" => "int"),
			"no_jp" => array("type" => "string"),
			"made" => array("type" => "string"),
			"model" => array("type" => "string"),
			"model_no" => array("type" => "string"),
			"year" => array("type" => "string"),
			"material" => array("type" => "string"),
			"product_desc" => array("type" => "string"),
			"pcs" => array("type" => "int"),
			"colour" => array("type" => "string"),
			"colour_no" => array("type" => "string"),
			"kaito" => array("type" => "double"),
			"supplier" => array("type" => "string"),
			"business_price" => array("type" => "double"),
			"other" => array("type" => "double"),
			"accessory_remark" => array("type" => "string"),
			"status" => array("type" => "string"),
			"auction_price" => array("type" => "double"),
			"kaito_price" => array("type" => "double"),
	);
}