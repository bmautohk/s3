<?php
require_once APPPATH.'classes/vendor/PHPExcel.php';

class Model_Sales_OrderForm {
	public $action;
	public $order_id;
	public $order;
	public $orderProducts;
	public $tempProductMasters;
	public $readOnlyOrderProducts;
	public $isPrintQuotation;
	public $taxRate;
	
	//add 3 para from 1.5 main 20201016
	public $auctionValueNumber;
	public $kaitoPricingNumber;
	public $redLineNumber;
		
	public $errors;
	public $warnings;
	
	const ACTION_SAVE = 'save';
	const ACTION_SAVE_ONLY = 'save_only';
	const ACTION_GO_TO_KAITOSTAFF = 'submit_to_kaito_staff';
	const ACTION_CALCULATE_PROFIT = 'calculate_profit';
	
	public function __construct() {
		$this->order = new Model_Order();
		$this->isPrintQuotation = false;
	}

	public function populate($post) {
		if (!empty($post['order_id'])) {
			// Existing record
			$this->order_id = $post['order_id'];
			$this->order = new Model_Order($this->order_id);
		}
		
		$this->action = $post['action'];
		
		$this->order->values($post);

		$this->orderProducts = array();
		if (isset($post['orderProducts'])) {
			foreach ($post['orderProducts'] as $idx=>$value) {
				if (isset($value['id'])) {
					// Existing record
					$orderProduct = new Model_OrderProduct($value['id']);
				} else {
					// New record
					$orderProduct = new Model_OrderProduct();
				}
				
				$orderProduct->values($value);
				
				if (!empty($orderProduct->product_cd) || !empty($orderProduct->qty) || !empty($orderProduct->market_price)
						|| !empty($orderProduct->delivery_fee) || !empty($orderProduct->product_cd)) {
					$this->orderProducts[] = $orderProduct;
					
					if (isset($value['id'])) {
						// Existing record
						$tempProductMaster = Model::factory('tempProductMaster')->where('order_product_id', '=', $value['id'])->find();
					} else {
						// New record
						$tempProductMaster = new Model_TempProductMaster();
					}
					
					$tempProductMaster->no_jp = $orderProduct->product_cd;
					if (isset($post['tempProductMasters'][$idx])) {
						$tempProductMaster->values($post['tempProductMasters'][$idx]);
					}
					$this->tempProductMasters[] = $tempProductMaster;
				}
			}
		}
	}

	public function populateByImportFile($_POST, $_FILES) {
		$uplFile = $_FILES["uplFile"];
		// var_dump($uplFile);

		$this->order = new Model_Order();
		$this->order->order_type_id = $_POST['order_type_id'];
		$this->order->customer_id = $_POST['customer_id'];
		$today = new DateTime();
		$this->order->delivery_date = $today->format('Y-m-d');

		$objPHPExcel = new PHPExcel();

		$objReader = PHPExcel_IOFactory::createReader('Excel2007');
		$objReader->setReadDataOnly(true);
		$objPHPExcel = $objReader->load($uplFile['tmp_name']);

		$this->orderProducts = array();
		$worksheet = $objPHPExcel->getActiveSheet();
		$rowNo = 0;
		foreach ($worksheet->getRowIterator() as $row) {
			$rowNo = $row->getRowIndex();
			if ($rowNo == 1) {
				// Skip 1st row (i.e. header)
				continue;
			}

			$orderProduct = new Model_OrderProduct();
			$orderProduct->delivery_fee = 0;

			$orderProduct->product_cd = $worksheet->getCellByColumnAndRow(0, $rowNo)->getValue();
			$orderProduct->qty = $worksheet->getCellByColumnAndRow(1, $rowNo)->getValue();
			$orderProduct->qty = intval($orderProduct->qty);

			$orderProduct->delivery_fee = $worksheet->getCellByColumnAndRow(2, $rowNo)->getValue();

			$product = ORM::factory('pmProductMaster')
						->where('no_jp', '=', $orderProduct->product_cd)
						->find();
				
			if ($product->loaded()) {
				$orderProduct->market_price = $product->other;
			}
			
			if (!empty($orderProduct->product_cd) || !empty($orderProduct->qty) || !empty($orderProduct->market_price)
						|| !empty($orderProduct->delivery_fee) || !empty($orderProduct->product_cd)) {
				$this->orderProducts[] = $orderProduct;
				
				$tempProductMaster = new Model_TempProductMaster();
				$tempProductMaster->no_jp = $orderProduct->product_cd;
				$this->tempProductMasters[] = $tempProductMaster;
			}
		}
	}
	
