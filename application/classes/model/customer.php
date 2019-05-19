<?php defined('SYSPATH') or die('No direct script access.');

class Model_Customer extends ORM {
	public $_table_name = 'customer';
	
	const KAITO_YES = "Y";
	const KAITO_NO = "N";
	
	protected $_table_columns = array(
			"id" => array("type" => "int"),
			"name" => array("type" => "string"),
			"postal_code" => array("type" => "string"),
			"address1" => array("type" => "string"),
			"address2" => array("type" => "string"),
			"address3" => array("type" => "string"),
			"delivery_address" => array("type" => "string"),
			"tel" => array("type" => "string"),
			"email" => array("type" => "string"),
			"cust_code" => array("type" => "string"),
			"manager_name" => array("type" => "string"),
			"contact_person" => array("type" => "string"),
			"remark" => array("type" => "string"),
			"bank_account" => array("type" => "string"),
			"office_address_id" => array("type" => "int"),
			"bank_account_id" => array("type" => "int"),
			"last_order_date" => array("type" => "timestamp"),
			"parent_customer_id" => array("type" => "int"),
			"is_kaito" => array("type" => "string"),
			"sales_code" => array("type" => "string"),
			"created_by" => array("type" => "string"),
			"create_date" => array("type" => "timestamp"),
			"last_updated_by" => array("type" => "string"),
			"last_update_date" => array("type" => "timestamp"),
	);
	
	public function rules() {
		return array(
			'name' => array(
					array('not_empty')
			),
			'cust_code' => array(
					array('not_empty'),
					array(array($this, 'unique'), array('cust_code', ':value')),
			),
		);
	}
	
	public function getS1SalesGroup() {
		return $this->cust_code.'_wholesale';
	}
	
	public static function getOptions($hasAll = false) {
		$user = Auth::instance()->get_user();
		if (!$user->isSales()) {
			$customers = ORM::factory('customer')->order_by('cust_code')->find_all();
		} else {
			$customers = ORM::factory('customer')->where('sales_code', '=', $user->username)->order_by('cust_code')->find_all();
		}

		if ($hasAll) {
			return array(0=>'All') + $customers->as_array('id', 'cust_code');
		} else {
			return $customers->as_array('id', 'cust_code');
		}
	}
}