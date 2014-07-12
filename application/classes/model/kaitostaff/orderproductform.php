<?php
require_once APPPATH.'classes/vendor/PHPExcel.php';

class Model_Kaitostaff_OrderProductForm extends Model_PageForm {
	public $action;
	public $search_action;
	public $search_order_date_from;
	public $search_order_date_to;
	public $search_keyword;
	public $search_product_cd;
	public $search_customer_id;
	public $search_is_complete;
	public $search_order_id;
	public $orderProducts;
	public $errors;
	
	public $page_url = 'kaitostaff/list';
	
	private $inputOrderProducts;
	
	const ACTION_GO_TO_AUDITOR = 'go_to_auditor';
	const ACTION_BACK_TO_SALES = 'back_to_sales';

	public function populate($post) {
		parent::populate($post);
		
		$this->action = isset($post['action']) ? $post['action'] : NULL;
		$this->search_action = isset($post['search_action']) ? $post['search_action'] : NULL;
		$this->search_order_date_from = isset($post['search_order_date_from']) ? $post['search_order_date_from'] : NULL;
		$this->search_order_date_to = isset($post['search_order_date_to']) ? $post['search_order_date_to'] : NULL;
		$this->search_keyword = isset($post['search_keyword']) ? $post['search_keyword'] : NULL;
		$this->search_product_cd = isset($post['search_product_cd']) ? trim($post['search_product_cd']) : NULL;
		$this->search_customer_id = isset($post['search_customer_id']) ? $post['search_customer_id'] : NULL;
		$this->search_is_complete = isset($post['search_is_complete']) ? $post['search_is_complete'] : NULL;
		$this->search_order_id = isset($post['search_order_id']) ? $post['search_order_id'] : NULL;
		
		if (isset($post['orderProducts'])) {
			$this->inputOrderProducts = $post['orderProducts'];
		} else {
			$this->inputOrderProducts = array();
		}
	}

	public function searchAction() {
		$result = $this->search();
		$this->orderProducts = $this->mergeFormValue($result);
	}
	
	public function exportAction() {
		$this->export();
	}
	
	public function saveForm() {
		if ($this->action == Model_Kaitostaff_OrderProductForm::ACTION_GO_TO_AUDITOR) {
			$this->goToAuditor();
		} else if ($this->action == Model_Kaitostaff_OrderProductForm::ACTION_BACK_TO_SALES) {
			$this->backToSales();
		}
		
		$this->item_count = NULL;
		$this->searchAction();
	}

	private function goToAuditor() {
		$this->errors = array();
		
		// Validation
		$rowNo = 1;
		$isValid = true;
		$saveList = array();
		foreach ($this->inputOrderProducts as $formValue) {
			if (isset($formValue['selected'])) {
				$orderProduct = ORM::factory('kaitostaff_OrderProduct')
								->where('id', '=', $formValue['id'])
								->find();
				
				try {
					$orderProduct->populate($formValue);
					$orderProduct->check();
					
					$saveList[] = $orderProduct;
				} catch (ORM_Validation_Exception $e) {
					$isValid = false;
					foreach ($e->errors('kaitostaff') as $error) {
						$this->errors[] = 'Order No['.$orderProduct->order_id.'] Product['.$orderProduct->product_cd.']: '.$error;
					}
				}
			}
			
			$rowNo++;
		}
		 
		if ($isValid) {
			// Save to DB
			$db = Database::instance();
			$db->begin();
			
			try {
				foreach ($saveList as $orderProduct) {
					$orderProduct->goToAuditor();
				}
			} catch (Exception $e) {
				$db->rollback();
				$this->errors[] = $e->getMessage();
				return;
			}
			
			$db->commit();
		}
	}
	
