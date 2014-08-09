<?php defined('SYSPATH') or die('No direct script access.');

class Model_ShippingFee extends ORM {
	public $_table_name = 'shipping_fee';
	
	const STATUS_INIT = 10;
	const STATUS_COMPLETE = 99;
	
	protected $_belongs_to = array('customer' => array('model'=>'customer', 'foreign_key'=>'customer_id'));
	
	protected $_table_columns = array(
			"id" => array("type" => "int"),
			"customer_id" => array("type" => "int"),
			"container_no" => array("type" => "string"),
			"description" => array("type" => "string"),
			"amount" => array("type" => "integer"),
			"remark" => array("type" => "string"),
			"status" => array("type" => "int"),
			"shipping_fee_delivery_note_id" => array("type" => "int"),
			"created_by" => array("type" => "string"),
			"create_date" => array("type" => "timestamp"),
	);
	
	public function rules() {
		return array(
				'customer_id' => array(
						array('not_empty'),
				),
				'amount' => array(
						array('not_empty'),
						array('digit'),
						array('CustomValidation::positive')
				),
		);
	}
}
