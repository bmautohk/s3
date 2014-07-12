<?php defined('SYSPATH') or die('No direct script access.');

class Model_InvoiceSettleDetail extends ORM {
	public $_table_name = 'invoice_settle_detail';
	
	protected $_table_columns = array(
			"id" => array("type" => "int"),
			"invoice_settle_id" => array("type" => "int"),
			"invoice_detail_id" => array("type" => "int"),
			"settle_amt" => array("type" => "double"),
	);
	
	public function rules() {
		return array(
		);
	}

}
