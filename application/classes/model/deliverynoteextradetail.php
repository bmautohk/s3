<?php defined('SYSPATH') or die('No direct script access.');

class Model_DeliveryNoteExtraDetail extends ORM {
	public $_table_name = 'delivery_note_extra_detail';
	
	const CURRENCY_RMB = 'RMB';
	const CURRENCY_JPY = 'JPY';
	
	protected $_belongs_to = array('deliveryNote' => array('model'=>'deliveryNote', 'foreign_key'=>'delivery_note_id'));
	
	protected $_table_columns = array(
			"id" => array("type" => "int"),
			"delivery_note_id" => array("type" => "int"),
			"description" => array("type" => "string"),
			"total" => array("type" => "double"),
			"currency" => array("type" => "string"),
			"remark" => array("type" => "double")
	);
	
	public function rules() {
		return array(
		);
	}

}