	public function initAdd() {
		$this->initView();
		
		$this->order->order_type_id = Model_OrderType::ID_ORDER;
		
		// By default, list out 10 rows
		$this->orderProducts = array();
		for ($i = 0; $i < 10; $i++) {
			$this->orderProducts[$i] = new Model_OrderProduct();
			$this->tempProductMasters[$i] = new Model_TempProductMaster();
		}
		
		$this->readOnlyOrderProducts = array();
	}
	
	public function processOrderTypeChange() {
		$this->initView();	

		if (sizeOf($this->orderProducts) == 0) {
			$this->orderProducts[0] = new Model_OrderProduct();
			$this->tempProductMasters[0] = new Model_TempProductMaster();
		}
	}
	
	public function retrieve($order_id) {
		$this->order_id = $order_id;
		$this->order = new Model_Order($order_id);
		
		if ($this->order->status == Model_Order::STATUS_VOID) {
			return false;
		}
		
		$this->initView();
		
		// Check whether the order is created by the logged user
		$user = Auth::instance()->get_user();
		if ($user->isSales() && $this->order->created_by != $user->username) {
			return false;
		}
		
		$this->orderProducts = ORM::factory('orderProduct')
								->with('productMaster')
								->where('order_id', '=', $order_id)
								->where('jp_status', '=', Model_OrderProduct::STATUS_SALES)
								->where('factory_status', '=', Model_OrderProduct::STATUS_SALES)
								->find_all();
		
		if ($this->order->order_type_id == Model_OrderType::ID_TEMP) {
			// Temp Order
			foreach ($this->orderProducts as $idx=>$orderProduct) {
				$tempProductMaster = new Model_TempProductMaster();
				$tempProductMaster->kaito = $orderProduct->productMaster->kaito;
				$tempProductMaster->business_price = $orderProduct->productMaster->business_price;
				$tempProductMaster->other = $orderProduct->productMaster->other;
				$tempProductMaster->product_desc = $orderProduct->productMaster->product_desc;
				$tempProductMaster->made = $orderProduct->productMaster->made;
				$tempProductMaster->model = $orderProduct->productMaster->model;
				$tempProductMaster->model_no = $orderProduct->productMaster->model_no;
				$tempProductMaster->colour = $orderProduct->productMaster->colour;
				$tempProductMaster->colour_no = $orderProduct->productMaster->colour_no;
				$tempProductMaster->pcs = $orderProduct->productMaster->pcs;
				$tempProductMaster->material = $orderProduct->productMaster->material;
				$tempProductMaster->accessory_remark = $orderProduct->productMaster->accessory_remark;
				$tempProductMaster->year = $orderProduct->productMaster->year;
				$tempProductMaster->supplier = $orderProduct->productMaster->supplier;
				$this->tempProductMasters[$idx] = $tempProductMaster;
			}
		}
	
		$this->readOnlyOrderProducts = $this->getReadOnlyOrderProducts($order_id);
		
		return true;
	}
	
