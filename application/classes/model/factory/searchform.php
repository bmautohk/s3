<?php
require_once APPPATH.'classes/vendor/PHPExcel.php';

class Model_Factory_SearchForm extends Model_PageForm {
	public $factory;
	
	public $search_container_no;
	public $search_customer_id;
	public $search_product_cd;
	public $search_order_date_from;
	public $search_order_date_to;
	public $search_translator_last_update_date_from;
	public $search_translator_last_update_date_to;
	public $search_status;
	public $search_order_id;

	public $order_product_ids;
	
	public $orderProducts;
	public $errors;

	public $page_url = 'factory/list/factory';
	
	public function __construct($factory) {
		$this->factory = $factory;
		$this->page_url .= '/'.$factory;
	}
	
	public function populate($post) {
		parent::populate($post);
		
		$this->search_container_no = isset($post['search_container_no']) ? trim($post['search_container_no']) : NULL;
		$this->search_customer_id = isset($post['search_customer_id']) ? $post['search_customer_id'] : NULL;
		$this->search_product_cd = isset($post['search_product_cd']) ? trim($post['search_product_cd']) : NULL;
		$this->search_order_date_from = isset($post['search_order_date_from']) ? $post['search_order_date_from'] : NULL;
		$this->search_order_date_to = isset($post['search_order_date_to']) ? $post['search_order_date_to'] : NULL;
		$this->search_translator_last_update_date_from = isset($post['search_translator_last_update_date_from']) ? $post['search_translator_last_update_date_from'] : NULL;
		$this->search_translator_last_update_date_to = isset($post['search_translator_last_update_date_to']) ? $post['search_translator_last_update_date_to'] : NULL;
		$this->search_status = isset($post['search_status']) ? $post['search_status'] : NULL;
		$this->search_order_id = isset($post['search_order_id']) ? $post['search_order_id'] : NULL;

		$this->order_product_ids = isset($post['order_product_ids']) ? $post['order_product_ids'] : NULL;
	}
	
	public function searchAction() {
		$this->orderProducts = $this->search();
	}

	public function voidOrderAction() {
		$input_order_ids = $this->getOrderIds($this->order_product_ids);

		$isSuccess = false;
		try {
			$this->validateVoidOrder($input_order_ids);
			$this->voidOrder($input_order_ids);
			$isSuccess = true;
		} catch (Exception $e) {
			$this->errors[] = $e->getMessage();
		}

		$this->searchAction();

		return $isSuccess;
	}

	private function getOrderIds($order_product_ids) {
		$order_ids = DB::select('order_id')
					->distinct(true)
					->from('order_product')
					->where('id', 'in', $order_product_ids)
					->execute();
		return $order_ids;
	}

	private function validateVoidOrder($order_ids) {
		foreach ($order_ids as $order_id) {
			$products = ORM::factory('orderproduct')
					->where('order_id', '=', $order_id)
					->find_all();

			foreach ($products as $product) {
				if ($product->factory_status != Model_OrderProduct::STATUS_FACTORY) {
					throw new Exception("You can't void the order. Not all products in order[$order_id] are in 工場.");
				}
			}
		}
	}

