<?php defined('SYSPATH') or die('No direct script access.');

class Model_InvoiceSettle extends ORM {
	public $_table_name = 'invoice_settle';
	
	protected $_belongs_to = array('invoice' => array('model'=>'invoice', 'foreign_key'=>'invoice_id'));
	
	protected $_table_columns = array(
			"id" => array("type" => "int"),
			"invoice_id" => array("type" => "int"),
			"settle_amt" => array("type" => "double"),
			"fee" => array("type" => "double"),
			"settle_date" => array("type" => "date"),
			"remark" => array("type" => "string"),
			"bank_id" => array("type" => "int"),
			"remaining_amt" => array("type" => "double"),
			"created_by" => array("type" => "string"),
			"create_date" => array("type" => "timestamp"),
	);
	
	public function rules() {
		return array(
				'settle_amt' => array(
						array('not_empty'),
						array('numeric'),
						//array('CustomValidation::positive'),
						array('range', array(':value', -99999999.99, 99999999.99))
				),
				'fee' => array(
						array('not_empty'),
						array('numeric'),
						//array('CustomValidation::minValue', array(':value', '0')),
						array('range', array(':value', -99999999.99, 99999999.99))
				),
				'settle_date' => array(
						array('not_empty'),
				),
				'bank_id' => array(
						array('not_empty'),
				),
		);
	}

}
