<?php defined('SYSPATH') or die('No direct script access.');

class Model_S1_BenDebt extends ORM {
	protected $_table_name = 'ben_debt';
	protected $_db_group = 's1';
	
	protected $_primary_key = 'debt_ref';
	
	protected $_table_columns = array(
			"debt_ref" => array("type" => "string"),
			"debt_tel" => array("type" => "string"),
			"debt_cust_address1" => array("type" => "string"),
			"debt_cust_address2" => array("type" => "string"),
			"debt_cust_address3" => array("type" => "string"),
			"debt_post_co" => array("type" => "date"),
			"debt_remark" => array("type" => "string"),
	);
	
	public function rules() {
		return array(
		);
	}
}
