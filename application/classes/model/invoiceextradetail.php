<?php defined('SYSPATH') or die('No direct script access.');

class Model_InvoiceExtraDetail extends ORM {
	public $_table_name = 'invoice_extra_detail';
	
	protected $_table_columns = array(
			"id" => array("type" => "int"),
			"invoice_id" => array("type" => "int"),
			"delivery_note_extra_detail_id" => array("type" => "int"),
			"description" => array("type" => "string"),
			"total" => array("type" => "double"),
			"currency" => array("type" => "string"),
			"rmb_to_jpy_rate" => array("type" => "double"), // obsolete 
	);
	
	public function rules() {
		return array(
		);
	}

}