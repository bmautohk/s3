<?php defined('SYSPATH') or die('No direct script access.');

class Model_OrderReturnInvoiceDetail extends ORM {
	public $_table_name = 'order_return_invoice_detail';
	
	const SOURCE_CONTAINER = 0;
	const SOURCE_DELIVERY_FEE = 1;
	const SOURCE_ORDER_RETURN = 2;
	
	protected $_table_columns = array(
			"id" => array("type" => "int"),
			"order_return_invoice_id" => array("type" => "int"),
			"delivery_note_detail_id" => array("type" => "int"),
			"product_cd" => array("type" => "string"),
			"description" => array("type" => "string"),
			"qty" => array("type" => "int"),
			"market_price_rmb" => array("type" => "double"),
			"market_price" => array("type" => "double"),
			"total" => array("type" => "double"),
			"remark" => array("type" => "string"),
	);
	
	public function rules() {
		return array(
		);
	}

}