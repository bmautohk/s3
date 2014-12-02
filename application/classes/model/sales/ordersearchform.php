<?php
require_once APPPATH.'classes/vendor/PHPExcel.php';

class Model_Sales_OrderSearchForm extends Model_PageForm {
	
	public $action;

	public $customer_id;
	public $search_order_id;
	public $product_cd;
	public $container_no;
	public $order_type_id;
	public $username;
	public $status;
	public $order_date_from;
	public $order_date_to;
	public $market_price;
	
	public $orderProducts;
	public $taxRate;
	
	public $page_url = 'sales/order_search';
	
	public function populate($post) {
		parent::populate($post);
		
		$this->action = isset($post['action']) ? $post['action'] : NULL;
		$this->customer_id = isset($post['customer_id']) ? $post['customer_id'] : NULL;
		$this->search_order_id = isset($post['search_order_id']) ? $post['search_order_id'] : NULL;
		$this->product_cd = isset($post['product_cd']) ? $post['product_cd'] : NULL;
		$this->container_no = isset($post['container_no']) ? $post['container_no'] : NULL;
		$this->order_type_id = isset($post['order_type_id']) ? $post['order_type_id'] : NULL;
		$this->username = isset($post['username']) ? $post['username'] : NULL;
		$this->status = isset($post['status']) ? $post['status'] : NULL;
		$this->order_date_from = isset($post['order_date_from']) ? $post['order_date_from'] : NULL;
		$this->order_date_to = isset($post['order_date_to']) ? $post['order_date_to'] : NULL;

		$this->order_product_id = isset($post['order_product_id']) ? $post['order_product_id'] : NULL;
		$this->market_price = isset($post['market_price']) ? $post['market_price'] : NULL;
		foreach (get_class_vars(get_class($this)) as $columnName=>$value) {
			if (isset($post[$columnName])) {
				$this->$columnName = $post[$columnName];
			}
		}
	}
	
	public function defaultSearchAction() {
		$this->customer_id = 0;
		$this->status = 'A';
		
		$this->searchAction();
	}
	
	public function searchAction() {
		$taxRateConfig = new Model_ProfitConfig15(Model_ProfitConfig15::CODE_TAX_RATE);
		$this->taxRate = $taxRateConfig->value / 100.0;
		
		$this->orderProducts = $this->search();
	}
	
	public function cancelAction() {
		$result = $this->cancel();
		
		$this->searchAction();
		
		return $result;
	}
	
	public function exportAction() {
		$this->export();
	}
	
	private function cancel() {
		$this->errors = array();
		
		$db = Database::instance();
		$db->begin();
		try {
			$orderProduct = new Model_OrderProduct($this->order_product_id);
			
			// Check whether the order is created by the logged user
			$user = Auth::instance()->get_user();
			if ($user->isSales()) {
				$order = new Model_Order($orderProduct->order_id);
				
				if ($order->created_by != $user->username) {
					return false;
				}
			}
			
			// Can't cancel if the product has been passed to kaitoStaff
			if ($orderProduct->factory_status >= Model_OrderProduct::STATUS_KAITOSTAFF || $orderProduct->jp_status >= Model_OrderProduct::STATUS_KAITOSTAFF) {
				return false;
			}
			
			$orderProduct->factory_status = Model_OrderProduct::STATUS_CANCEL;
			$orderProduct->jp_status = Model_OrderProduct::STATUS_CANCEL;
			$orderProduct->save();
			
			// If all product lines are canceled, the order status is changed to void.
			$result = DB::select(array(DB::expr('COUNT(id)'), 'count'))
					->from('order_product')
					->where('order_id', '=', $orderProduct->order_id)
					->and_where_open()
					->or_where('factory_status', '>=', Model_OrderProduct::STATUS_SALES)
					->or_where('jp_status', '>=', Model_OrderProduct::STATUS_SALES)
					->and_where_close()
					->execute();
			
			if ($result[0]['count'] == 0) {
				$order = new Model_Order($orderProduct->order_id);
				$order->last_updated_by = $user->username;
				$order->status = Model_Order::STATUS_VOID;
				$order->save();
			}
			
			/* if ($order->order_type_id == Model_OrderType::ID_TEMP) {
				// Temp Order
				$tempProductMasters = ORM::factory('tempProductMaster')
								->join('order_product')->on('order_product.id', '=', 'tempproductmaster.order_product_id')
								->where('order_product.order_id', '=', $this->order_id)
								->find_all();
				
				foreach ($tempProductMasters as $tempProductMaster) {
					$tempProductMaster->status = Model_TempProductMaster::STATUS_INACTIVE;
					$tempProductMaster->save();
				}
			} 
			
			$order->last_updated_by = $user->username;
			$order->status = Model_Order::STATUS_VOID;
			$order->save(); */
		} catch (Exception $e) {
			$db->rollback();
			$this->errors[] = $e->getMessage();
			return false;
		}
		
		$db->commit();
		
		return true;;
	}
	
