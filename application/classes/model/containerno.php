<?php defined('SYSPATH') or die('No direct script access.');

class Model_ContainerNo extends ORM {
	public $_table_name = 'vw_container_no';

	protected $_table_columns = array(
			"container_no" => array("type" => "string"),
	);
}