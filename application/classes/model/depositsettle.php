<?php defined('SYSPATH') or die('No direct script access.');

class Model_DepositSettle extends ORM {
	public $_table_name = 'deposit_settle';
	
	protected $_belongs_to = array('order' => array('model' => 'order', 'foreign_key' => 'order_id'));
	
	const CONFIRM_YES = "Y";
	const CONFIRM_NO ="N";
	
	protected $_table_columns = array(
			"id" => array("type" => "int"),
			"order_id" => array("type" => "int"),
			"settle_amt" => array("type" => "double"),
			"fee" => array("type" => "double"),
			"settle_date" => array("type" => "date"),
			"remark" => array("type" => "string"),
			"bank_name" => array("type" => "string"), // obsolete
			"is_confirm" => array("type" => "string"),
			"accountant_remark" => array("type" => "string"),
			"created_by" => array("type" => "string"),
			"create_date" => array("type" => "timestamp"),
	);
	
	public function rules() {
		return array(
				'settle_amt' => array(
						array('not_empty'),
						array('numeric'),
						array('CustomValidation::positive')
				),
				'fee' => array(
						array('not_empty'),
						array('numeric'),
						array('CustomValidation::minValue', array(':value', '0'))
				),
				'settle_date' => array(
						array('not_empty'),
				),
				/* 'bank_name' => array(
						array('not_empty'),
				), */
		);
	}

}