	private function export() {
		$cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
		$cacheSettings = array('memoryCacheSize' => '8MB');
		PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
		
		$objPHPExcel = new PHPExcel();
		
		$objPHPExcel->getProperties()->setCreator("S3")
					->setLastModifiedBy("S3")
					->setTitle("Order");
		
		$sheet = $objPHPExcel->setActiveSheetIndex(0);
		
		// Header
		$i = 0;
		$sheet->setCellValueByColumnAndRow($i++, 1, __('label.order_no'))
		->setCellValueByColumnAndRow($i++, 1, __('label.order_date'))
		->setCellValueByColumnAndRow($i++, 1, __('label.kaito_remark'))
		->setCellValueByColumnAndRow($i++, 1, __('label.cust_code'))
		->setCellValueByColumnAndRow($i++, 1, __('label.product_cd'))
		->setCellValueByColumnAndRow($i++, 1, __('label.qty'))
		->setCellValueByColumnAndRow($i++, 1, __('label.market_price'))
		->setCellValueByColumnAndRow($i++, 1, __('label.reference_price'))
		->setCellValueByColumnAndRow($i++, 1, __('label.business_price'))
		->setCellValueByColumnAndRow($i++, 1, __('label.product_desc'))
		->setCellValueByColumnAndRow($i++, 1, __('label.made'))
		->setCellValueByColumnAndRow($i++, 1, __('label.model'))
		->setCellValueByColumnAndRow($i++, 1, __('label.model_no'))
		->setCellValueByColumnAndRow($i++, 1, __('label.colour'))
		->setCellValueByColumnAndRow($i++, 1, __('label.colour_no'))
		->setCellValueByColumnAndRow($i++, 1, __('label.pcs').__('label.sales.per_item'))
		->setCellValueByColumnAndRow($i++, 1, __('label.material').__('label.sales.per_item'))
		->setCellValueByColumnAndRow($i++, 1, '商品说明')
		->setCellValueByColumnAndRow($i++, 1, '年式')
		->setCellValueByColumnAndRow($i++, 1, __('label.subtotal'))
		->setCellValueByColumnAndRow($i++, 1, __('label.payment'))
		->setCellValueByColumnAndRow($i++, 1, __('label.profit'))
		->setCellValueByColumnAndRow($i++, 1,  __('label.is_tax'))
		->setCellValueByColumnAndRow($i++, 1, __('label.delivery_fee'))
		->setCellValueByColumnAndRow($i++, 1, __('label.container_no'))
		->setCellValueByColumnAndRow($i++, 1, __('label.factory_delivery_qty').__('label.sales.per_item'))
		->setCellValueByColumnAndRow($i++, 1, __('label.delivery_date').__('label.sales.per_item'))
		->setCellValueByColumnAndRow($i++, 1, __('label.container_input_date').__('label.sales.per_item'))
		->setCellValueByColumnAndRow($i++, 1, __('label.sales_remark'))
		->setCellValueByColumnAndRow($i++, 1, __('label.jp_auditor_remark'))
		->setCellValueByColumnAndRow($i++, 1, __('label.factory_auditor_remark'))
		->setCellValueByColumnAndRow($i++, 1, __('label.order_type'))
		->setCellValueByColumnAndRow($i++, 1, __('label.order_status'))
		;
		
		$orderProducts = $this->getData(NULL, NULL);
		
		$taxRate = Model_ProfitConfig15::getTaxRate();
		
		$rowNo = 1;
		foreach($orderProducts as $orderProduct) {
			$i = 0;
			$rowNo++;
			
			$sheet->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->order_id)
				->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->order->order_date)
				->setCellValueExplicitByColumnAndRow($i++, $rowNo, $orderProduct->kaito_remark)
				->setCellValueExplicitByColumnAndRow($i++, $rowNo, $orderProduct->cust_code)
				->setCellValueExplicitByColumnAndRow($i++, $rowNo, $orderProduct->product_cd)
				->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->qty)
				->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->market_price)
				->setCellValueByColumnAndRow($i++, $rowNo, '')
				->setCellValueExplicitByColumnAndRow($i++, $rowNo, $orderProduct->productMaster->business_price)
				->setCellValueExplicitByColumnAndRow($i++, $rowNo, $orderProduct->productMaster->product_desc)
				->setCellValueExplicitByColumnAndRow($i++, $rowNo, $orderProduct->productMaster->made)
				->setCellValueExplicitByColumnAndRow($i++, $rowNo, $orderProduct->productMaster->model)
				->setCellValueExplicitByColumnAndRow($i++, $rowNo, $orderProduct->productMaster->model_no)
				->setCellValueExplicitByColumnAndRow($i++, $rowNo, $orderProduct->productMaster->colour)
				->setCellValueExplicitByColumnAndRow($i++, $rowNo, $orderProduct->productMaster->colour_no)
				->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->productMaster->pcs)
				->setCellValueExplicitByColumnAndRow($i++, $rowNo, $orderProduct->productMaster->material)
				->setCellValueExplicitByColumnAndRow($i++, $rowNo, $orderProduct->productMaster->accessory_remark)
				->setCellValueExplicitByColumnAndRow($i++, $rowNo, $orderProduct->productMaster->year)
				->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->getSubTotalWithDeliveryFeeTax($orderProduct->order->rmb_to_jpy_rate, $taxRate))
				->setCellValueByColumnAndRow($i++, $rowNo, '')
				->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->profit)
				->setCellValueExplicitByColumnAndRow($i++, $rowNo, $orderProduct->getTaxDescription())
				->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->delivery_fee)
				->setCellValueExplicitByColumnAndRow($i++, $rowNo, $orderProduct->containerSummary->container_no_list)
				->setCellValueExplicitByColumnAndRow($i++, $rowNo, $orderProduct->containerSummary->delivery_qty_list)
				->setCellValueExplicitByColumnAndRow($i++, $rowNo, $orderProduct->containerSummary->delivery_date_list)
				->setCellValueExplicitByColumnAndRow($i++, $rowNo, $orderProduct->containerSummary->container_input_date_list)
				->setCellValueExplicitByColumnAndRow($i++, $rowNo, $orderProduct->order->remark)
				->setCellValueExplicitByColumnAndRow($i++, $rowNo, $orderProduct->jp_auditor_remark)
				->setCellValueExplicitByColumnAndRow($i++, $rowNo, $orderProduct->factory_auditor_remark)
				->setCellValueExplicitByColumnAndRow($i++, $rowNo, $orderProduct->order_type_description)
				->setCellValueExplicitByColumnAndRow($i++, $rowNo, $orderProduct->order->status ==  'C' ? '完成' : '未完成');
		}

		header("Content-Type:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
		//header("Content-type:application/vnd.ms-excel");
		header('Content-Disposition: attachment;filename="order.xlsx"');
		header('Cache-Control: max-age=0');
		
 		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
		$objWriter->save('php://output');
		
	}
	
