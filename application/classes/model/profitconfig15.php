<?php defined('SYSPATH') or die('No direct script access.');

class Model_ProfitConfig15 extends ORM {
	protected $_table_name = 'profit_config_15';
	protected $_primary_key = 'code';
	
	const CODE_JP_DELIVERY_FEE = "JP_DELIVERY_FEE";
	const CODE_TAX_RATE = "TAX_RATE";
	
	protected $_table_columns = array(
			"code" => array("type" => "int"),
			"description" => array("type" => "string"),
			"value" => array("type" => "string"),
	);
	
	public function rules() {
		return array(
				'value' => array(
						array('not_empty'),
						array('numeric'),
				),
		);
	}
	
	public static function getTaxRate() {
		$taxRateConfig = new Model_ProfitConfig15(Model_ProfitConfig15::CODE_TAX_RATE);
		return $taxRateConfig->value / 100.0;
	}
}