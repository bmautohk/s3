<?php defined('SYSPATH') or die('No direct script access.');

class Model_InvoiceDetail extends ORM {
	const SETTLED_YES = 'Y';
	const SETTLED_NO = 'N';
	
	public $_table_name = 'invoice_detail';
	
	const SOURCE_CONTAINER = 'CONTAINER';
	const SOURCE_DELIVERY_FEE = 'DELIVERY_FEE';
	const SOURCE_ORDER_RETURN = 'ORDER_RETURN';
	const SOURCE_ADJUSTMENT = 'ADJUSTMENT';
	const SOURCE_TAX = 'TAX';
	const SOURCE_DEPOSIT = 'DEPOSIT'; // REFERENCE_ID = ORDER.ID
	
	protected $_table_columns = array(
			"id" => array("type" => "int"),
			"invoice_id" => array("type" => "int"),
			"delivery_note_detail_id" => array("type" => "int"),
			"product_cd" => array("type" => "string"),
			"description" => array("type" => "string"),
			"qty" => array("type" => "int"),
			"market_price_rmb" => array("type" => "double"),
			"market_price" => array("type" => "double"),
			"total" => array("type" => "double"),
			"is_tax" => array("type" => "string"),
			"rmb_to_jpy_rate" => array("type" => "double"), // obsolete
			"source" => array("type" => "string"),
			"reference_id" => array("type" => "int"),
			"settle_amt" => array("type" => "double"),
			"is_settle" => array("type" => "string"),
	);
	
	public function rules() {
		return array(
		);
	}

}