<?php
require_once APPPATH.'classes/vendor/PHPExcel.php';

class Model_Warehouse_SearchForm extends Model_PageForm {	
	public $action;
	public $container_no;
	public $order_id;
	public $product_cd;
	public $customer_id;
	public $borrow_qty;
	public $order;
	public $order_date_from;
	public $order_date_to;
	
	public $orderProducts;
	
	public $page_url = 'warehouse/list';
	
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
	
	public static function getOrderOptions() {
		return array(
				'0' => 'order no',
				'1' => 'Part No',
				'2' => 'Cust Code',
		);
	}
	
	private function export() {
		$cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
		$cacheSettings = array('memoryCacheSize' => '8MB');
		PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
	
		$objPHPExcel = new PHPExcel();
	
		$objPHPExcel->getProperties()->setCreator("S3")
		->setLastModifiedBy("S3")
		->setTitle("warehouse_list");
	
		$sheet = $objPHPExcel->setActiveSheetIndex(0);
	
		// Header
		$i = 0;
		$sheet->setCellValueByColumnAndRow($i++, 1, 'item lv status')
		->setCellValueByColumnAndRow($i++, 1, '櫃號(multiple)')
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
		->setCellValueByColumnAndRow($i++, 1, 'color')
		->setCellValueByColumnAndRow($i++, 1, __('label.colour_no'))
		->setCellValueByColumnAndRow($i++, 1, 'pieces(per items)')
		->setCellValueByColumnAndRow($i++, 1, 'material(per items)')
		->setCellValueByColumnAndRow($i++, 1, 'subtotal')
		->setCellValueByColumnAndRow($i++, 1, 'deposit amt')
		->setCellValueByColumnAndRow($i++, 1, 'profit')
		->setCellValueByColumnAndRow($i++, 1, 'tax included稅')
		->setCellValueByColumnAndRow($i++, 1, 'delivery fee (per item)送料')
		->setCellValueByColumnAndRow($i++, 1, '交貨日期')
		->setCellValueByColumnAndRow($i++, 1, '入櫃日期')
		->setCellValueByColumnAndRow($i++, 1, '庫存量')
		->setCellValueByColumnAndRow($i++, 1, '總貨量')
		->setCellValueByColumnAndRow($i++, 1, '厰/ben 數量(大步分貨量')
		->setCellValueByColumnAndRow($i++, 1, '已從工厰/ben給客人的數量(納品書已寄出)')
		->setCellValueByColumnAndRow($i++, 1, '已從工厰/ben運到日本的數量(納品書未寄出)')
		->setCellValueByColumnAndRow($i++, 1, '已從倉庫借出量')
		->setCellValueByColumnAndRow($i++, 1, '已還貨給倉庫量')
		->setCellValueByColumnAndRow($i++, 1, 'pm 設定了的供應商')
		->setCellValueByColumnAndRow($i++, 1, '発送方法');
	
		$rowNo = 1;
		$orderProducts = $this->getData(NULL, NULL);
		foreach($orderProducts as $orderProduct) {
			$i = 0;
			$rowNo++;
	
			$sheet->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->factory_status == 99 ? '完成' : '未完成')
			->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->containerSummary->container_no_list)
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
			->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->productMaster->colour)
			->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->productMaster->colour_no)
			->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->productMaster->pcs)
			->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->productMaster->material)
			->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->getSubTotal())
			->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->order->deposit_amt)
			->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->profit)
			->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->getTaxDescription())
			->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->delivery_fee)
			->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->containerSummary->delivery_date_list)
			->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->containerSummary->container_input_date_list)
			->setCellValueByColumnAndRow($i++, $rowNo, '')
			->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->qty)
			->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->factory_qty)
			->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->factory_delivery_note_qty)
			->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->factory_delivery_qty - $orderProduct->factory_delivery_note_qty)
			->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->warehouse_borrow_qty)
			->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->warehouse_return_qty)
			->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->productMaster->supplier)
			->setCellValueByColumnAndRow($i++, $rowNo, Model_Order::getDisplayDeliveryMethod($orderProduct->delivery_method_description, $orderProduct->delivery_method))
			;
		}
	
		header("Content-type:application/vnd.ms-excel");
		header('Content-Disposition: attachment;filename="warehouse.xls"');
		header('Cache-Control: max-age=0');
	
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
	}
	
// Overrided function
	public function getCount() {
		$query = DB::select(array(DB::expr('COUNT(distinct orderproduct.id)'), 'count'))
				->from(array('order_product', 'orderproduct'))
				->join('order')->on('order.id', '=', 'orderproduct.order_id')
				->join('customer')->on('customer.id', '=', 'order.customer_id');
		
		$query = $this->appendCriteria($query);
		
		$result = $query->execute();
		return $result[0]['count'];
	}
	
	public function getData($limit, $offset) {
		$orm = ORM::factory('orderProduct')
				->with('productMaster')
				->with('order')
				->with('containerSummary')
				->join('customer')->on('customer.id', '=', 'order.customer_id')
				->join('delivery_method')->on('delivery_method.id', '=', 'order.delivery_method_id')
				->join('order_type')->on('order_type.id', '=', 'order.order_type_id')
				->distinct(true)
				->select('customer.cust_code')
				->select(array('delivery_method.description', 'delivery_method_description'))
				->select('order.delivery_method')
				->select(array('order_type.description', 'order_type_description'))
				->order_by('has_container_to_accountant', 'desc')
				->limit($limit)
				->offset($offset);
		
		$orm = $this->appendCriteria($orm);
		
		switch ($this->order) {
			case 0:
				$orm->order_by('order_id', 'desc');
				break;
			case 1:
				$orm->order_by('product_cd')
					->order_by('order_id', 'desc');
				break;
			case 2:
				$orm->order_by('customer.cust_code')
					->order_by('order_id', 'desc');
		}
		
		return $orm->find_all();
	}
	
	public function getCriteria() {
	}
	
	public function appendCriteria($orm) {
		$orm->where('orderproduct.factory_status', '>=', Model_OrderProduct::STATUS_WAREHOUSE)
				->where('factory_qty', '>', 0);
				
		if (!empty($this->container_no)) {
			$orm->join('container')->on('container.order_product_id', '=', 'orderproduct.id')
			->where('container_no', 'like', '%'.$this->container_no.'%');
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
		
		if (!empty($this->borrow_qty)) {
			$orm->where('warehouse_borrow_qty', '>=', $this->borrow_qty);
		}
		
		if (!empty($this->order_date_from)) {
			$orm->where('order.order_date', '>=', $this->order_date_from);
		}
			
		if (!empty($this->order_date_to)) {
			$toDate = date('Y-m-d', strtotime($this->order_date_to.' + 1 days'));
			$orm->where('order.order_date', '<', $toDate);
		}
		
		$orm->where('order.order_type_id', '<>', Model_OrderType::ID_KAITO);
		
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
		
		if (!empty($this->borrow_qty)) {
			$query_string .= '&borrow_qty='.$this->borrow_qty;
		}
		
		if (!empty($this->order_date_from)) {
			$query_string .= '&order_date_from='.$this->order_date_from;
		}
			
		if (!empty($this->order_date_to)) {
			$query_string .= '&order_date_to='.$this->order_date_to;
		}
		
		switch ($this->order) {
			case 0:
				$query_string .= '&order=0';
				break;
			case 1:
				$query_string .= '&order=1';
				break;
			case 2:
				$query_string .= '&order=2';
		}
	
		return $query_string;
	}
}