	public function processProfitCalculation() {
		$this->initView();
		
		if ($this->order->rmb_to_jpy_rate == 0) {
			$this->errors[] = 'Can\'t find rate RMB <-> JPY';
			return false;
		}
		
		if ($this->order->rmb_to_usd_rate == 0) {
			$this->errors[] = 'Can\'t find rate RMB <-> USD';
			return false;
		}
		
		// Get profit config
		$profitConfigs = ORM::factory('profitConfig')
						->find_all();
			
		$profitConfigs = $profitConfigs->as_array('code', 'value');
			
		// Get japan delivery fee
		$profit = new Model_ProfitConfig15(Model_ProfitConfig15::CODE_JP_DELIVERY_FEE);
		
		// Calculate profit
		foreach ($this->orderProducts as $idx=>$orderProduct) {
			// Retrieve product
			
			$kaito = 0;
			if (!$this->isTempOrderType()) {
				$product = Model_PMProductMaster::getProductByNoJp($orderProduct->product_cd);
				
				if (!$product->loaded()) {
					// Product not found
					continue;
				}
				
				$kaito = $product != NULL ? $product->kaito : 0;
				/* if ($this->order->order_type_id == Model_OrderType::ID_CLAIM || $this->order->order_type_id == Model_OrderType::ID_SAMPLE) {
					$kaito = 0;
				} */
			} else {
				$kaito = $this->tempProductMasters[$idx]->kaito;
			}
			
			$productProfit = $this->calculateProfit($orderProduct, $kaito, $this->order->order_type_id, $profitConfigs, $profit->value);
			$orderProduct->profit = $productProfit;
			$this->orderProducts[$idx] = $orderProduct;
		}
		
		return true;
	}
	
	public function processSaveAction($isPrintQuotation = true) {
		$this->initView();
		
		// Save to DB
		$result = $this->save();
		
		if ($result) {
			$this->isPrintQuotation = $isPrintQuotation;
		}
		
		// Retrieve readonly order_product for display
		if (!empty($this->order_id)) {
			$this->readOnlyOrderProducts = $this->getReadOnlyOrderProducts($this->order_id);
		}
		
		return $result;
	}
	
	public function processGoToKaitoStaff() {
		$result = $this->save();
		
		// Retrieve readonly order_product for display
		if (!empty($this->order_id)) {
			$this->readOnlyOrderProducts = $this->getReadOnlyOrderProducts($this->order_id);
		}
		
		if (!$result) {
			return false;
		}
		
		$result = $this->goToKaitoStaff();
		
		$this->retrieve($this->order_id);
		
		return $result;
	}

	public function processImportAction() {
		$defaultMethod = ORM::factory('deliveryMethod')
					->order_by('id')
					->find();
		$this->order->delivery_method_id = $defaultMethod->id;
		if ($this->processSaveAction(false)) {
			$this->goToKaitoStaff();
		}
	}
	
	public function isTempOrderType() {
		return $this->order->order_type_id == Model_OrderType::ID_TEMP ? true : false;
	}

