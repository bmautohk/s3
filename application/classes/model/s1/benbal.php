<?php defined('SYSPATH') or die('No direct script access.');

class Model_S1_BenBal extends ORM {
	protected $_table_name = 'ben_bal';
	protected $_db_group = 's1';
	
	protected $_primary_key = 'bal_ref';
	
	protected $_table_columns = array(
			"bal_ref" => array("type" => "string"),
			"bal_pay" => array("type" => "double"),
			"bal_dat" => array("type" => "date"),
			"bal_pay_type" => array("type" => "string"),
			"bal_ship_type" => array("type" => "string"),
	);
	
	public function rules() {
		return array(
		);
	}
}
