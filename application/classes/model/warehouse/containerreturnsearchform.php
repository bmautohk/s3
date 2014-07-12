<?php
require_once APPPATH.'classes/vendor/PHPExcel.php';

class Model_Warehouse_ContainerReturnSearchForm extends Model_PageForm {	
	public $action;
	public $container_no;
	public $order_id;
	public $product_cd;
	public $customer_id;
	public $order_date_from;
	public $order_date_to;
	
	public $orderProducts;
	
	public $page_url = 'warehouse/container_return_list';
	
	public function populate($post) {
		parent::populate($post);
		
		$this->action = isset($post['action']) ? $post['action'] : NULL;
		$this->container_no = isset($post['container_no']) ? $post['container_no'] : NULL;
		$this->order_id = isset($post['order_id']) ? $post['order_id'] : NULL;
		$this->product_cd = isset($post['product_cd']) ? $post['product_cd'] : NULL;
		$this->customer_id = isset($post['customer_id']) ? $post['customer_id'] : NULL;
		$this->borrow_qty = isset($post['borrow_qty']) ? $post['borrow_qty'] : NULL;
		$this->order_date_from = isset($post['order_date_from']) ? $post['order_date_from'] : NULL;
		$this->order_date_to = isset($post['order_date_to']) ? $post['order_date_to'] : NULL;
		$this->order = isset($post['order']) ? $post['order'] : NULL;
	}
	
	public function searchAction() {
		$this->orderProducts = $this->search();
	}
	
	public function exportAction() {
		$this->export();
	}
	
	private function export() {
		$cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
		$cacheSettings = array('memoryCacheSize' => '8MB');
		PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
	
		$objPHPExcel = new PHPExcel();
	
		$objPHPExcel->getProperties()->setCreator("S3")
		->setLastModifiedBy("S3")
		->setTitle("return");
	
		$sheet = $objPHPExcel->setActiveSheetIndex(0);
	
		// Header
		$i = 0;
		$sheet->setCellValueByColumnAndRow($i++, 1, '櫃號')
			->setCellValueByColumnAndRow($i++, 1, 'Order No.')
			->setCellValueByColumnAndRow($i++, 1, 'Cust Code')
			->setCellValueByColumnAndRow($i++, 1, 'Part No.:(品番)')
			->setCellValueByColumnAndRow($i++, 1, 'qty')
			->setCellValueByColumnAndRow($i++, 1, 'marketprice')
			->setCellValueByColumnAndRow($i++, 1, '參考價格')
			->setCellValueByColumnAndRow($i++, 1, 'cost海渡價')
			->setCellValueByColumnAndRow($i++, 1, 'product name(per items)')
			->setCellValueByColumnAndRow($i++, 1, 'Brand name(pm.車種)')
			->setCellValueByColumnAndRow($i++, 1, 'Car Name(車型)')
			->setCellValueByColumnAndRow($i++, 1, 'Model Name(型號)')
			->setCellValueByColumnAndRow($i++, 1, __('label.accessory_remark'))
			->setCellValueByColumnAndRow($i++, 1, __('label.year'))
			->setCellValueByColumnAndRow($i++, 1, 'color')
			->setCellValueByColumnAndRow($i++, 1, __('label.colour_no'))
			->setCellValueByColumnAndRow($i++, 1, 'pieces(per items)')
			->setCellValueByColumnAndRow($i++, 1, 'material(per items)')
			->setCellValueByColumnAndRow($i++, 1, '從工厰/ben的數量')
			->setCellValueByColumnAndRow($i++, 1, '返品數量')
			->setCellValueByColumnAndRow($i++, 1, '返品日期')
			->setCellValueByColumnAndRow($i++, 1, '倉管員Remark')
		;
	
		$rowNo = 1;
		$orderProducts = $this->getData(NULL, NULL);
		foreach($orderProducts as $orderProduct) {
			$i = 0;
			$rowNo++;
	
			$sheet->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->container_no)
				->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->order_id)
				->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->cust_code)
				->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->product_cd)
				->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->qty)
				->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->market_price)
				->setCellValueByColumnAndRow($i++, $rowNo, '')
				->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->kaito)
				->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->productMaster->product_desc)
				->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->productMaster->made)
				->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->productMaster->model)
				->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->productMaster->model_no)
				->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->productMaster->accessory_remark)
				->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->productMaster->year)
				->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->productMaster->colour)
				->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->productMaster->colour_no)
				->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->productMaster->pcs)
				->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->productMaster->material)
				->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->orig_delivery_qty)
				->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->return_qty)
				->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->return_date)
				->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->return_remark)
			;
		}
	
		header("Content-type:application/vnd.ms-excel");
		header('Content-Disposition: attachment;filename="return.xls"');
		header('Cache-Control: max-age=0');
	
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
	}
	
// Overrided function
	public function getData($limit, $offset) {
		return $this->getCriteria()
					->select('cust_code')
					->select('container.container_no')
					->select('container.orig_delivery_qty')
					->select(array('container_return.qty', 'return_qty'))
					->select(array('container_return.create_date', 'return_date'))
					->select(array('container_return.remark', 'return_remark'))
					->order_by('container_return.create_date', 'desc')
					->order_by('product_cd')
					->limit($limit)
					->offset($offset)
					->find_all();
	}
	
	public function getCriteria() {
		$orm = ORM::factory('orderProduct')
		->with('productMaster')
		->with('order')
		->join('container')->on('container.order_product_id', '=', 'orderproduct.id')
		->join('container_return')->on('container_return.container_id', '=', 'container.id')
		->join('customer')->on('customer.id', '=', 'order.customer_id')
		->select('customer.cust_code');
		
		$orm->where('orderproduct.factory_status', '>=', Model_OrderProduct::STATUS_WAREHOUSE)
			->where('container.source', '=', Model_Container::SOURCE_FACTORY);
		
		if (!empty($this->container_no)) {
			$orm->where('container_no', 'like', '%'.$this->container_no.'%');
		}
		
		if (!empty($this->order_id)) {
			$orm->where('order_id', '=', $this->order_id);
		}
		
		if (!empty($this->product_cd)) {
			$orm->where('product_cd', '=', $this->product_cd);
		}
		
		if (!empty($this->customer_id) && $this->customer_id != 0) {
			$orm->where('customer_id', '=', $this->customer_id);
		}
		
		if (!empty($this->order_date_from)) {
			$orm->where('order.order_date', '>=', $this->order_date_from);
		}
			
		if (!empty($this->order_date_to)) {
			$toDate = date('Y-m-d', strtotime($this->order_date_to.' + 1 days'));
			$orm->where('order.order_date', '<', $toDate);
		}
		
		return $orm;
	}
	
	public function getQueryString() {
		$query_string = '';
	
		if (!empty($this->container_no)) {
			$query_string .= '&container_no='.$this->container_no;
		}
		
		if (!empty($this->order_id)) {
			$query_string .= '&order_id='.$this->order_id;
		}
		
		if (!empty($this->product_cd)) {
			$query_string .= '&product_cd='.$this->product_cd;
		}
		
		if (!empty($this->customer_id)) {
			$query_string .= '&customer_id='.$this->customer_id;
		}
		
		if (!empty($this->order_date_from)) {
			$query_string .= '&order_date_from='.$this->order_date_from;
		}
			
		if (!empty($this->order_date_to)) {
			$query_string .= '&order_date_to='.$this->order_date_to;
		}
		
		return $query_string;
	}
}