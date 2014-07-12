<?php defined('SYSPATH') or die('No direct script access.');

class Model_S1_BenSale extends ORM {
	protected $_table_name = 'ben_sale';
	protected $_db_group = 's1';
	
	protected $_primary_key = 'sale_index';
	
	protected $_table_columns = array(
			"sale_index" => array("type" => "int"),
			"sale_ref" => array("type" => "string"),
			"sale_date" => array("type" => "date"),
			"sale_group" => array("type" => "string"),
			"sale_discount" => array("type" => "double"),
			"sale_dat" => array("type" => "date"),
			"sale_chk_ref" => array("type" => "string"),
			"sale_tax" => array("type" => "double"),
			"sale_ship_fee" => array("type" => "double"),
			"sale_email" => array("type" => "string"),
			"sale_name" => array("type" => "string"),
			"sale_yahoo_id" => array("type" => "string"),
			"sale_email2" => array("type" => "string"),
			"sts" => array("type" => "string"),
			"s3_delivery_note_no" => array("type" => "string"),
	);
	
	const SALE_CHK_REF_YAHOO = 0;
	const SALE_CHK_REF_AUTO = 1;
	
	public function rules() {
		return array(
		);
	}
}