<?php defined('SYSPATH') or die('No direct script access.');

class Model_ShippingFeeDeliveryNote extends ORM {
	
	const SETTLE_YES = 'Y';
	const SETTLE_NO = 'N';
	const SETTLE_VOID = 'V';
	
	public $_table_name = 'shipping_fee_delivery_note';
	
	protected $_belongs_to = array('customer' => array('model'=>'customer', 'foreign_key'=>'customer_id'));
	
	protected $_table_columns = array(
			"id" => array("type" => "int"),
			"customer_id" => array("type" => "int"),
			"total_amt" => array("type" => "double"),
			"rmb_to_jpy_rate" => array("type" => "double"),
			"rmb_to_usd_rate" => array("type" => "double"),
			"is_settle" => array("type" => "string"),
			"remark" => array("type" => "string"),
			"created_by" => array("type" => "string"),
			"create_date" => array("type" => "timestamp"),
			"last_print_date" => array("type" => "timestamp"),
			"settle_date" => array("type" => "timestamp"),
			"void_date" => array("type" => "timestamp"),
	);
	
	public function rules() {
		return array(
		);
	}

}