	private function save() {
		$this->errors = array();
		$this->warnings = array();
		$isValid = true;
		
		// Check privilege
		$user = Auth::instance()->get_user();
		if ($user->isSales() && $this->order->loaded() && $this->order->created_by != $user->username) {
			// Old order but not created by this sales
			throw new HTTP_Exception_401();
		}
		
		// Get RMB <-> JPY rate
		$today = date('Y-m-d');
		$rmbJPYRate = Model_Rate::getCurrentRate('RMB', 'JPY');
		
		if ($rmbJPYRate == NULL) {
			$this->errors[] = 'Can\'t find rate RMB <-> JPY';
			$this->order->rmb_to_jpy_rate = 0;
			$isValid = false;
		} else {
			$this->order->rmb_to_jpy_rate = $rmbJPYRate->rate;
		}
		
		// Get RMB <-> USD rate
		$rmbUSDRate = Model_Rate::getCurrentRate('RMB', 'USD');
		
		if ($rmbUSDRate == NULL) {
			$this->errors[] = 'Can\'t find rate RMB <-> USD';
			$this->order->rmb_to_usd_rate = 0;
			$isValid = false;
		} else {
			$this->order->rmb_to_usd_rate = $rmbUSDRate->rate;
		}
		
		if (sizeOf($this->orderProducts) == 0) {
			$this->errors[] = 'No product is added.';
			$isValid = false;
		}
		
		if (!$isValid) {
			return false;
		}
		
		// Validate
		try {
			$this->order->check();
		} catch (ORM_Validation_Exception $e) {
			$isValid = false;
			foreach ($e->errors('sales') as $error) {
				$this->errors[] = $error;
			}
		}
		
		if ($this->order->order_type_id == Model_OrderType::ID_ORDER
				|| $this->order->order_type_id == Model_OrderType::ID_RETAIL
				|| $this->order->order_type_id == Model_OrderType::ID_MONOPOLY
				|| $this->order->order_type_id == Model_OrderType::ID_MONOPOLY_RETAIL) {
			$isCheckBusinessPrcie = true;
		} else {
			$isCheckBusinessPrcie = false;
		}
		
		$isTempOrderType = $this->isTempOrderType();

		$rowProducts = array();
		$rowNo = 1;
		$productCdArray = array();
		foreach ($this->orderProducts as $idx=>$orderProduct) {
			try {
				$orderProduct->check();
			} catch (ORM_Validation_Exception $e) {
				$isValid = false;
				foreach ($e->errors('sales') as $error) {
					$this->errors[] = 'Row '.$rowNo.': '.$error;
				}
			}
			
			if (!$isTempOrderType) {
				// Check whether the product exists
				$product = ORM::factory('pmProductMaster')
						->where('no_jp', '=', $orderProduct->product_cd)
						->find();
				
				if (!$product->loaded()) {
					// Product not found
					$isValid = false;
					$this->errors[] = 'Row '.$rowNo.': Product ['.$orderProduct->product_cd.'] does not exist.';
					$rowProducts[$idx] = NULL;
					continue;
				}
				
				$rowProducts[$idx] = $product;
				
				// Update TEMP_PRODUCT_MASTER
				$pmProduct = $this->tempProductMasters[$idx];
				$pmProduct->no_jp = $orderProduct->product_cd;
				$pmProduct->made = $product->made;
				$pmProduct->model = $product->model;
				$pmProduct->model_no = $product->model_no;
				$pmProduct->year = $product->year;
				$pmProduct->material = $product->material;
				$pmProduct->product_desc = $product->product_desc;
				$pmProduct->pcs = $product->pcs;
				$pmProduct->colour = $product->colour;
				$pmProduct->colour_no = $product->colour_no;
				$pmProduct->kaito = $product->kaito;
				$pmProduct->supplier = $product->supplier;
				$pmProduct->business_price = $product->business_price;
				$pmProduct->other = $product->other;
				$pmProduct->accessory_remark = $product->accessory_remark;
				$pmProduct->status = Model_TempProductMaster::STATUS_ACTIVE;
				
				//add two more fields  20201016
				$pmProduct->kaito_price = $product->kaito_price;
				$pmProduct->auction_price = $product->auction_price;
				
				$this->tempProductMasters[$idx] = $pmProduct;
				
				// Check business price
				$customer = new Model_Customer($this->order->customer_id);
				if ( ($customer->is_kaito == 'N')
					&& ($product->other == NULL || $product->other == 0) ) {
					$isValid = false;
					$this->errors[] = 'Row '.$rowNo.': '.$orderProduct->product_cd.' 批发价 = 0';
					continue;
				}
				
				if ($isCheckBusinessPrcie && $product->other != NULL && $orderProduct->market_price < ($product->other*$this->redLineNumber)) {
					// sale price < 批发价
					$isValid = false;
					$this->errors[] = 'Row '.$rowNo.': 売値 ['.$orderProduct->market_price.'] is less than 批发价 ['.($product->other*$this->redLineNumber).'].';
					continue;
				}
			} else {
				// Temp order
				$product = new Model_TempProductMaster();
				$product->no_jp = $orderProduct->product_cd;
				$product->kaito = $this->tempProductMasters[$idx]->kaito;
				
				// Product can't exist in PM
				/* $pmProduct = new Model_PMProductMaster($orderProduct->product_cd);
				if ($pmProduct->loaded()) {
					$isValid = false;
					$this->errors[] = 'Row '.$rowNo.': Partno ['.$orderProduct->product_cd.'] is PM product.';
					continue;
				} */
				
				// Check if the product code is used by other order
				/* $orm = ORM::factory('tempProductMaster')
					->join('order_product')->on('order_product.id', '=', 'tempproductmaster.order_product_id')
					->where('no_jp', '=', $orderProduct->product_cd)
					->where('tempproductmaster.status', '=', Model_TempProductMaster::STATUS_ACTIVE);
				
				if ($this->order->id != NULL) {
					$orm->where('order_id', '<>', $this->order->id);
				}
				
				$p = $orm->find();
				
				if ($p->loaded()) {
					$isValid = false;
					$this->errors[] = 'Row '.$rowNo.': Partno ['.$orderProduct->product_cd.'] has already been used by other order.';
					continue;
				} */
				
				$productCd = strtoupper($orderProduct->product_cd);
				if (array_key_exists($productCd, $productCdArray)) {
					$isValid = false;
					$this->errors[] = 'Row '.$rowNo.': Partno ['.$orderProduct->product_cd.'] is duplicate with row['.$productCdArray[$productCd].'].';
					continue;
				} else {
					$productCdArray[$productCd] = $rowNo;
				}
				
				$rowProducts[$idx] = $product;
			}
			
			$rowNo++;
		}
		
		if (!$isValid) {
			return false;
		}
		
		$isNewOrder = $this->order->loaded() ? false : true;
		if ($isNewOrder) {
			$this->order->created_by = Auth::instance()->get_user()->username;
			$this->order->create_date = DB::expr('current_timestamp');
			$this->order->order_date = DB::expr('current_timestamp');
		}
		
		// Get profit config
		$profitConfigs = ORM::factory('profitConfig')
						->find_all();
		
		$profitConfigs = $profitConfigs->as_array('code', 'value');
			
		// Get japan delivery fee
		$profit = new Model_ProfitConfig15(Model_ProfitConfig15::CODE_JP_DELIVERY_FEE);
		
		// Calculate profit & assign required information
		foreach ($this->orderProducts as $idx=>$orderProduct) {
			$orderProduct->product_cd = $rowProducts[$idx]->no_jp;
			$this->orderProducts[$idx] = $orderProduct;
			
			$orderProduct->kaito = $rowProducts[$idx]->kaito;
			if ($this->order->order_type_id == Model_OrderType::ID_CLAIM || $this->order->order_type_id == Model_OrderType::ID_SAMPLE) {
				$orderProduct->kaito = 0;
			}
			
			// Calcualte profit
			$productProfit = $this->calculateProfit($orderProduct, $orderProduct->kaito, $this->order->order_type_id, $profitConfigs, $profit->value, $rmbJPYRate->rate);
			$orderProduct->profit = $productProfit;
			
			if ($productProfit < 0) {
				if ($this->order->order_type_id != Model_OrderType::ID_KAITO
						&& $this->order->order_type_id != Model_OrderType::ID_CLAIM
						&& $this->order->order_type_id != Model_OrderType::ID_SAMPLE
						&& $this->order->order_type_id != Model_OrderType::ID_STOCK) {
					$isValid = false;
				}
			}
		}
		
		if (!$isValid) {
			$this->errors[] = 'proft小0時order不成立';
			return false;
		}
		
		// Data process
		$this->order->delivery_address1 = trim($this->order->delivery_address1);
		$this->order->delivery_address2 = trim($this->order->delivery_address2);
		$this->order->delivery_address3 = trim($this->order->delivery_address3);
		
		// Determinie whether it is kaito order
		$this->order->is_kaito = Model_Order::KAITO_NO;
		if ($this->order->order_type_id == Model_OrderType::ID_KAITO) {
			$this->order->is_kaito = Model_Order::KAITO_YES;
		} else {
			$customer = new Model_Customer($this->order->customer_id);
			if ($customer->is_kaito == Model_Customer::KAITO_YES) {
				$this->order->is_kaito = Model_Order::KAITO_YES;
			}
		}
		
		/**
		 * Save to DB
		 */
		$db = Database::instance();
		$db->begin();
			
		try {
			$this->order = $this->order->save();
			
			/* DB::delete(ORM::factory('orderProduct')->table_name())
			->where('order_id', '=', $this->order->id)
			->where('jp_status', '=', Model_OrderProduct::STATUS_SALES)
			->where('factory_status', '=', Model_OrderProduct::STATUS_SALES)
			->execute(); */
			
			// Get existing order_product_id
			$orderProductIds = $this->getExistingOrderProductId($this->order->id);

			$orderTypeId = $this->order->order_type_id;
			foreach ($this->orderProducts as $idx=>$orderProduct) {
				$orderProduct->order_id = $this->order->id;
				$orderProduct->save();
				
				$tempProductMaster = $this->tempProductMasters[$idx];
				$tempProductMaster->order_product_id = $orderProduct->id;
				$tempProductMaster->save();
				
				unset($orderProductIds[$orderProduct->id]);
			}

			// Delete removed record
			foreach ($orderProductIds as $orderProductId) {
				/* $tempProduct = ORM::factory('tempProductMaster')->where('order_product_id', '=', $orderProductId)->find();
				if ($tempProduct->loaded()) {
					$tempProduct->delete();
				} */
				
				$orderProduct = new Model_OrderProduct($orderProductId);
				//$orderProduct->delete();
				$orderProduct->jp_status = Model_OrderProduct::STATUS_DELETE;
				$orderProduct->factory_status = Model_OrderProduct::STATUS_DELETE;
				$orderProduct->save();
			}
			
			// Update customer's last order date
			if ($isNewOrder) {
				$customer = ORM::factory('customer')->where('id', '=', $this->order->customer_id)->find();
				$customer->last_order_date = date('Y-m-d H:i:s');
				$customer->save();
			}
			
			// Upload image
			if (isset($_FILES['picture1']) && Upload::not_empty($_FILES['picture1'])) {
				$fileName = $this->saveImage($_FILES['picture1'], $this->order->id, '1');
				if ($fileName) {
					$this->order->picture1 = $fileName;
				} else {
					$this->warnings[] = 'Fail to upload picture 1';
				}
			}
			
			if (isset($_FILES['picture2']) && Upload::not_empty($_FILES['picture2'])) {
				$fileName = $this->saveImage($_FILES['picture2'], $this->order->id, '2');
				if ($fileName) {
					$this->order->picture2 = $fileName;
				} else {
					$this->warnings[] = 'Fail to upload picture 2';
				}
			}
			
			if (isset($_FILES['picture3']) && Upload::not_empty($_FILES['picture3'])) {
				$fileName = $this->saveImage($_FILES['picture3'], $this->order->id, '3');
				if ($fileName) {
					$this->order->picture3 = $fileName;
				} else {
					$this->warnings[] = 'Fail to upload picture 3';
				}
			}
			
			$this->order->save();
		} catch (Exception $e) {
			$db->rollback();
			$this->errors[] = $e->getMessage();
			//return false;
			throw $e;
		}
		
		$db->commit();
		
		return true;
	}
	
