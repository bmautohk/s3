<?php
require_once APPPATH.'classes/vendor/PHPExcel.php';

class Model_Auditor_FactoryOrderProductForm extends Model_PageForm {
	public $action;
	public $search_action;
	public $search_order_date_from;
	public $search_order_date_to;
	public $search_keyword;
	public $search_is_complete;
	public $search_order_id;
	public $factory;
	
	public $orderProducts;
	public $errors;
	
	public $page_url = 'auditor/list/factory';
	
	private $inputOrderProducts;
	
	const ACTION_GO_TO_TRANSLATOR = 'go_to_translator';
	const ACTION_BACK_TO_KAITOSTAFF = 'back_to_kaitostaff';
	
	public function __construct($factory = NULL) {
		$this->factory = $factory;
		$this->page_url .= '/'.$factory;
	}

	public function populate($post) {
		parent::populate($post);
		
		$this->action = isset($post['action']) ? $post['action'] : NULL;
		$this->search_action = isset($post['search_action']) ? $post['search_action'] : NULL;
		$this->search_order_date_from = isset($post['search_order_date_from']) ? $post['search_order_date_from'] : NULL;
		$this->search_order_date_to = isset($post['search_order_date_to']) ? $post['search_order_date_to'] : NULL;
		$this->search_keyword = isset($post['search_keyword']) ? $post['search_keyword'] : NULL;
		$this->search_is_complete = isset($post['search_is_complete']) ? $post['search_is_complete'] : NULL;
		$this->search_order_id = isset($post['search_order_id']) ? $post['search_order_id'] : NULL;
		$this->inputOrderProducts = array(); // order_product_id <-> order_product
		if (isset($post['orderProducts'])) {
			foreach ($post['orderProducts'] as $orderProduct) {
				$this->inputOrderProducts[$orderProduct['id']] = $orderProduct;
			}
		}
	}
	
	public function searchAction() {
		$result = $this->search();
		$this->orderProducts = $this->mergeFormValue($result);
	}
	
	public function exportAction() {
		$this->export();
	}
	
	public function processGoToTranslatorAction() {
		$result = $this->goToTranslator();
	
		$this->item_count = NULL; // Re-count total # of record
		$this->searchAction();
	
		return $result;
	}
	
	public function processBackToKaitostaffAction() {
		$result = $this->backToKaitostaff();
	
		$this->item_count = NULL; // Re-count total # of record
		$this->searchAction();
	
		return $result;
	}
	
	public function getFactoryDescription() {
		if ($this->factory == GlobalConstant::FORM_FACTORY_BEN) {
			return 'ben';
		} else if ($this->factory == GlobalConstant::FORM_FACTORY_GZ) {
			return '廠';
		}
	}

