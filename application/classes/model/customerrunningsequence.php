<?php defined('SYSPATH') or die('No direct script access.');

class Model_CustomerRunningSequence extends ORM {
	public $_table_name = 'customer_running_sequence';
	
	public $_primary_key = 'cust_code';
	
	protected $_table_columns = array(
			"cust_code" => array("type" => "string"),
			"delivery_note_seq" => array("type" => "int"),
			"invoice_seq" => array("type" => "int"),
	);
	
	public function rules() {
		return array(
		);
	}
}