	public function backToSales() {
		$this->errors = array();
		
		// Update status to 10
		$db = Database::instance();
		$db->begin();
		
		try {
			foreach ($this->inputOrderProducts as $formValue) {
				if (isset($formValue['selected'])) {
					$orderProduct = ORM::factory('kaitostaff_OrderProduct')
									->where('id', '=', $formValue['id'])
									->find();

					$orderProduct->kaito_remark = $formValue['kaito_remark'];
					$orderProduct->backToSales();
				}
			}
		} catch (Exception $e) {
			$db->rollback();
			$this->errors[] = $e->getMessage();
			return;
		}
		
		$db->commit();
	}
	
	private function mergeFormValue($orderProducts) {
		if (isset($this->inputOrderProducts)) {
			$orderProductMap = array(); // order_product_id <-> order_product
			foreach ($this->inputOrderProducts as $orderProduct) {
				$orderProductMap[$orderProduct['id']] = $orderProduct;
			}
			
			$newList = array();
			foreach ($orderProducts as $orderProduct) {
				if (array_key_exists($orderProduct->id, $orderProductMap)) {
					$formValue = $orderProductMap[$orderProduct->id];
					$orderProduct->populate($formValue);
				}
				
				$newList[] = $orderProduct;
			}
			return $newList;
		} else {
			return $orderProducts;
		}
	}
	
	private function export() {
		$cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
		$cacheSettings = array('memoryCacheSize' => '8MB');
		PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
		
		$objPHPExcel = new PHPExcel();
		
		$objPHPExcel->getProperties()->setCreator("S3")
					->setLastModifiedBy("S3")
					->setTitle("大步哥");
		
		$sheet = $objPHPExcel->setActiveSheetIndex(0);
		
		// Header
		$i = 0;
		$sheet->setCellValueByColumnAndRow($i++, 1, 'Order No.')
		->setCellValueByColumnAndRow($i++, 1, '退單')
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
		->setCellValueByColumnAndRow($i++, 1, 'container no.')
		->setCellValueByColumnAndRow($i++, 1, '交貨日期')
		->setCellValueByColumnAndRow($i++, 1, '入櫃日期')
		->setCellValueByColumnAndRow($i++, 1, '庫')
		->setCellValueByColumnAndRow($i++, 1, '廠')
		->setCellValueByColumnAndRow($i++, 1, 'ben')
		->setCellValueByColumnAndRow($i++, 1, 'Kaito Staff Remark')
		->setCellValueByColumnAndRow($i++, 1, 'Sales Remark')
		->setCellValueByColumnAndRow($i++, 1, 'Auditor Remark (國內)')
		->setCellValueByColumnAndRow($i++, 1, 'Auditor Remark (factory)')
		->setCellValueByColumnAndRow($i++, 1, '高原 Remark')
		->setCellValueByColumnAndRow($i++, 1, 'pm 設定了的供應商')
		->setCellValueByColumnAndRow($i++, 1, '発送方法');
		
		$rowNo = 1;
		$orderProducts = $this->getData(NULL, NULL);
		foreach($orderProducts as $orderProduct) {
			$i = 0;
			$rowNo++;
			
			$sheet->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->order_id)
			->setCellValueByColumnAndRow($i++, $rowNo, '')
			->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->cust_code)
			->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->product_cd)
			->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->qty)
			->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->market_price)
			->setCellValueByColumnAndRow($i++, $rowNo, '')
			->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->kaito)
			->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->product_desc)
			->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->made)
			->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->model)
			->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->model_no)
			->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->colour)
			->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->colour_no)
			->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->pcs)
			->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->material)
			->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->getSubTotal())
			->setCellValueByColumnAndRow($i++, $rowNo, '')
			->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->profit)
			->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->getTaxDescription())
			->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->delivery_fee)
			->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->containerSummary->container_no_list)
			->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->containerSummary->delivery_date_list)
			->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->containerSummary->container_input_date_list)
			->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->jp_qty)
			->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->getGzQty())
			->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->getBenQty())
			->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->kaito_remark)
			->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->order->remark)
			->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->jp_auditor_remark)
			->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->factory_auditor_remark)
			->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->translator_remark)
			->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->supplier)
			->setCellValueByColumnAndRow($i++, $rowNo, Model_Order::getDisplayDeliveryMethod($orderProduct->delivery_method_description, $orderProduct->delivery_method))
			;
		}
		
		header("Content-type:application/vnd.ms-excel");
		header('Content-Disposition: attachment;filename="kaitostaff.xls"');
		header('Cache-Control: max-age=0');
		
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
	}
	