	private function goToTranslator() {
		$this->errors = array();
		
		// Validation
		$rowNo = 1;
		$isValid = true;
		$saveList = array();
		foreach ($this->inputOrderProducts as $formValue) {
			if (isset($formValue['selected'])) {
				$orderProduct = ORM::factory('auditor_FactoryOrderProduct')
								->where('id', '=', $formValue['id'])
								->find();
				
				try {
					$orderProduct->populate($formValue);
					$orderProduct->check();
					
					$saveList[] = $orderProduct;
				} catch (ORM_Validation_Exception $e) {
					$isValid = false;
					foreach ($e->errors('auditor') as $error) {
						$this->errors[] = 'Row '.$rowNo.': '.$error;
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
					$orderProduct->goToTranslator();
				}
			} catch (Exception $e) {
				$db->rollback();
				$this->errors[] = $e->getMessage();
				return false;
			}
			
			$db->commit();
			
			return true;
		} else {
			return false;
		}
	}
	
	private function backToKaitostaff() {
		$this->errors = array();
		
		// Update process_status to 20
		$db = Database::instance();
		$db->begin();
		
		try {
			foreach ($this->inputOrderProducts as $order_product_id=>$formValue) {
				if (isset($formValue['selected'])) {
					$orderProduct = ORM::factory('auditor_FactoryOrderProduct')
									->where('id', '=', $order_product_id)
									->find();

					$orderProduct->populate($formValue);
					$orderProduct->backToKaitostaff();
				}
			}
		} catch (Exception $e) {
			$db->rollback();
			$this->errors[] = $e->getMessage();
			return false;
		}
		
		$db->commit();
		
		return true;
	}
	
	private function mergeFormValue($orderProducts) {
		if (isset($this->inputOrderProducts)) {
			$newList = array();
			foreach ($orderProducts as $orderProduct) {
				if (array_key_exists($orderProduct->id, $this->inputOrderProducts)) {
					$formValue = $this->inputOrderProducts[$orderProduct->id];
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
		->setTitle("Auditor");
	
		$sheet = $objPHPExcel->setActiveSheetIndex(0);
		$sheet->setCellValueByColumnAndRow(0, 1, 'ABC');
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
		->setCellValueByColumnAndRow($i++, 1, $this->getFactoryDescription())
		->setCellValueByColumnAndRow($i++, 1, 'Sales Remark')
		->setCellValueByColumnAndRow($i++, 1, 'Kaito Staff Remark')
		->setCellValueByColumnAndRow($i++, 1, 'Auditor Remark')
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
			->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->getFactoryQty())
			->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->order->remark)
			->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->kaito_remark)
			->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->getAuditorRemark())
			->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->translator_remark)
			->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->supplier)
			->setCellValueByColumnAndRow($i++, $rowNo, Model_Order::getDisplayDeliveryMethod($orderProduct->delivery_method_description, $orderProduct->delivery_method))
			;
		}

		header("Content-Type:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
		header('Content-Disposition: attachment;filename="auditor.xlsx"');
		header('Cache-Control: max-age=0');
		
		$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
		$objWriter->save('php://output');
	}
	
// Overrided function
	public function getData($limit, $offset) {
		return $this->getCriteria()
				->select('cust_code')
				->select(array('order_type.description', 'order_type_description'))
				->select(array('delivery_method.description', 'delivery_method_description'))
				->select('order.delivery_method')
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
				->select(array(DB::expr("if(is_reject = 'Y' and factory_status = ".Model_OrderProduct::STATUS_AUDITOR.", 0, 1)"), 'seq'))
				->order_by('seq')
				->order_by('order_id', 'desc')->order_by('product_cd')
				->limit($limit)
				->offset($offset)
				->find_all();
	}
	
	public function getCriteria() {
		$orm = ORM::factory('auditor_FactoryOrderProduct')
				->with('order')
				->with('productMaster')
				//->join('product_master')->on('product_master.no_jp' ,'=', 'auditor_factoryorderproduct.product_cd')
				->join('order_type')->on('order_type.id', '=', 'order.order_type_id')
				->with('containerSummary')
				->join('customer')->on('customer.id', '=', 'order.customer_id')
				->join('delivery_method')->on('delivery_method.id', '=', 'order.delivery_method_id');
				
				
		if (!empty($this->search_order_id)) {
			$orm->where('order.id', '=', $this->search_order_id);
		}
		
		if ($this->search_order_date_from != '') {
			$orm->where('order.order_date', '>=', $this->search_order_date_from);
		}
		
		if ($this->search_order_date_to != '') {
			$toDate = date('Y-m-d', strtotime($this->search_order_date_to.' + 1 days'));
			$orm->where('order.order_date', '<', $toDate);
		}

		// Search un-complete records
		if ($this->search_is_complete == 'N') {
			$orm = $orm->where('factory_status', '=', Model_OrderProduct::STATUS_AUDITOR);
		} else if ($this->search_is_complete == 'Y') {
			$orm = $orm->where('factory_status', '>', Model_OrderProduct::STATUS_AUDITOR);
		} else {
			$orm = $orm->where('factory_status', '>=', Model_OrderProduct::STATUS_AUDITOR);
		}
		
		$orm = $orm->where('factory', '=', $this->factory == GlobalConstant::FORM_FACTORY_BEN ? Model_OrderProduct::FACTORY_BEN : Model_OrderProduct::FACTORY_GZ);
		
		return $orm;
	}
	
	public function getQueryString() {
		$query_string = '&search_order_date_from='.$this->search_order_date_from.'&search_order_date_to='.$this->search_order_date_to;
		$query_string .= '&search_is_complete='.$this->search_is_complete;
		if (!empty($this->search_order_id)) {
			$query_string .= '&search_order_id='.$this->search_order_id;
		}
		return $query_string;
	}
}
