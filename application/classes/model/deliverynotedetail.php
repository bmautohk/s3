<?php defined('SYSPATH') or die('No direct script access.');

class Model_DeliveryNoteDetail extends ORM {
	public $_table_name = 'delivery_note_detail';
	
	const SOURCE_CONTAINER = 'CONTAINER';
	const SOURCE_DELIVERY_FEE = 'DELIVERY_FEE';
	const SOURCE_ORDER_RETURN = 'ORDER_RETURN';
	
	const CURRENCY_RMB = 'RMB';
	const CURRENCY_JPY = 'JPY';
	
	const TABLE_ORDER_RETURN = "ORDER_RETURN";
	const TABLE_DELIVERY_FEE = "ORDER_PRODUCT";

	protected $_belongs_to = array('container' => array('model'=>'container', 'foreign_key'=>'container_id'),
									'deliveryNote' => array('model'=>'deliveryNote', 'foreign_key'=>'delivery_note_id'),
								);
	
	protected $_table_columns = array(
			"id" => array("type" => "int"),
			"delivery_note_id" => array("type" => "int"),
			"container_id" => array("type" => "int"),
			"reference_id" => array("type" => "int"),
			"reference_table" => array("type" => "string"),
			"product_cd" => array("type" => "string"),
			"description" => array("type" => "string"),
			"qty" => array("type" => "int"),
			"market_price" => array("type" => "double"),
			"total" => array("type" => "double"),
			"currency" => array("type" => "string"),
			"is_tax" => array("type" => "int"),
			"remark" => array("type" => "string"),
			"source" => array("type" => "int"),
	);
	
	public function rules() {
		return array(
		);
	}

}