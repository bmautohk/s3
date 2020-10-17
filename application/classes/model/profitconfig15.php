<?php defined('SYSPATH') or die('No direct script access.');

class Model_ProfitConfig15 extends ORM {
	protected $_table_name = 'profit_config_15';
	protected $_primary_key = 'code';
	
	const CODE_JP_DELIVERY_FEE = "JP_DELIVERY_FEE";
	const CODE_TAX_RATE = "TAX_RATE";
	
	//add 3 para 20201016
	const CODE_AUCTION_VALUE_NUMBER = "AUCTION_VALUE_NUMBER";
	const CODE_KAITO_PRICING_NUMBER = "KAITO_PRICING_NUMBER";
	const CODE_RED_LINE_NUMBER      = "RED_LINE_NUMBER";
	
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
	
	//add 3 para 20201016
	public static function getAuctionValueNumber() {
		$auctionValueNumberConfig = new Model_ProfitConfig15(Model_ProfitConfig15::CODE_AUCTION_VALUE_NUMBER);
		return $auctionValueNumberConfig->value;
	}
	
	public static function getKaitoPricingNumber() {
		$kaitoPricingNumberConfig = new Model_ProfitConfig15(Model_ProfitConfig15::CODE_KAITO_PRICING_NUMBER);
		return $kaitoPricingNumberConfig->value;
	}
	
	public static function getRedLineNumber() {
		$redLineNumberConfig = new Model_ProfitConfig15(Model_ProfitConfig15::CODE_RED_LINE_NUMBER);
		return $redLineNumberConfig->value;
	}
}