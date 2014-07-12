<?php defined('SYSPATH') or die('No direct script access.');

class Model_ContainerReturn extends ORM {
	public $_table_name = 'container_return';

	protected $_table_columns = array(
			"id" => array("type" => "int"),
			"container_id" => array("type" => "int"),
			"qty" => array("type" => "int"),
			"remark" => array("type" => "string"),
			"created_by" => array("type" => "string"),
			"create_date" => array("type" => "timestamp"),
	);

	public function rules() {
		return array(
				'qty' => array(
						array('not_empty'),
						array('digit'),
						//array('CustomValidation::positive')
				)
		);
	}
}