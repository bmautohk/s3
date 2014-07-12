<?php defined('SYSPATH') or die('No direct script access.');

class Model_Container extends ORM {
	public $_table_name = 'container';
	
	const STATUS_DRAFT = 0;
	const STATUS_INIT = 10;
	const STATUS_READY_FOR_DELIVERY_NOTE = 20;
	const STATUS_DELIVERY_NOTE_GENREATED = 30;
	const STATUS_INVOICE_GENERATED = 40;
	const STATUS_COMPLETE = 99;
	
	const SOURCE_JP = 'J';
	const SOURCE_FACTORY = 'F';
	const SOURCE_GIFT = 'G';
	
	protected $_belongs_to = array('orderProduct' => array('model' => 'orderProduct', 'foreign_key' => 'order_product_id'));
	
	protected $_table_columns = array(
			"id" => array("type" => "int"),
			"order_product_id" => array("type" => "int"),
			"gift_id" => array("type" => "int"),
			"container_no" => array("type" => "string"),
			"delivery_qty" => array("type" => "int"),
			"orig_delivery_qty" => array("type" => "int"),
			"delivery_date" => array("type" => "date"),
			"container_input_date" => array("type" => "date"),
			"source" => array("type" => "string"),
			"status" => array("type" => "int"),
			"created_by" => array("type" => "string"),
			"create_date" => array("type" => "timestamp"),
			"last_updated_by" => array("type" => "string"),
	);
	
	public function rules() {
		return array(
			/*'delivery_qty' => array(
					array('not_empty'),
					array('digit'),
					array('CustomValidation::positive')
			),
			'delivery_date' => array(
					array('not_empty')
			),
			'container_input_date' => array(
					array('not_empty')
			),
			'container_no' => array(
					array('not_empty')
			), */
		);
	}
	
	public function save(Validation $validation = NULL) {
		$this->last_updated_by = Auth::instance()->get_user()->username;
		return parent::save($validation);
	}
}