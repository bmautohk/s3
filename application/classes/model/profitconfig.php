<?php defined('SYSPATH') or die('No direct script access.');

class Model_ProfitConfig extends ORM {
	public $_table_name = 'profit_config';
	
	protected $_primary_key = 'code';
	
	protected $_table_columns = array(
			"code" => array("type" => "string"),
			"value" => array("type" => "string"),
	);
}