	private function voidOrder($order_ids) {
		$db = Database::instance();
		$db->begin();
		
		try {
			foreach ($order_ids as $order_id) {
				$order = ORM::factory('order')->where('id', '=', $order_id)->find();
				$order->status = Model_Order::STATUS_VOID;
				$order->save();

				$products = ORM::factory('orderproduct')
						->where('order_id', '=', $order_id)
						->find_all();

				foreach ($products as $product) {
					$product->factory_status = Model_OrderProduct::STATUS_CANCEL;
					$product->jp_status = Model_OrderProduct::STATUS_CANCEL;
					$product->save();
				}
			}
		} catch (Exception $e) {
			$db->rollback();
			throw $e;
		}
		
		$db->commit();
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
		->setTitle("工場");
	
		$sheet = $objPHPExcel->setActiveSheetIndex(0);
	
		// Header
		$i = 0;
		$sheet->setCellValueByColumnAndRow($i++, 1, 'Order No.')
		->setCellValueByColumnAndRow($i++, 1, '訂單情況(item lv)')
		//->setCellValueByColumnAndRow($i++, 1, '高原第一次批核日期')
		->setCellValueByColumnAndRow($i++, 1, '高原最新的批核日期')
		->setCellValueByColumnAndRow($i++, 1, '客戶編號')
		->setCellValueByColumnAndRow($i++, 1, '貨品編號')
		->setCellValueByColumnAndRow($i++, 1, '進倉數量')
		->setCellValueByColumnAndRow($i++, 1, '已出貨數量')
		->setCellValueByColumnAndRow($i++, 1, 'kaito staff 分貨qty')
		->setCellValueByColumnAndRow($i++, 1, '工場佘數')
		->setCellValueByColumnAndRow($i++, 1, 'cost海渡價')
		->setCellValueByColumnAndRow($i++, 1, '貨品名稱')
		->setCellValueByColumnAndRow($i++, 1, 'Brand name(pm.車種)')
		->setCellValueByColumnAndRow($i++, 1, 'Car Name(車型)')
		->setCellValueByColumnAndRow($i++, 1, 'Model Name(型號)')
		->setCellValueByColumnAndRow($i++, 1, '商品说明')
		->setCellValueByColumnAndRow($i++, 1, '年分')
		//->setCellValueByColumnAndRow($i++, 1, 'color')
		->setCellValueByColumnAndRow($i++, 1, __('label.colour_no'))
		->setCellValueByColumnAndRow($i++, 1, '件數')
		->setCellValueByColumnAndRow($i++, 1, '材質')
		->setCellValueByColumnAndRow($i++, 1, '高元remark')
		->setCellValueByColumnAndRow($i++, 1, '櫃號(multi)');
	
		$rowNo = 1;
		$orderProducts = $this->getData(NULL, NULL);
		foreach($orderProducts as $orderProduct) {
			$i = 0;
			$rowNo++;
				
			$sheet->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->order_id)
			->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->factory_status == 99 ? '完成' : '未完成')
		//	->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->translator_first_update_date)
			->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->translator_last_update_date)
			->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->cust_code)
			->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->product_cd)
			->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->factory_entry_qty)
			->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->factory_delivery_qty)
			->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->factory_qty)
			->setCellValueByColumnAndRow($i++, $rowNo,$orderProduct->factory_qty - $orderProduct->factory_delivery_qty)
			->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->kaito)
			->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->product_desc)
			->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->made)
			->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->model)
			->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->model_no)
			->setCellValueExplicitByColumnAndRow($i++, $rowNo, $orderProduct->accessory_remark)
			->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->year)
			//->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->colour)
			->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->colour_no)
			->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->pcs)
			->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->material)
			->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->translator_remark)
			->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->containerSummary->container_no_list)
			;
		}
	
		header("Content-type:application/vnd.ms-excel");
		header('Content-Disposition: attachment;filename="factory.xls"');
		header('Cache-Control: max-age=0');
	
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
	}
	
