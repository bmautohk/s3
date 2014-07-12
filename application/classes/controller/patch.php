<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Patch extends Controller {
	public function action_data_patch() {
		
		$summaryList = ORM::factory('containerSummary')->find_all();
		
		foreach ($summaryList as $summary) {
			$summary->container_no_list = '';
			$summary->delivery_date_list = '';
			$summary->container_input_date_list = '';
			$summary->delivery_qty_list = '';
			
			// Update container_no list
			$containers = DB::select('container_no')
			->from('container')
			->where('order_product_id', '=', $summary->order_product_id)
			->where('status', '<>', Model_Container::STATUS_VOID)
			//->order_by('container_no')
			->order_by('container.id')
			->execute();
			
			foreach ($containers as $container) {
				$summary->container_no_list .= '['.$container['container_no'].'],';
			}
			
			// Update delivery_date list
			$containers = DB::select('delivery_date')
			->from('container')
			->where('order_product_id', '=', $summary->order_product_id)
			->where('status', '<>', Model_Container::STATUS_VOID)
			//->order_by('delivery_date')
			->order_by('container.id')
			->execute();
			
			foreach ($containers as $container) {
				$summary->delivery_date_list .= '['.$container['delivery_date'].'],';
			}
			
			// Update container_input_date list
			$containers = DB::select('container_input_date')
			->from('container')
			->where('order_product_id', '=', $summary->order_product_id)
			->where('status', '<>', Model_Container::STATUS_VOID)
			//->order_by('container_input_date')
			->order_by('container.id')
			->execute();
			
			foreach ($containers as $container) {
				$summary->container_input_date_list .= '['.$container['container_input_date'].'],';
			}
			
			// Update delivery QTY list
			$containers = DB::select('delivery_qty')
			->from('container')
			->where('order_product_id', '=', $summary->order_product_id)
			->where('status', '<>', Model_Container::STATUS_VOID)
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
}