	private function goToKaitoStaff() {
		$this->errors = array();
		
		$db = Database::instance();
		$db->begin();
		
		try {
			// Check deposit
			if ($this->order->deposit_amt > $this->order->confirm_deposit_amt) {
				$this->errors[] = 'The order can\'t be transfered to 大步哥. Deposit has not been confirmed by 入金管理.';
				return false;
			}
			
			DB::update(ORM::factory('orderProduct')->table_name())
				->set(array('jp_status' => Model_OrderProduct::STATUS_KAITOSTAFF))
				->set(array('factory_status' => Model_OrderProduct::STATUS_KAITOSTAFF))
				->set(array('is_reject' => Model_OrderProduct::IS_REJECT_NO))
				->where('order_id', '=', $this->order->id)
				->where('jp_status', '=', Model_OrderProduct::STATUS_SALES)
				->where('factory_status', '=', Model_OrderProduct::STATUS_SALES)
				->execute();
			
		} catch (Exception $e) {
			$db->rollback();
			$this->errors[] = $e->getMessage();
			return false;
		}
		
		$db->commit();
		
		return true;
	}
	
	public function isSaveEnable() {
		if (empty($this->order->id)) {
			return true;
		}
		
		if (sizeOf($this->orderProducts) > 0) {
			return true;
		}

		return false;
	}
	
