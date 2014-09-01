<?php defined('SYSPATH') or die('No direct script access.');

class Model_ContainerSummary extends ORM {
	public $_table_name = 'container_summary';

	protected $_table_columns = array(
			"id" => array("type" => "int"),
			"order_product_id" => array("type" => "int"),
			"container_no_list" => array("type" => "string"),
			"delivery_date_list" => array("type" => "string"),
			"container_input_date_list" => array("type" => "string"),
			"delivery_qty_list" => array("type" => "string"),
	);

	public static function createSummary($order_product_id) {
		$summary = ORM::factory('containerSummary')
					->where('order_product_id', '=', $order_product_id)
					->find();
		
		if (!$summary->loaded()) {
			$summary = new Model_ContainerSummary();
			$summary->order_product_id = $order_product_id;
		}
			
		$summary->container_no_list = '';
		$summary->delivery_date_list = '';
		$summary->container_input_date_list = '';
		$summary->delivery_qty_list = '';
			
		// Update container_no list
		$containers = DB::select('container_no')
						->from('container')
						->where('order_product_id', '=', $order_product_id)
						->where('source', '=', Model_Container::SOURCE_FACTORY)
						//->order_by('container_no')
						->order_by('container.id')
						->execute();
	
		foreach ($containers as $container) {
			$summary->container_no_list .= '['.$container['container_no'].'],';
		}
			
		// Update delivery_date list
		$containers = DB::select('delivery_date')
						->from('container')
						->where('order_product_id', '=', $order_product_id)
						->where('source', '=', Model_Container::SOURCE_FACTORY)
						//->order_by('delivery_date')
						->order_by('container.id')
						->execute();
			
		foreach ($containers as $container) {
			$summary->delivery_date_list .= '['.$container['delivery_date'].'],';
		}
			
		// Update container_input_date list
		$containers = DB::select('container_input_date')
						->from('container')
						->where('order_product_id', '=', $order_product_id)
						->where('source', '=', Model_Container::SOURCE_FACTORY)
						//->order_by('container_input_date')
						->order_by('container.id')
						->execute();
			
		foreach ($containers as $container) {
			$summary->container_input_date_list .= '['.$container['container_input_date'].'],';
		}
	
		// Update delivery QTY list
		$containers = DB::select('delivery_qty')
						->from('container')
						->where('order_product_id', '=', $order_product_id)
						->where('source', '=', Model_Container::SOURCE_FACTORY)
						//->order_by('container_input_date')
						->order_by('container.id')
						->execute();
			
		foreach ($containers as $container) {
			$summary->delivery_qty_list .= '['.$container['delivery_qty'].'],';
		}
			
		$summary->container_no_list = substr($summary->container_no_list, 0, strlen($summary->container_no_list) - 1);
		$summary->delivery_date_list = substr($summary->delivery_date_list, 0, strlen($summary->delivery_date_list) - 1);
		$summary->container_input_date_list = substr($summary->container_input_date_list, 0, strlen($summary->container_input_date_list) - 1);
		$summary->delivery_qty_list = substr($summary->delivery_qty_list, 0, strlen($summary->delivery_qty_list) - 1);
		$summary->save();
	}
}