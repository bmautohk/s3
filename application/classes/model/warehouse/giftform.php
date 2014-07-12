<?php
require_once APPPATH.'classes/vendor/PHPExcel.php';

class Model_Warehouse_GiftForm extends Model_PageForm {	
	public $action;
	public $container_no;
	public $product_cd;
	public $product_desc;
	public $customer_id;
	public $delivery_date_from;
	public $delivery_date_to;
	public $container_input_date_from;
	public $container_input_date_to;
	
	public $gifts;
	
	public $page_url = 'warehouse/gift_list';
	
	public function populate($post) {
		parent::populate($post);
		
		$this->action = isset($post['action']) ? $post['action'] : NULL;
		$this->container_no = isset($post['container_no']) ? $post['container_no'] : NULL;
		$this->product_cd = isset($post['product_cd']) ? $post['product_cd'] : NULL;
		$this->product_desc = isset($post['product_desc']) ? $post['product_desc'] : NULL;
		$this->customer_id = isset($post['customer_id']) ? $post['customer_id'] : NULL;
		
		$this->delivery_date_from = isset($post['delivery_date_from']) ? $post['delivery_date_from'] : NULL;
		$this->delivery_date_to = isset($post['delivery_date_to']) ? $post['delivery_date_to'] : NULL;
		
		$this->container_input_date_from = isset($post['container_input_date_from']) ? $post['container_input_date_from'] : NULL;
		$this->container_input_date_to = isset($post['container_input_date_to']) ? $post['container_input_date_to'] : NULL;
	}
	
	public function searchAction() {
		$this->gifts = $this->search();
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
		->setTitle("gift");
	
		$sheet = $objPHPExcel->setActiveSheetIndex(0);
	
		// Header
		$i = 0;
		$sheet->setCellValueByColumnAndRow($i++, 1, 'Cust Code')
				->setCellValueByColumnAndRow($i++, 1, '交貨日期')
				->setCellValueByColumnAndRow($i++, 1, '入櫃日期')
				->setCellValueByColumnAndRow($i++, 1, '櫃號')
				->setCellValueByColumnAndRow($i++, 1, '運送貨量')
				->setCellValueByColumnAndRow($i++, 1, 'Brand name(pm.車種)')
				->setCellValueByColumnAndRow($i++, 1, 'Car Name(車型)')
				->setCellValueByColumnAndRow($i++, 1, 'Model Name(型號)')
				->setCellValueByColumnAndRow($i++, 1, 'Color')
				->setCellValueByColumnAndRow($i++, 1, 'Color No')
				->setCellValueByColumnAndRow($i++, 1, '件數')
				->setCellValueByColumnAndRow($i++, 1, '貨品編號')
				->setCellValueByColumnAndRow($i++, 1, '貨品名稱')
				->setCellValueByColumnAndRow($i++, 1, 'Material')
				->setCellValueByColumnAndRow($i++, 1, 'Cost')
		;
	
		$rowNo = 1;
		$gifts = $this->getData(NULL, NULL);
		foreach($gifts as $gift) {
			$i = 0;
			$rowNo++;
	
			$sheet->setCellValueByColumnAndRow($i++, $rowNo, $gift->cust_code)
				->setCellValueByColumnAndRow($i++, $rowNo, $gift->delivery_date)
				->setCellValueByColumnAndRow($i++, $rowNo, $gift->container_input_date)
				->setCellValueByColumnAndRow($i++, $rowNo, $gift->container_no)
				->setCellValueByColumnAndRow($i++, $rowNo, $gift->delivery_qty)
				->setCellValueByColumnAndRow($i++, $rowNo, $gift->made)
				->setCellValueByColumnAndRow($i++, $rowNo, $gift->model)
				->setCellValueByColumnAndRow($i++, $rowNo, $gift->model_no)
				->setCellValueByColumnAndRow($i++, $rowNo, $gift->colour)
				->setCellValueByColumnAndRow($i++, $rowNo, $gift->colour_no)
				->setCellValueByColumnAndRow($i++, $rowNo, $gift->qty)
				->setCellValueByColumnAndRow($i++, $rowNo, $gift->product_cd)
				->setCellValueByColumnAndRow($i++, $rowNo, $gift->product_desc)
				->setCellValueByColumnAndRow($i++, $rowNo, $gift->material)
				->setCellValueByColumnAndRow($i++, $rowNo, $gift->cost)
			;
		}
	
		header("Content-type:application/vnd.ms-excel");
		header('Content-Disposition: attachment;filename="gift.xls"');
		header('Cache-Control: max-age=0');
	
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
	}
	