// Overrided function
	public function getData($limit, $offset) {
		return $this->getCriteria()
				->select('cust_code')
				//->select(array(DB::expr("IF(order.delivery_method_id = ".Model_DeliveryMethod::ID_OTHER.", CONCAT(delivery_method.description, ' - ', order.delivery_method), delivery_method.description)"), 'delivery_method'))
				->select(array('order_type.description', 'order_type_description'))
				->select(array('delivery_method.description', 'delivery_method_description'))
				->select('order.delivery_method')
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
				->select('productMaster.supplier')
				->select(array(DB::expr("if(is_reject = 'Y' and (jp_status = ".Model_OrderProduct::STATUS_KAITOSTAFF." or factory_status = ".Model_OrderProduct::STATUS_KAITOSTAFF."), 0, 1)"), 'seq'))
				->order_by('seq')
				->order_by('order_id', 'desc')
				->order_by('product_cd')
				->limit($limit)
				->offset($offset)
				->find_all();
	}
	
	public function getCriteria() {
		$orm = ORM::factory('kaitostaff_OrderProduct')
				->with('order')
				->with('productMaster')
				//->join('product_master')->on('product_master.no_jp' ,'=', 'kaitostaff_orderproduct.product_cd')
				->with('containerSummary')
				->join('order_type')->on('order_type.id', '=', 'order.order_type_id')
				->join('customer')->on('customer.id', '=', 'order.customer_id')
				->join('delivery_method')->on('delivery_method.id', '=', 'order.delivery_method_id');
		
		if (!empty($this->search_order_id)) {
			$orm->where('order.id', '=', $this->search_order_id);
		}
		
		// Search by date
		if ($this->search_order_date_from != '') {
			$orm->where('order.order_date', '>=', $this->search_order_date_from);
		}
		
		if ($this->search_order_date_to != '') {
			$toDate = date('Y-m-d', strtotime($this->search_order_date_to.' + 1 days'));
			$orm->where('order.order_date', '<', $toDate);
		}

		if (!empty($this->search_product_cd)) {
			$orm->where('product_cd', 'like', '%'.$this->search_product_cd.'%');
		}
		
		if ($this->search_customer_id != 0) {
			$orm->where('order.customer_id', '=', $this->search_customer_id);
		}
		
		if ($this->search_is_complete == 'N') {
			$orm->and_where_open()
				->or_where('jp_status', '=', Model_OrderProduct::STATUS_KAITOSTAFF)
				->or_where('factory_status', '=', Model_OrderProduct::STATUS_KAITOSTAFF)
				->and_where_close();
		} else if ($this->search_is_complete == 'Y') {
			$orm->where('jp_status', '>', Model_OrderProduct::STATUS_KAITOSTAFF)
				->where('factory_status', '>', Model_OrderProduct::STATUS_KAITOSTAFF);
		} else {
			$orm->and_where_open()
				->or_where('jp_status', '>=', Model_OrderProduct::STATUS_KAITOSTAFF)
				->or_where('factory_status', '>=', Model_OrderProduct::STATUS_KAITOSTAFF)
				->and_where_close();
		}

		return $orm;
	}
	
	public function getQueryString() {
	
	
		$query_string = '&search_order_date_from='.$this->search_order_date_from.'&search_order_date_to='.$this->search_order_date_to;
		$query_string .= '&search_product_cd='.$this->search_product_cd.'&search_customer_id='.$this->search_customer_id.'&search_is_complete='.$this->search_is_complete;
		if (!empty($this->search_order_id)) {
			$query_string .= '&search_order_id='.$this->search_order_id;
		}
		
		return $query_string;
	}
}
