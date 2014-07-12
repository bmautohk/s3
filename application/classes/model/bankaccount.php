<?php defined('SYSPATH') or die('No direct script access.');

class Model_BankAccount extends ORM {
	public $_table_name = 'bank_account';
	
	protected $_table_columns = array(
			"id" => array("type" => "int"),
			"bank_name" => array("type" => "string"),
			"branch" => array("type" => "string"),
			"txn_type" => array("type" => "string"),
			"account_no" => array("type" => "string"),
			"owner" => array("type" => "string"),
	);
	
	public function rules() {
		return array(
				'bank_name'  => array(
						array('not_empty')
				),
				'owner' => array(
						array('not_empty')
				),
		);
	}
	
	public function get_txn_type_description() {
		$description = '';
		
		switch ($this->txn_type) {
			case 'cash': 
					$description = '普通口座';
					break;
			case 'tt':
					$description = 'tt';
					break;
			case 'cheque':
					$description = 'Cheque';
					break;
			default:
		}
		
		return $description; 
	}
	
	public static function getTransactionTypeOptions() {
		return array(
				'cash' => 'Cash',
				'tt' => 'tt',
				'cheque' => 'Cheque',
			);
	}

	public static function getOptions() {
		$banks = ORM::factory('bankAccount')
				->order_by('bank_name')
				->find_all();
		
		$options = array();
		foreach ($banks as $bank) {
			$options[$bank->id] = $bank->bank_name.' '.$bank->branch.' '.$bank->account_no.' ('.$bank->owner.')';
		}
		
		//return $banks->as_array('id', 'bank_name');
		
		return $options;
	}
}