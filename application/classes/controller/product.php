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
	
	public function action_search_by_cd() {
		$product_cd = $_GET['term'];
		
		$product = ORM::factory('pmProductMaster')
					->where('no_jp', '=', $product_cd)
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