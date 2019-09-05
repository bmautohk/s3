<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Product extends Controller {

	public function action_search() {
		//$keyword = $this->request->param('keyword');
		$keyword = $_GET['term'];
		
		$products = ORM::factory('pmProductMaster')
					->where('no_jp', 'like', '%'.$keyword.'%')
					->limit(10)
					->order_by('no_jp')
					->find_all();
		
		$result = array();
		foreach ($products as $product) {
			$item['label'] = $product->no_jp;
			$item['value'] = $product->no_jp;
			$item['business_price'] = $product->business_price;
			$item['other'] = $product->other;
			$item['product_desc'] = $product->product_desc;
			$item['made'] = $product->made;
			$item['model'] = $product->model;
			$item['model_no'] = $product->model_no;
			$item['colour'] = $product->colour;
			$item['colour_no'] = $product->colour_no;
			$item['pcs'] = $product->pcs;
			$item['material'] = $product->material;
			$item['supplier'] = $product->supplier;
			$item['accessory_remark'] = $product->accessory_remark;
			$item['year'] = $product->year;
			$result[] = $item;
		}

		echo json_encode($result);
	}
	
	public function action_searchTempProduct() {
		$keyword = $_GET['term'];
	
		/* $products = ORM::factory('tempProductMaster')
					->join('order_product')->on('order_product.id', '=', 'tempproductmaster.order_product_id')
					->join('order')->on('order.id', '=', 'order_product.order_id')
					->where('tempproductmaster.no_jp', 'like', '%'.$keyword.'%')
					->where('tempproductmaster.status', '=', 'A')
					->where('order_type_id', '=', Model_OrderType::ID_TEMP)
					->limit(10)
					->order_by('product_cd')
					->distinct(true)
					->find_all(); */
		
		$queryResult = DB::select('no_jp',
							array(DB::expr('MAX(temp_product_master.id)'), 'temp_product_master_id'))
				->from('temp_product_master')
				->join('order_product')->on('order_product.id', '=', 'temp_product_master.order_product_id')
				->join('order')->on('order.id', '=', 'order_product.order_id')
				->where('temp_product_master.no_jp', 'like', '%'.$keyword.'%')
				->where('temp_product_master.status', '=', 'A')
				->where('order_type_id', '=', Model_OrderType::ID_TEMP)
				->group_by('no_jp')
				->order_by('no_jp')
				->limit(10)
				->distinct(true)
				->execute();
		
		$ids = array();
		foreach($queryResult as $obj) {
			$ids[] = $obj['temp_product_master_id'];
		}

		$result = array();
		if (!empty($ids)) {
			$products = ORM::factory('tempProductMaster')
						->where('id', 'in', $ids)
						->order_by('no_jp')
						->find_all();
			
			foreach ($products as $product) {
				$item['label'] = $product->no_jp;
				$item['value'] = $product->no_jp;
				$item['business_price'] = $product->business_price;
				$item['product_desc'] = $product->product_desc;
				$item['made'] = $product->made;
				$item['model'] = $product->model;
				$item['model_no'] = $product->model_no;
				$item['colour'] = $product->colour;
				$item['colour_no'] = $product->colour_no;
				$item['pcs'] = $product->pcs;
				$item['material'] = $product->material;
				$item['supplier'] = $product->supplier;
				$item['accessory_remark'] = $product->accessory_remark;
				$item['year'] = $product->year;
				$result[] = $item;
			}
		}
	
		echo json_encode($result);
	}
	
	public function action_search_by_cd() {
		$product_cd = $_GET['term'];
		
		$product = ORM::factory('pmProductMaster')
					->where('no_jp', '=', $product_cd)
					->find();
		
		if ($product->loaded()) {
			$item['label'] = $product->no_jp;
			$item['value'] = $product->no_jp;
			$item['business_price'] = $product->business_price;
			$item['other'] = $product->other;
			$item['product_desc'] = $product->product_desc;
			$item['made'] = $product->made;
			$item['model'] = $product->model;
			$item['model_no'] = $product->model_no;
			$item['colour'] = $product->colour;
			$item['colour_no'] = $product->colour_no;
			$item['pcs'] = $product->pcs;
			$item['material'] = $product->material;
			$item['supplier'] = $product->supplier;
			$item['accessory_remark'] = $product->accessory_remark;
			$item['year'] = $product->year;
			
			echo json_encode($item);
		} else {
			return null;
		}
	}

	public function action_search_temp_by_cd() {
		$product_cd = $_GET['term'];

		$queryResult = DB::select('no_jp',
							array(DB::expr('MAX(temp_product_master.id)'), 'temp_product_master_id'))
				->from('temp_product_master')
				->join('order_product')->on('order_product.id', '=', 'temp_product_master.order_product_id')
				->join('order')->on('order.id', '=', 'order_product.order_id')
				->where('temp_product_master.no_jp', '=', $product_cd)
				->where('temp_product_master.status', '=', 'A')
				->where('order_type_id', '=', Model_OrderType::ID_TEMP)
				->distinct(true)
				->execute();
		
		$id = 0;
		foreach($queryResult as $obj) {
			$id = $obj['temp_product_master_id'];
			break;
		}

		$product = ORM::factory('tempProductMaster')
					->where('id', '=', $id)
					->find();
		
		if ($product->loaded()) {
			$item['label'] = $product->no_jp;
			$item['value'] = $product->no_jp;
			$item['business_price'] = $product->business_price;
			$item['product_desc'] = $product->product_desc;
			$item['made'] = $product->made;
			$item['model'] = $product->model;
			$item['model_no'] = $product->model_no;
			$item['colour'] = $product->colour;
			$item['colour_no'] = $product->colour_no;
			$item['pcs'] = $product->pcs;
			$item['material'] = $product->material;
			$item['supplier'] = $product->supplier;
			$item['accessory_remark'] = $product->accessory_remark;
			$item['year'] = $product->year;
			
			echo json_encode($item);
		} else {
			return null;
		}
	}
}