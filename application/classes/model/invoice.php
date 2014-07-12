<?php defined('SYSPATH') or die('No direct script access.');

class Model_Invoice extends ORM {
	const SETTLED_YES = 'Y';
	const SETTLED_NO = 'N';
	
	public $_table_name = 'invoice';
	
	protected $_belongs_to = array('customer' => array('model'=>'customer', 'foreign_key'=>'customer_id'));
	
	protected $_has_many = array('invoiceDetails' => array('model'=>'invoiceDetail', 'foreign_key'=>'invoice_id'));
	
	protected $_table_columns = array(
			"id" => array("type" => "int"),
			"invoice_no" => array("type" => "string"),
			"customer_id" => array("type" => "int"),
			"bill_date_from" => array("type" => "date"),
			"bill_date_to" => array("type" => "date"),
			"due_date" => array("type" => "date"),
			"bank_id" => array("type" => "int"),
			"last_month_amt" => array("type" => "double"),
			"last_month_settle" => array("type" => "double"),
			"current_month_amt" => array("type" => "double"),
			"total_tax" => array("type" => "double"),
			"total_amt" => array("type" => "double"),
			"settle_amt" => array("type" => "double"),
			"rmb_to_jpy_rate" => array("type" => "double"),
			"rmb_to_usd_rate" => array("type" => "double"),
			"office_address_id" => array("type" => "int"),
			"last_print_date" => array("type" => "date"),
			"is_settle" => array("type" => "string"),
			"delivery_note_id_list" => array("type" => "string"),
			"create_date" => array("type" => "timestamp"),
	);
	
	public function rules() {
		return array(
		);
	}
	
	public function getInvoiceAmount() {
		return $this->last_month_amt - $this->last_month_settle + $this->current_month_amt;
	}

}