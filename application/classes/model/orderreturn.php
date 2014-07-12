<?php defined('SYSPATH') or die('No direct script access.');

class Model_OrderReturn extends ORM {
	public $_table_name = 'order_return';
	
	const STATUS_CANCEL = 0;
	const STATUS_INIT = 10;
	const STATUS_READY_FOR_DELIVERY_NOTE = 20;
	const STATUS_COMPLETE = 99;
	
	
	protected $_belongs_to = array('customer' => array('model'=>'customer', 'foreign_key'=>'customer_id'));
	
	protected $_table_columns = array(
			"id" => array("type" => "int"),
			"customer_id" => array("type" => "int"),
			"product_cd" => array("type" => "string"),
			"return_qty" => array("type" => "int"),
			"return_pay" => array("type" => "double"),
			"remark" => array("type" => "string"),
			"return_date" => array("type" => "date"),
			"rmb_to_jpy_rate" => array("type" => "double"),
			"status" => array("type" => "string"),
			"created_by" => array("type" => "string"),
			"create_date" => array("type" => "date"),
	);
	
	public function rules() {
		return array(
				'product_cd' => array(
						array('not_empty')
				),
				'return_qty' => array(
						array('not_empty'),
						array('digit'),
						array('CustomValidation::positive')
				),
				'return_pay' => array(
						array('not_empty'),
						array('numeric'),
						array('CustomValidation::positive')
				),
		);
	}
	
	public function isConfirm() {
		return $this->status >= self::STATUS_READY_FOR_DELIVERY_NOTE || $this->status == self::STATUS_CANCEL;
	}
	
	function getStatus() {
		switch ($this->status) {
			case self::STATUS_CANCEL:
				return 'Cancel';
			case self::STATUS_INIT:
				return 'Waiting for confirm';
			case self::STATUS_READY_FOR_DELIVERY_NOTE:
				return 'Confirmed';
			case self::STATUS_COMPLETE:
				return 'Confirmed';
			default:
				return '';
		}
	}
	
	public function getReturnPayJPY() {
		return GlobalFunction::convertRMB2JPY($this->return_pay, $this->rmb_to_jpy_rate);
	}
}