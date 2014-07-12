<?php defined('SYSPATH') or die('No direct script access.');

class Model_S1_Customer extends ORM {
	protected $_table_name = 'customer';
	protected $_db_group = 's1';
	
	protected $_table_columns = array(
			"id" => array("type" => "int"),
			"cust_cd" => array("type" => "string"),
			"cust_company_name" => array("type" => "string"),
			"cust_contact_name" => array("type" => "string"),
			"cust_tel" => array("type" => "string"),
			"cust_post_cd" => array("type" => "string"),
			"cust_post_address1" => array("type" => "string"),
			"cust_post_address2" => array("type" => "string"),
			"create_by" => array("type" => "string"),
			"create_date" => array("type" => "timestamp"),
			"modify_by" => array("type" => "string"),
			"website" => array("type" => "string"),
	);
	
	const SALE_CHK_REF_YAHOO = 0;
	const SALE_CHK_REF_AUTO = 1;
	
	public function rules() {
		return array(
		);
	}
}