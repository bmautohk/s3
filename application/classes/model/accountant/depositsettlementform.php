<?php
require_once APPPATH.'classes/vendor/PHPExcel.php';

class Model_Accountant_DepositSettlementForm extends Model_PageForm {
	public $action;
	public $order_id;
	public $customer_id;
	public $settle_date_from;
	public $settle_date_to;
	public $deposit_settle_id;
	public $rmb_to_jpy_rate;
	
	public $depositSettle;
	public $depositSettleHistory;
	public $errors;
	
	public $page_url = 'accountant/deposit_settlement';
	
	public function populate($post) {
		parent::populate($post);
		
		$this->action = isset($post['action']) ? $post['action'] : NULL;
		$this->order_id = isset($post['order_id']) ? trim($post['order_id']) : NULL;
		$this->customer_id = isset($post['customer_id']) ? trim($post['customer_id']) : NULL;
		$this->settle_date_from = isset($post['settle_date_from']) ? trim($post['settle_date_from']) : NULL;
		$this->settle_date_to = isset($post['settle_date_to']) ? trim($post['settle_date_to']) : NULL;
		$this->deposit_settle_id = isset($post['deposit_settle_id']) ? trim($post['deposit_settle_id']) : NULL;
		$this->remark = isset($post['remark']) ? trim($post['remark']) : NULL;
		
		$this->depositSettle = new Model_DepositSettle();
		$this->depositSettle->values($post);
	}
	
	public function processSearchAction() {
		$this->depositSettleHistory = $this->search();
	}
	
	public function exportAction() {
		$this->export();
	}
	
	public function processConfirmInit() {
		$this->depositSettle = Model::factory('depositSettle')
							->with('order')
							->join('customer')->on('customer.id', '=', 'order.customer_id')
							->where('depositsettle.id', '=', $this->deposit_settle_id)
							->select('cust_code')
							->find();

		return $this->depositSettle->loaded() ? true : false;
	}
	
	public function processConfirmAction() {
		$result = $this->confirm();
		$this->processSearchAction();
		return $result;
	}
	
	public function processAddInit() {
		if ($this->initAddPage()) {
			if (!$this->isValidToAddDeposit()) {
				$this->errors[] = 'You can\'t add deposit to this order. All products in this order have already been generated invoice.';
				return false;
			}
			
			return true;
		} else {
			$this->order_id = '';
			$this->processSearchAction();
			return false;
		}
	}
	
	public function processAddAction() {
		$result = $this->save();
		$this->initAddPage();
		
		return $result;
	}
	
	private function confirm() {
		$this->errors = array();
		
		$db = Database::instance();
		$db->begin();
		
		try {
			$depositSettle = new Model_DepositSettle($this->deposit_settle_id);
			
			if (!$depositSettle->loaded()) {
				$this->errors[] = 'Record not found';
				return false;
			} else if ($depositSettle->is_confirm == Model_DepositSettle::CONFIRM_YES) {
				$this->errors[] = 'The deposit has already been confirmed.';
				return false;
			}
			
			// Updated order's confirmed deposit amount
			$order = new Model_Order($depositSettle->order_id);
			$order->confirm_deposit_amt += $depositSettle->settle_amt + $depositSettle->fee;
			$order->save();
			
			// Update status
			$depositSettle->is_confirm = Model_DepositSettle::CONFIRM_YES;
			$depositSettle->accountant_remark = $this->remark;
			$depositSettle->save();
			
		} catch (Exception $e) {
			$db->rollback();
			$this->errors[] = $e->getMessage();
			return false;
		}
		
		$db->commit();
		
		return true;
	}
	
	private function initAddPage() {
		$this->order = Model::factory('order')
						->with('customer')
						->where('order.id', '=', $this->order_id)
						->find();
	
		if (!$this->order->loaded()) {
			$this->errors[] = 'Order No['.$this->order_id.'] does not exist.';
			return false;
		}
	
		$this->depositSettleHistory = Model::factory('depositSettle')
		->where('order_id', '=', $this->order_id)
		->find_all();
	
		$this->totalInputDeposit = 0;
		foreach ($this->depositSettleHistory as $history) {
			$this->totalInputDeposit += $history->settle_amt + $history->fee;
		}

		// Get current rmb to jpy rate
		$rate = Model_Rate::getCurrentRate(Model_Rate::RATE_FROM_RMB, Model_Rate::RATE_TO_JPY);
		if ($rate->loaded()) {
			$this->rmb_to_jpy_rate = $rate->rate;
		} else {
			$this->rmb_to_jpy_rate = 0;
		}
		
		return true;
	}
	