	private function initView() {
		if (empty($this->order->id)) {
			$this->order->order_date = Date('Y-m-d');
			
			$this->order->rmb_to_jpy_rate = 0;
			$this->order->rmb_to_usd_rate = 0;
				
			// Get rate
			$rates = $this->getCurrentRates();
			foreach ($rates as $rate) {
				if ($rate->rate_to == 'JPY') {
					$this->order->rmb_to_jpy_rate = $rate->rate;
				} else if ($rate->rate_to == 'USD') {
					$this->order->rmb_to_usd_rate = $rate->rate;
				}
			}
		}
		
		// Find tax
		$this->taxRate = Model_ProfitConfig15::getTaxRate();
		
		//Find new 3 para 20201016
		$this->auctionValueNumber = Model_ProfitConfig15::getAuctionValueNumber();
		$this->kaitoPricingNumber = Model_ProfitConfig15::getKaitoPricingNumber();
		$this->redLineNumber = Model_ProfitConfig15::getRedLineNumber();
	}
	
	private function getExistingOrderProductId($order_id) {
		$result = DB::select('id')
					->from('order_product')
					->where('order_id', '=', $order_id)
					->where('jp_status', '=', Model_OrderProduct::STATUS_SALES)
					->where('factory_status', '=', Model_OrderProduct::STATUS_SALES)
					->execute();
		
		$orderProductIds = array();
		foreach ($result as $column) {
			$orderProductIds[$column['id']] = $column['id'];
		}
		
		return $orderProductIds;
	}
	