// Overrided function
	public function getCount() {
		$query = DB::select(array(DB::expr('COUNT(distinct orderproduct.id)'), 'count'))
		->from(array('order_product', 'orderproduct'))
		->join('order')->on('order.id', '=', 'orderproduct.order_id')
		->join('customer')->on('customer.id', '=', 'order.customer_id')
		->where('factory_status', '>=', Model_OrderProduct::STATUS_FACTORY)
		->where('factory', '=', Model_OrderProduct::getFactoryCode($this->factory));
	
		$query = $this->appendCriteria($query);
	
		$result = $query->execute();
		return $result[0]['count'];
	}
	
	public function getData($limit, $offset) {
		$orm = ORM::factory('orderProduct')
				->with('order')
				->with('containerSummary')
				->with('productMaster')
				//->join('product_master')->on('product_master.no_jp' ,'=', 'orderproduct.product_cd')
				->join('customer')->on('customer.id', '=', 'order.customer_id')
				->join('order_type')->on('order_type.id', '=', 'order.order_type_id')
				->where('factory_status', '>=', Model_OrderProduct::STATUS_FACTORY)
				->where('factory', '=', Model_OrderProduct::getFactoryCode($this->factory))
				->select('customer.cust_code')
				->select('order.order_type_id')
				->select('productMaster.business_price')
				->select('productMaster.product_desc')
				->select('productMaster.made')
				->select('productMaster.model')
				->select('productMaster.model_no')
				->select('productMaster.accessory_remark')
				->select('productMaster.year')
				->select('productMaster.colour')
				->select('productMaster.colour_no')
				->select('productMaster.pcs')
				->select('productMaster.material')
				->select(array('order_type.description', 'order_type_description'))
				->distinct(true)
				->order_by('order_id', 'desc')
				->order_by('product_cd')
				->limit($limit)
				->offset($offset);
		
		$orm = $this->appendCriteria($orm);
				
		return $orm->find_all();
	}
	
	public function getCriteria() {
		if (!empty($this->search_order_id)) {
			$orm->where('order.id', '=', $this->search_order_id);
		}
		
		return $orm;
	}
	
	public function appendCriteria($orm) {
		if (!empty($this->search_order_id)) {
			$orm->where('order.id', '=', $this->search_order_id);
		}
		
		if ($this->search_container_no != '') {
			// Search by container no
			$orm->join('container')->on('container.order_product_id', '=', 'orderproduct.id')
				->where('container_no', 'like', '%'.$this->search_container_no.'%');
		}
		
		if ($this->search_customer_id != 0) {
			// Search by customer
			$orm->where('order.customer_id', '=', $this->search_customer_id);
		}
		
		if ($this->search_product_cd != '') {
			// Search by product code
			$orm->where('product_cd', '=', $this->search_product_cd);
		}
		
		
		// Search by order date
		if ($this->search_order_date_from != '') {
			$orm->where('order.order_date', '>=', $this->search_order_date_from);
		}
		
		if ($this->search_order_date_to != '') {
			$toDate = date('Y-m-d', strtotime($this->search_order_date_to.' + 1 days'));
			$orm->where('order.order_date', '<', $toDate);
		}
		
		// Search by translater last updated date
		if ($this->search_translator_last_update_date_from != '') {
			$orm->where('orderproduct.translator_last_update_date', '>=', $this->search_translator_last_update_date_from);
		}
		
		if ($this->search_translator_last_update_date_to != '') {
			$toDate = date('Y-m-d', strtotime($this->search_translator_last_update_date_to.' + 1 days'));
			$orm->where('orderproduct.translator_last_update_date', '<', $toDate);
		}
		
		if ($this->search_status != '') {
			// Search by status
			if ($this->search_status == 'A') {
				$orm->where('factory_status', '<>', Model_OrderProduct::STATUS_COMPLETE);
			} else if ($this->search_status == 'C') {
				$orm->where('factory_status', '=', Model_OrderProduct::STATUS_COMPLETE);
			}
		}
		
		return $orm;
	}
	
	public function getQueryString() {
		$query_string = '';
		
		$query_string .= '&search_container_no='.$this->search_container_no;
	
		$query_string .= '&search_customer_id='.$this->search_customer_id;
	
		$query_string .= '&search_by_product_cd='.$this->search_product_cd;
		
		$query_string .= '&search_order_date_from='.$this->search_order_date_from;
		$query_string .= '&search_order_date_to='.$this->search_order_date_to;
		
		$query_string .= '&search_translator_last_update_date_from='.$this->search_translator_last_update_date_from;
		$query_string .= '&search_translator_last_update_date_to='.$this->search_translator_last_update_date_to;
		
		$query_string .= '&search_status='.$this->search_status;
		if (!empty($this->search_order_id)) {
			$query_string .= '&search_order_id='.$this->search_order_id;
		}
		return $query_string;
	}
}