	private function save() {
		$db = Database::instance();
		$db->begin();
	
		$this->errors = array();
		
		try {
			// Validation
			$this->depositSettle->check();
			
			/* $queryResult = DB::select(array(DB::expr('sum(settle_amt + fee)'), 'total'))
						->from('deposit_settle')
						->where('order_id', '=', $this->order_id)
						->execute();
				
			if ($this->depositSettle->settle_amt + $this->depositSettle->fee + $queryResult[0]['total'] > $order->deposit_amt) {
				$this->errors[] = '總入金大於 Deposit 金額';
				return false;
			} */

			if (!$this->isValidToAddDeposit()) {
				$this->errors[] = 'You can\'t add deposit to this order. All products in this order have already been generated invoice.';
				return;
			}
			
			// Save to DB
			$this->depositSettle->is_confirm = Model_DepositSettle::CONFIRM_YES;
			$this->depositSettle->created_by = Auth::instance()->get_user()->username;
			$this->depositSettle->save();
			
			$order = Model::factory('order')
					->with('customer')
					->where('order.id', '=', $this->order_id)
					->find();
			$order->confirm_deposit_amt += $this->depositSettle->settle_amt + $this->depositSettle->fee;
			$order->save();
				
			// Clear form
			$this->depositSettle = new Model_DepositSettle();
		} catch (ORM_Validation_Exception $e) {
			$db->rollback();
			foreach ($e->errors('sales') as $error) {
				$this->errors[] = $error;
			}
			return false;
		} catch (Exception $e) {
			$db->rollback();
			throw $e;
			$this->errors[] = $e->getMessage();
			return false;
		}
	
		$db->commit();
	
		return true;
	}
	
	private function isValidToAddDeposit() {
		$queryResult = DB::select(array(DB::expr('count(id)'), 'count'))
						->from('order_product')
						->where('order_id', '=', $this->order_id)
						->and_where_open()
						->or_where('jp_status', '<', Model_OrderProduct::STATUS_INVOICE_GENERATED)
						->or_where('factory_status', '<', Model_OrderProduct::STATUS_INVOICE_GENERATED)
						->and_where_close()
						->execute();
			
		return $queryResult[0]['count'] == 0 ? false : true;
	}
	
	private function export() {
		$cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
		$cacheSettings = array('memoryCacheSize' => '8MB');
		PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
	
		$objPHPExcel = new PHPExcel();
	
		$objPHPExcel->getProperties()->setCreator("S3")
		->setLastModifiedBy("S3")
		->setTitle("deposit_settlement");
	
		$sheet = $objPHPExcel->setActiveSheetIndex(0);
	
		// Header
		$i = 0;
		$sheet->setCellValueByColumnAndRow($i++, 1, __('label.order_no'))
				->setCellValueByColumnAndRow($i++, 1, __('label.cust_code'))
				->setCellValueByColumnAndRow($i++, 1, '入金')
				->setCellValueByColumnAndRow($i++, 1, '送金手數費')
				->setCellValueByColumnAndRow($i++, 1, '入金日期')
				->setCellValueByColumnAndRow($i++, 1, 'Remark')
				->setCellValueByColumnAndRow($i++, 1, '銀行名字')
				->setCellValueByColumnAndRow($i++, 1, 'Remark (入金管理)')
				->setCellValueByColumnAndRow($i++, 1, '輸入日期 ')
		;
	
		$rowNo = 1;
		$depositSettles = $this->getData(NULL, NULL);
		foreach($depositSettles as $depositSettle) {
			$i = 0;
			$rowNo++;
	
			$sheet->setCellValueByColumnAndRow($i++, $rowNo, $depositSettle->order_id)
					->setCellValueByColumnAndRow($i++, $rowNo, $depositSettle->cust_code)
					->setCellValueByColumnAndRow($i++, $rowNo, $depositSettle->settle_amt)
					->setCellValueByColumnAndRow($i++, $rowNo, $depositSettle->fee)
					->setCellValueByColumnAndRow($i++, $rowNo, $depositSettle->settle_date)
					->setCellValueByColumnAndRow($i++, $rowNo, $depositSettle->remark)
					->setCellValueByColumnAndRow($i++, $rowNo, $depositSettle->bank_name)
					->setCellValueByColumnAndRow($i++, $rowNo, $depositSettle->accountant_remark)
					->setCellValueByColumnAndRow($i++, $rowNo, date("Y-m-d", strtotime($depositSettle->create_date)))
			;
		}
	
		header("Content-type:application/vnd.ms-excel");
		header('Content-Disposition: attachment;filename="deposit_settlement.xls"');
		header('Cache-Control: max-age=0');
	
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
	}

// Overrided function
	public function getData($limit, $offset) {
		return $this->getCriteria()
					->select('cust_code')
					->order_by('create_date', 'desc')
					->limit($limit)
					->offset($offset)
					->find_all();
	}
	
	public function getCriteria() {
		$orm = Model::factory('depositSettle')
				->with('order')
				->join('customer')->on('customer.id', '=', 'order.customer_id');
	
		if ($this->order_id != '') {
			$orm->where('order_id', '=', $this->order_id);
		}
		
		if (!empty($this->customer_id)) {
			$orm->where('order.customer_id', '=', $this->customer_id);
		}
		
		if (!empty($this->settle_date_from)) {
			$orm->where('settle_date', '>=', $this->settle_date_from);
		}
			
		if (!empty($this->settle_date_to)) {
			$toDate = date('Y-m-d', strtotime($this->settle_date_to.' + 1 days'));
			$orm->where('settle_date', '<', $toDate);
		}
	
		return $orm;
	}
	
	public function getQueryString() {
		$query_string = '';
		
		if ($this->order_id != '') {
			$query_string .= '&order_id='.$this->order_id;
		}
		
		if (!empty($this->customer_id)) {
			$query_string .= '&customer_id='.$this->customer_id;
		}
		
		if (!empty($this->settle_date_from)) {
			$query_string .= '&settle_date_from='.$this->settle_date_from;
		}
			
		if (!empty($this->settle_date_to)) {
			$query_string .= '&settle_date_to='.$this->settle_date_to;
		}
		
		return $query_string;
	}
}