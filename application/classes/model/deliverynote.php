<?php defined('SYSPATH') or die('No direct script access.');

class Model_DeliveryNote extends ORM {
	public $_table_name = 'delivery_note';
	
	protected $_belongs_to = array('customer' => array('model'=>'customer', 'foreign_key'=>'customer_id'));
	
	protected $_has_many = array('deliveryNoteDetails' => array('model'=>'deliveryNoteDetail', 'foreign_key'=>'delivery_note_id'));
	
	protected $_table_columns = array(
			"id" => array("type" => "int"),
			"delivery_note_no" => array("type" => "string"),
			"customer_id" => array("type" => "int"),
			"total_detail_amt" => array("type" => "double"),
			"total_tax" => array("type" => "double"),
			"total_amt" => array("type" => "double"),
			"rmb_to_jpy_rate" => array("type" => "double"),
			"rmb_to_usd_rate" => array("type" => "double"),
			"tax_rate" => array("type" => "double"),
			"office_address_id" => array("type" => "int"),
			"delivery_method_id" => array("type" => "int"),
			"delivery_method" => array("type" => "string"),
			"invoice_id" => array("type" => "int"),
			"created_by" => array("type" => "string"),
			"create_date" => array("type" => "timestamp"),
			"print_date" => array("type" => "date"),
			"last_print_date" => array("type" => "timestamp"),
	);
	
	public function rules() {
		return array(
		);
	}

}