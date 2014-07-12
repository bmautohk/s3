<?php
require_once APPPATH.'classes/vendor/PHPExcel.php';

class Model_Translator_OrderProductForm extends Model_PageForm {
	public $action;
	public $search_order_date_from;
	public $search_order_date_to;
	public $search_keyword;
	public $search_is_complete;
	public $search_product_cd;
	public $search_product_desc;
	public $search_made;
	public $search_model;
	public $search_model_no;
	public $search_factory_qty;
	public $search_is_reject;
	public $search_order_id;
	public $factory;
	
	public $orderProducts;
	public $errors;
	
	public $page_url = 'translator/list/factory';
	
	private $inputOrderProducts;
	
	const FACTORY_GZ = "gz";
	const FACTORY_BEN = "ben";
	
	const ACTION_GO_TO_FACTORY = 'go_to_factory';
	const ACTION_BACK_TO_AUDITOR = 'back_to_auditor';
	
	public function __construct($factory = NULL) {
		$this->factory = $factory;
		$this->page_url .= '/'.$factory;
	}

	public function populate($post) {
		parent::populate($post);
		
		$this->action = isset($post['action']) ? $post['action'] : NULL;
		$this->search_order_date_from = isset($post['search_order_date_from']) ? $post['search_order_date_from'] : NULL;
		$this->search_order_date_to = isset($post['search_order_date_to']) ? $post['search_order_date_to'] : NULL;
		$this->search_keyword = isset($post['search_keyword']) ? $post['search_keyword'] : NULL;
		$this->search_is_complete = isset($post['search_is_complete']) ? $post['search_is_complete'] : NULL;
		$this->search_product_cd = isset($post['search_product_cd']) ? trim($post['search_product_cd']) : NULL;
		$this->search_product_desc = isset($post['search_product_desc']) ? trim($post['search_product_desc']) : NULL;
		$this->search_made = isset($post['search_made']) ? trim($post['search_made']) : NULL;
		$this->search_model = isset($post['search_model']) ? trim($post['search_model']) : NULL;
		$this->search_model_no = isset($post['search_model_no']) ? trim($post['search_model_no']) : NULL;
		$this->search_factory_qty = isset($post['search_factory_qty']) ? trim($post['search_factory_qty']) : NULL;
		$this->search_is_reject = isset($post['search_is_reject']) ? $post['search_is_reject'] : NULL;
		$this->search_order_id = isset($post['search_order_id']) ? $post['search_order_id'] : NULL;
		
		if (isset($post['orderProducts'])) {
			$this->inputOrderProducts = array(); // order_product_id <-> order_product
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
	
	public function processGoToFactoryAction() {
		$result = $this->goToFactory();
	
		$this->item_count = NULL; // Re-count total # of record
		$this->searchAction();
	
		return $result;
	}
	
	public function processBackToAuditorAction() {
		$result = $this->backToAuditor();
	
		$this->item_count = NULL; // Re-count total # of record
		$this->searchAction();
	
		return $result;
	}
	
	public function getFactoryDescription() {
		if ($this->factory == GlobalConstant::FORM_FACTORY_BEN) {
			return 'ben';
		} else if ($this->factory == GlobalConstant::FORM_FACTORY_GZ) {
			return '厰';
		}
	}

	private function goToFactory() {
		$this->errors = array();

		$saveList = $this->validation();
		
		if (!empty($this->errors)) {
			return false;
		}
		
		if (sizeOf($saveList) > 0) {
			// Save to DB
			$db = Database::instance();
			$db->begin();
			
			try {
				foreach ($saveList as $orderProduct) {
					$orderProduct->goToFactory();
				}
			} catch (Exception $e) {
				$db->rollback();
				$this->errors[] = $e->getMessage();
				return false;
			}
			
			$db->commit();
		}
		
		return true;
	}
	
	private function backToAuditor() {
		$this->errors = array();
		
		// Validation
		$saveList = $this->validation(true);
			
		if (sizeOf($saveList) > 0) {
			// Save to DB
			$db = Database::instance();
			$db->begin();
				
			try {
				foreach ($saveList as $orderProduct) {
					$orderProduct->backToAuditor();
				}
			} catch (Exception $e) {
				$db->rollback();
				$this->errors[] = $e->getMessage();
				return false;
			}
				
			$db->commit();
		}
		
		return true;
	}
	
	private function validation($isBackToAuditor = false) {
		$rowNo = 1;
		$isValid = true;
		$saveList = array();
		foreach ($this->inputOrderProducts as $formValue) {
			if (isset($formValue['selected'])) {
				$orderProduct = ORM::factory('translator_OrderProduct')
								->where('id', '=', $formValue['id'])
								->find();
		
				try {
					$orderProduct->populate($formValue);
					$orderProduct->check();
					
					/* if (!$isBackToAuditor) {
						if (empty($orderProduct->translator_remark)) {
							$isValid = false;
							$this->errors[] = 'Row '.$rowNo.': 高原 Remark must not be empty';
							continue;
						}
					} */
					
					$saveList[] = $orderProduct;
				} catch (ORM_Validation_Exception $e) {
					$isValid = false;
					foreach ($e->errors('translator') as $error) {
						$this->errors[] = 'Row '.$rowNo.': '.$error;
					}
				}
			}
				
			$rowNo++;
		}
		
		return $saveList;
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
		->setTitle("translator");
	
		$sheet = $objPHPExcel->setActiveSheetIndex(0);
	
		// Header
		$i = 0;
		$sheet->setCellValueByColumnAndRow($i++, 1, __('label.order_no'))
		->setCellValueByColumnAndRow($i++, 1, __('label.order_date'))
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
		->setCellValueByColumnAndRow($i++, 1, __('label.accessory_remark'))
		->setCellValueByColumnAndRow($i++, 1, __('label.year'))
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
		->setCellValueByColumnAndRow($i++, 1,  $this->getFactoryDescription())
		->setCellValueByColumnAndRow($i++, 1, 'Kaito Staff Remark')
		->setCellValueByColumnAndRow($i++, 1, 'Auditor Remark')
		->setCellValueByColumnAndRow($i++, 1, '高原 Remark')
		->setCellValueByColumnAndRow($i++, 1, 'Void Remark')
		->setCellValueByColumnAndRow($i++, 1, 'pm 設定了的供應商')
		->setCellValueByColumnAndRow($i++, 1, '発送方法');
	
		$rowNo = 1;
		$orderProducts = $this->getData(NULL, NULL);
		foreach($orderProducts as $orderProduct) {
			$i = 0;
			$rowNo++;
				
			$sheet->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->order_id)
			->setCellValueByColumnAndRow($i++, $rowNo, date('Y-m-d', strtotime($orderProduct->order->order_date)))
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
			->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->accessory_remark)
			->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->year)
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
			->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->factory_qty)
			->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->kaito_remark)
			->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->factory_auditor_remark)
			->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->translator_remark)
			->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->factory_remark)
			->setCellValueByColumnAndRow($i++, $rowNo, $orderProduct->supplier)
			->setCellValueByColumnAndRow($i++, $rowNo, Model_Order::getDisplayDeliveryMethod($orderProduct->delivery_method_description, $orderProduct->delivery_method))
			;
		}
	
		header("Content-type:application/vnd.ms-excel");
		header('Content-Disposition: attachment;filename="translator.xls"');
		header('Cache-Control: max-age=0');
	
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
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
				->select(array(DB::expr("if(is_reject = 'Y' and factory_status = ".Model_OrderProduct::STATUS_TRANSLATOR.", 0, 1)"), 'seq'))
				->order_by('seq')
				->order_by('order_id', 'desc')->order_by('product_cd')
				->limit($limit)
				->offset($offset)
				->find_all();
	}
	
	public function getCriteria() {
		$orm = ORM::factory('translator_OrderProduct')
				->with('order')
				->with('productMaster')
				//->join('product_master')->on('product_master.no_jp' ,'=', 'translator_orderproduct.product_cd')
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
		
		$orm->where('factory_status', '>=', Model_OrderProduct::STATUS_TRANSLATOR);
	
		// Search un-complete records
		if ($this->search_is_complete == 'N') {
			$orm->where('factory_status', '=', Model_OrderProduct::STATUS_TRANSLATOR);
		} else if ($this->search_is_complete == 'Y') {
			$orm->where('factory_status', '>', Model_OrderProduct::STATUS_TRANSLATOR);
		} else {
			$orm->where('factory_status', '>=', Model_OrderProduct::STATUS_TRANSLATOR);
		}
		
		if ($this->search_product_cd != '') {
			$orm->where('productMaster.no_jp', 'like', '%'.$this->search_product_cd.'%');
		}
		
		if ($this->search_product_desc != '') {
			$orm->where('productMaster.product_desc', 'like', '%'.$this->search_product_desc.'%');
		}
		
		if ($this->search_made != '') {
			$orm->where('productMaster.made', 'like', '%'.$this->search_made.'%');
		}
		
		if ($this->search_model != '') {
			$orm->where('productMaster.model', 'like', '%'.$this->search_model.'%');
		}
		
		if ($this->search_model_no != '') {
			$orm->where('productMaster.model_no', 'like', '%'.$this->search_model_no.'%');
		}
		
		if ($this->search_factory_qty != '') {
			$orm->where('translator_orderproduct.factory_qty', '=', $this->search_factory_qty);
		}
		
		if ($this->search_is_reject == 'Y') {
			$orm->where('translator_orderproduct.is_reject', '=', Model_OrderProduct::IS_REJECT_YES)
				->where('translator_orderproduct.factory_status', '=', Model_OrderProduct::STATUS_TRANSLATOR);
		} else if ($this->search_is_reject == 'N') {
			$orm->and_where_open()
				->or_where('translator_orderproduct.is_reject', '<>', Model_OrderProduct::IS_REJECT_YES)
				->or_where('translator_orderproduct.factory_status', '<>', Model_OrderProduct::STATUS_TRANSLATOR)
				->and_where_close();
		}
		
		$orm->where('factory', '=', $this->factory == Model_Translator_OrderProductForm::FACTORY_BEN ? Model_OrderProduct::FACTORY_BEN : Model_OrderProduct::FACTORY_GZ);
		
		return $orm;
	}
	
	public function getQueryString() {
		$query_string = '&search_order_date_from='.$this->search_order_date_from.'&search_order_date_to='.$this->search_order_date_to;
		$query_string .= '&search_is_complete='.$this->search_is_complete;
		$query_string .= '&search_product_cd='.$this->search_product_cd;
		$query_string .= '&search_product_desc='.$this->search_product_desc;
		$query_string .= '&search_made='.$this->search_made;
		$query_string .= '&search_model='.$this->search_model;
		$query_string .= '&search_model_no='.$this->search_model_no;
		$query_string .= '&search_factory_qty='.$this->search_factory_qty;
		$query_string .= '&search_is_reject='.$this->search_is_reject;
		if (!empty($this->search_order_id)) {
			$query_string .= '&search_order_id='.$this->search_order_id;
		}
		return $query_string;
	}
}