	/* public function addDeliveryNoteAction($gift_id) {
		$result = $this->add_delivery_note($gift_id);
		
		$this->searchAction();
	
		return $result;
	}
	
	private function add_delivery_note($gift_id) {
		$db = Database::instance();
		$db->begin();
	
		$this->errors = array();
	
		try {
			$gift = new Model_Gift($gift_id);
			$gift->status = Model_Gift::STATUS_ACCOUNTANT;
			$gift->save();
			
			// Create container
			$container = new Model_Container();
			$container->gift_id = $gift->id;
			$container->container_no = $gift->container_no;
			$container->delivery_qty = $gift->delivery_qty;
			$container->orig_delivery_qty = $gift->delivery_qty;
			$container->delivery_date = $gift->delivery_date;
			$container->container_input_date = $gift->container_input_date;
			$container->source = Model_Container::SOURCE_GIFT;
			$container->status = Model_Container::STATUS_READY_FOR_DELIVERY_NOTE;
			
			$username = Auth::instance()->get_user()->username;
			$container->created_by = $username;
			$container->create_date = DB::expr('current_timestamp');
			$container->last_updated_by = $username;
			$container->save();
			
		} catch (Exception $e) {
			$db->rollback();
			$this->errors[] = $e->getMessage();
			return false;
		}
	
		$db->commit();
	
		return true;
	} */

// Overrided function
	public function getData($limit, $offset) {
		return $this->getCriteria()
				->select('customer.cust_code')
				->order_by('delivery_date', 'desc')
				->find_all();
	}
	
	public function getCriteria() {
		$orm = ORM::factory('gift')
				->join('customer')->on('customer.id', '=', 'gift.customer_id');
		
		if (!empty($this->container_no)) {
			$orm->where('container_no', '=', $this->container_no);
		}
		
		if (!empty($this->product_cd)) {
			$orm->where('product_cd', 'like', '%'.$this->product_cd.'%');
		}
		
		if (!empty($this->product_desc)) {
			$orm->where('product_desc', 'like', '%'.$this->product_desc.'%');
		}
		
		if (!empty($this->customer_id)) {
			$orm->where('customer_id', '=', $this->customer_id);
		}
		
		if (!empty($this->delivery_date_from)) {
			$orm->where('delivery_date', '>=', $this->delivery_date_from);
		}
			
		if (!empty($this->delivery_date_to)) {
			$toDate = date('Y-m-d', strtotime($this->delivery_date_to.' + 1 days'));
			$orm->where('delivery_date', '<', $toDate);
		}
		
		if (!empty($this->container_input_date_from)) {
			$orm->where('container_input_date', '>=', $this->container_input_date_from);
		}
			
		if (!empty($this->container_input_date_to)) {
			$toDate = date('Y-m-d', strtotime($this->container_input_date_to.' + 1 days'));
			$orm->where('container_input_date', '<', $toDate);
		}
		
		return $orm;
	}
	
	public function getQueryString() {
		$query_string = '';
	
		if (!empty($this->container_no)) {
			$query_string .= '&container_no='.$this->container_no;
		}
		
		if (!empty($this->product_cd)) {
			$query_string .= '&product_cd='.$this->product_cd;
		}
		
		if (!empty($this->product_desc)) {
			$query_string .= '&product_desc='.$this->product_desc;
		}
		
		if (!empty($this->customer_id)) {
			$query_string .= '&customer_id='.$this->customer_id;
		}
		
		if (!empty($this->delivery_date_from)) {
			$query_string .= '&delivery_date_from='.$this->delivery_date_from;
		}
		
		if (!empty($this->delivery_date_to)) {
			$query_string .= '&delivery_date_to='.$this->delivery_date_to;
		}
		
		if (!empty($this->container_input_date_from)) {
			$query_string .= '&container_input_date_from='.$this->container_input_date_from;
		}
		
		if (!empty($this->container_input_date_to)) {
			$query_string .= '&container_input_date_to='.$this->container_input_date_to;
		}
		
		return $query_string;
	}
}