// Overrided function
	public function getData($limit, $offset) {
		return $this->getCriteria()->select('customer.cust_code')
					->select(array('order_type.description', 'order_type_description'))
					->select('order.rmb_to_jpy_rate')
					->select('order.rmb_to_usd_rate')
					->select('picture1')
					->select('picture2')
					->select('picture3')
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
					->select('productMaster.accessory_remark')
					->select('productMaster.year')
					->select(array(DB::expr("if(is_reject = 'Y' and jp_status = ".Model_OrderProduct::STATUS_SALES.", 0, 1)"), 'seq'))
					->order_by('seq')
					->order_by('order_id', 'desc')->order_by('product_cd')
					->limit($limit)
					->offset($offset)
					->find_all();
	}
	
	public function getCriteria() {
		$orm = ORM::factory('orderProduct')
			->with('order')
			->with('productMaster')
			//->join('product_master')->on('product_master.no_jp' ,'=', 'orderproduct.product_cd')
			->with('containerSummary')
			->join('order_type')->on('order_type.id', '=', 'order.order_type_id')
			->join('customer')->on('customer.id', '=', 'order.customer_id')
			->where('jp_status', '>=', Model_OrderProduct::STATUS_CANCEL)
			->where('factory_status', '>=', Model_OrderProduct::STATUS_CANCEL);
		
		if ($this->customer_id !== NULL && $this->customer_id != 0) {
			$orm->where('order.customer_id', '=', $this->customer_id);
		}
		
		if (!empty($this->search_order_id)) {
			$orm->where('order.id', '=', $this->search_order_id);
		}
		
		if (!empty($this->product_cd)) {
			$orm->where('product_cd', 'like', '%'.$this->product_cd.'%');
		}
		
		if (!empty($this->container_no)) {
			$orm->where('containerSummary.container_no_list', 'like', '%'.$this->container_no.'%');
		}
		
		if (!empty($this->order_type_id)) {
			$orm->where('order_type_id', '=', $this->order_type_id);
		}
		
		if ($this->market_price != '') {
		    $orm->where('market_price', '=', $this->market_price);
		}
		if ($this->status == 'A') {
			// Not complete
			$orm->where('order.status', '<>', Model_Order::STATUS_VOID)
				->and_where_open()
				->or_where('orderproduct.jp_status', '<', Model_OrderProduct::STATUS_INVOICE_GENERATED)
				->or_where('orderproduct.factory_status', '<', Model_OrderProduct::STATUS_INVOICE_GENERATED)
				->and_where_close();
		} else if ($this->status == 'C') {
			// Complete
			$orm->where('order.status', '<>', Model_Order::STATUS_VOID)
				->where('orderproduct.jp_status', '>=', Model_OrderProduct::STATUS_INVOICE_GENERATED)
				->where('orderproduct.factory_status', '>=', Model_OrderProduct::STATUS_INVOICE_GENERATED);
		} else if ($this->status == 'V') {
			$orm->where('order.status', '=', Model_Order::STATUS_VOID);
		}
		
		if (!empty($this->username)) {
			$orm->where('order.created_by', '=', $this->username);
		}
		
		if ($this->order_date_from != '') {
			$orm->where('order.order_date', '>=', $this->order_date_from);
		}
			
		if ($this->order_date_to != '') {
			$toDate = date('Y-m-d', strtotime($this->order_date_to.' + 1 days'));
			$orm->where('order.order_date', '<', $toDate);
		}
		
		$user = Auth::instance()->get_user();
		if ($user->isSales()) {
			$orm->where('order.created_by', '=', $user->username);
		}
		
		return $orm;
	}
	
	public function getQueryString() {
		$query_string = '';
		
		if ($this->customer_id !== NULL) {
			$query_string .= '&customer_id='.$this->customer_id;
		}
		
		if (!empty($this->search_order_id)) {
			$query_string .= '&search_order_id='.$this->search_order_id;
		}
		
		if (!empty($this->product_cd)) {
			$query_string .= '&product_cd='.$this->product_cd;
		}
		
		if (!empty($this->container_no)) {
			$query_string .= '&container_no='.$this->container_no;
		}
		
		if (!empty($this->order_type_id)) {
			$query_string .= '&order_type_id='.$this->order_type_id;
		}
		
		if (!empty($this->market_price)){
			$query_string .= '&market_price='.$this->market_price;
		}
		if ($this->status !== NULL) {
			$query_string .= '&status='.$this->status;
		}
		
		if (!empty($this->username)) {
			$query_string .= '&username='.$this->username;
		}
		
		if ($this->order_date_from != '') {
			$query_string .= '&order_date_from='.$this->order_date_from;
		}
			
		if ($this->order_date_to != '') {
			$query_string .= '&order_date_to='.$this->order_date_to;
		}
		
		return $query_string;
	}
}