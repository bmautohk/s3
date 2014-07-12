<?php defined('SYSPATH') or die('No direct script access.');

class Model_OrderReturnInvoice extends ORM {
	public $_table_name = 'order_return_invoice';
	
	protected $_belongs_to = array('customer' => array('model'=>'customer', 'foreign_key'=>'customer_id'));
	
	protected $_has_many = array('orderreturninvoiceDetails' => array('model'=>'orderReturnInvoiceDetail', 'foreign_key'=>'order_return_invoice_id'));
	
	protected $_table_columns = array(
			"id" => array("type" => "int"),
			"customer_id" => array("type" => "int"),
			"bill_date_from" => array("type" => "date"),
			"bill_date_to" => array("type" => "date"),
			"due_date" => array("type" => "date"),
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