	private function getCurrentRates() {
		// Rate
		$today = date('Y-m-d');
		$rates = ORM::factory('rate')
				->where('date_from', '<=', $today)
				->where('date_to', '>=', $today)
				->find_all();
		return $rates;
	}
	
	private function getReadOnlyOrderProducts($order_id) {
		return ORM::factory('orderProduct')
				->with('productMaster')
				->where('order_id', '=', $order_id)
				->and_where_open()
				->or_where('jp_status', '>', Model_OrderProduct::STATUS_SALES)
				->or_where('factory_status', '>', Model_OrderProduct::STATUS_SALES)
				->and_where_close()
				->find_all();
	}
	
	private function calculateProfit($orderProduct, $kaito, $orderTypeId, $profitConfigs, $japanDeliveryFee) {
		if ($orderTypeId == Model_OrderType::ID_ORDER) {
			if ($orderProduct->is_tax == 1) {
				// 税 = 込
				if ($orderProduct->is_shipping_fee == 1) {
					// 輸入経費 = 込
					$A = $profitConfigs['A'];
					$B = $profitConfigs['E'];
				} else {
					// 輸入経費 = 拔
					$A = $profitConfigs['B'];
					$B = $profitConfigs['F'];
				}
			} else {
				// 税 = 拔
				if ($orderProduct->is_shipping_fee == 1) {
					// 輸入経費 = 込
					$A = $profitConfigs['C'];
					$B = $profitConfigs['G'];
				} else {
					// 輸入経費 = 拔
					$A = $profitConfigs['D'];
					$B = $profitConfigs['H'];
				}
			}
		} else if ($orderTypeId == Model_OrderType::ID_RETAIL || $orderTypeId == Model_OrderType::ID_TEMP) {
			if ($orderProduct->is_tax == 1) {
				// 税 = 込
			if ($orderProduct->is_shipping_fee == 1) {
					// 輸入経費 = 込
					$A = $profitConfigs['A'];
					$B = $profitConfigs['I'];
				} else {
					// 輸入経費 = 拔
					$A = $profitConfigs['B'];
					$B = $profitConfigs['J'];
				}
			} else {
				// 税 = 拔
			if ($orderProduct->is_shipping_fee == 1) {
					// 輸入経費 = 込
					$A = $profitConfigs['C'];
					$B = $profitConfigs['K'];
				} else {
					// 輸入経費 = 拔
					$A = $profitConfigs['D'];
					$B = $profitConfigs['L'];
				}
			}
		} else if ($orderTypeId == Model_OrderType::ID_MONOPOLY) {
			if ($orderProduct->is_tax == 1) {
				// 税 = 込
			if ($orderProduct->is_shipping_fee == 1) {
					// 輸入経費 = 込
					$A = $profitConfigs['A'];
					$B = $profitConfigs['M'];
				} else {
					// 輸入経費 = 拔
					$A = $profitConfigs['B'];
					$B = $profitConfigs['N'];
				}
			} else {
				// 税 = 拔
			if ($orderProduct->is_shipping_fee == 1) {
					// 輸入経費 = 込
					$A = $profitConfigs['C'];
					$B = $profitConfigs['O'];
				} else {
					// 輸入経費 = 拔
					$A = $profitConfigs['D'];
					$B = $profitConfigs['P'];
				}
			}
		} else if ($orderTypeId == Model_OrderType::ID_MONOPOLY_RETAIL) {
			if ($orderProduct->is_tax == 1) {
				// 税 = 込
			if ($orderProduct->is_shipping_fee == 1) {
					// 輸入経費 = 込
					$A = $profitConfigs['A'];
					$B = $profitConfigs['Q'];
				} else {
					// 輸入経費 = 拔
					$A = $profitConfigs['B'];
					$B = $profitConfigs['R'];
				}
			} else {
				// 税 = 拔
			if ($orderProduct->is_shipping_fee == 1) {
					// 輸入経費 = 込
					$A = $profitConfigs['C'];
					$B = $profitConfigs['S'];
				} else {
					// 輸入経費 = 拔
					$A = $profitConfigs['D'];
					$B = $profitConfigs['T'];
				}
			}
		} else {
			if ($orderProduct->is_tax == 1) {
				// 税 = 込
			if ($orderProduct->is_shipping_fee == 1) {
					// 輸入経費 = 込
					$A = $profitConfigs['A'];
					$B = $profitConfigs['U'];
				} else {
					// 輸入経費 = 拔
					$A = $profitConfigs['B'];
					$B = $profitConfigs['V'];
				}
			} else {
				// 税 = 拔
			if ($orderProduct->is_shipping_fee == 1) {
					// 輸入経費 = 込
					$A = $profitConfigs['C'];
					$B = $profitConfigs['W'];
				} else {
					// 輸入経費 = 拔
					$A = $profitConfigs['D'];
					$B = $profitConfigs['X'];
				}
			}
		}
		
		$profit = (($orderProduct->market_price + $japanDeliveryFee) * $A - $kaito *  $B) * $orderProduct->qty;
		
		return $profit;
	}
	
	private function saveImage($image, $orderId, $fileNamePrefix) {
		if (
			! Upload::valid($image) OR
			! Upload::type($image, array('jpg', 'jpeg', 'png', 'gif'))) {
			
			$this->warnings[] = 'Only support picture type jpg/png/gif.';
			return false;
		}
		
		$directory = ORDER_IMAGE_UPLOAD_DIRECTORY.$orderId.'/';
		
		try {
		
			// Create directory
			if (!file_exists($directory)) {
				mkdir($directory, 0777, true);
			}
			
			$ext = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));
			$fileName = $fileNamePrefix.'.'.$ext;
		
			if ($file = Upload::save($image, $fileName, $directory)) {
				Image::factory($file)->resize(200, 200, Image::AUTO)->save($directory.'s_'.$fileName);
				return $fileName;
			}
		} catch (Exception $e) {
			$this->warnings[] = $e->getMessage();
			return false;
		}
	
		return false;
	}
}