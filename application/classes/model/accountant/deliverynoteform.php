<?php
class Model_Accountant_DeliveryNoteForm extends Model_PageForm {
	
	public $action;
	
	/* User Input */
	public $customer_id;
	public $print_date;
	public $delivery_method_id;
	public $delivery_method;
	public $order_id_for_delivery_address;
	public $s1_client_name;
	public $s1_tel;
	public $s1_postal_code;
	public $s1_remark;
	public $delivery_note_remarks;
	public $delivery_note_order_return_remarks;
	
	public $delivery_address_options;
	/* public $s1_client_name_options;
	public $tel_options;
	public $postal_code_options; */
	
	public $containers;
	public $pendingOrderReturns;
	public $deliveryNotes;
	public $deliveryNoteDetails;
	
	/* For delivery note generation */
	private $selectedContainerIds;
	private $selectedOrderReturnIds;
	
	/* For return delivery note */
	public $return_delivery_note_no;
	private $delivery_note_id;

	public $errors;
	
	public $page_url = 'accountant/delivery_note';
	
	public function populate($post) {
		parent::populate($post);
		
		$this->action = isset($post['action']) ? $post['action'] : NULL;
		$this->customer_id = isset($post['customer_id']) ? $post['customer_id'] : NULL;
		$this->print_date = isset($post['print_date']) ? $post['print_date'] : NULL;
		$this->delivery_method_id = isset($post['delivery_method_id']) ? $post['delivery_method_id'] : NULL;
		$this->delivery_method = isset($post['delivery_method']) ? $post['delivery_method'] : NULL;
		$this->order_id_for_delivery_address = isset($post['order_id_for_delivery_address']) ? $post['order_id_for_delivery_address'] : NULL;
		$this->s1_remark = isset($post['s1_remark']) ? $post['s1_remark'] : NULL;
		$this->delivery_note_id = isset($post['delivery_note_id']) ? $post['delivery_note_id'] : NULL;
		/* $this->s1_client_name = isset($post['s1_client_name']) ? $post['s1_client_name'] : NULL;
		$this->s1_tel = isset($post['s1_tel']) ? $post['s1_tel'] : NULL;
		$this->s1_postal_code = isset($post['s1_postal_code']) ? $post['s1_postal_code'] : NULL; */
		
		$this->selectedContainerIds = array();
		if (isset($post['container_id'])) {
			$this->selectedContainerIds = $post['container_id'];
		}
		
		$this->selectedOrderReturnIds = array();
		if (isset($post['order_return_id'])) {
			$this->selectedOrderReturnIds = $post['order_return_id'];
		}
		
		if (isset($post['delivery_note_remarks'])) {
			$this->delivery_note_remarks = $post['delivery_note_remarks'];
		}
		
		if (isset($post['delivery_note_order_return_remarks'])) {
			$this->delivery_note_order_return_remarks = $post['delivery_note_order_return_remarks'];
		}
	}
	
	public function processSearchAction() {
		if ($this->customer_id == NULL) {
			$this->customer_id = 0;
			/* $customer = ORM::factory('customer')->order_by('cust_code')->find();;
			$this->customer_id = $customer->id; */
		}
		
		if ($this->print_date == NULL) {
			$this->print_date = date('Y-m-d');
		}
		
		// Find delivery_address
		if ($this->customer_id != 0) {
			$orders = ORM::factory('order')
					->join('order_product')->on('order_product.order_id', '=', 'order.id')
					->join('container')->on('container.order_product_id', '=', 'order_product.id')
					->where('container.status', '=', Model_Container::STATUS_READY_FOR_DELIVERY_NOTE)
					->where('order.customer_id', '=', $this->customer_id)
					->distinct(true)
					->find_all();
			
			// Delivery address options
			$this->delivery_address_options = array();
			$this->delivery_address_options[''] = '';
			foreach($orders as $order) {
				if ($order->delivery_address1 != '' || $order->delivery_address2 != '' || $order->delivery_address3 != '') {
					$this->delivery_address_options[$order->id] = $order->delivery_address1.' '.$order->delivery_address2.' '.$order->delivery_address3;
				}
			}
		}
		
		$this->deliveryNotes = $this->search();
		$this->deliveryNoteDetails = array();
		foreach ($this->deliveryNotes as $deliveryNote) {
			$this->deliveryNoteDetails[$deliveryNote->id] = $this->getDeliveryNoteDetail($deliveryNote->id);
		}
		
		// Client name options
		/* $result = DB::select('s1_client_name')
				->distinct(true)
				->from('order')
				->join('order_product')->on('order_product.order_id', '=', 'order.id')
				->join('container')->on('container.order_product_id', '=', 'order_product.id')
				->where('container.status', '=', Model_Container::STATUS_READY_FOR_DELIVERY_NOTE)
				->where('order.customer_id', '=', $this->customer_id)
				->where('s1_client_name', '<>', '')
				->order_by(array('s1_client_name'))
				->execute();
		
		$this->s1_client_name_options[] = '';
		foreach ($result as $obj) {
			$this->s1_client_name_options[$obj['s1_client_name']] = $obj['s1_client_name'];
		}
		
		// Tel
		$result = DB::select('tel')
				->distinct(true)
				->from('order')
				->join('order_product')->on('order_product.order_id', '=', 'order.id')
				->join('container')->on('container.order_product_id', '=', 'order_product.id')
				->where('container.status', '=', Model_Container::STATUS_READY_FOR_DELIVERY_NOTE)
				->where('order.customer_id', '=', $this->customer_id)
				->where('tel', '<>', '')
				->order_by(array('tel'))
				->execute();
		
		$this->tel_options[] = '';
		foreach ($result as $obj) {
			$this->tel_options[$obj['tel']] = $obj['tel'];
		}
		
		// Postal code
		$result = DB::select('postal_code')
					->distinct(true)
					->from('order')
					->join('order_product')->on('order_product.order_id', '=', 'order.id')
					->join('container')->on('container.order_product_id', '=', 'order_product.id')
					->where('container.status', '=', Model_Container::STATUS_READY_FOR_DELIVERY_NOTE)
					->where('order.customer_id', '=', $this->customer_id)
					->where('tel', '<>', '')
					->order_by(array('postal_code'))
					->execute();
		
		$this->postal_code_options[] = '';
		foreach ($result as $obj) {
			$this->postal_code_options[$obj['postal_code']] = $obj['postal_code'];
		} */
		
		$this->getPendingContainer();
	}
	
	public function processReturnAction() {
		$result = $this->returnDeliveryNote();
		
		$this->processSearchAction();
		
		return $result;
	}
	
	public function processBackToPrevStepAction() {
		$result = $this->backToPrevStep();
		
		$this->processSearchAction();
		
		return $result;
	}
	
	public function processScanAction() {
		$result = false;
		
		if ($this->customer_id == 0) {
			// Generate delivery note of all customers
			/* $errors = array();
			$customers = ORM::factory('customer')->find_all();
			foreach ($customers as $customer) {
				try {
					$result = $this->create_delivery_note($customer->id);
					
					if (!$result) {
						$errors[] = 'Error when generating 納品書 for customer ['.$customer->cust_code.']';
						$errors = array_merge($errors, $this->errors);
						$errors[] = '';
					}
				} catch (Exception $e) {
					if ($e->getMessage() != 'NO_ITEM') {
						$this->errors[] = $e->getMessage();
					}
				}	
			}
			
			$this->errors = $errors;
			$result = sizeOf($this->errors) > 0 ? false : true; */
		} else {
			// Generate delivery note of specfic customer
			try {
				$result = $this->create_delivery_note($this->customer_id);
			} catch (Exception $e) {
				if ($e->getMessage() == 'NO_ITEM') {
					$this->errors[] = 'No item is available for generating 納品書';
					
				} else {
					$this->errors[] = $e->getMessage();
				}
			}
		}
		
		if ($result) {
			// Clear form
			$this->s1_remark = NULL;
			$this->order_id_for_delivery_address = NULL;
		}
		
		$this->processSearchAction();
		
		return $result;
	}
	
/* 	private function hasItemForDeliveryNote() {
		$query = DB::select(array(DB::expr('COUNT(distinct container.id)'), 'count'))
				->from('container')
				->join('order_product')->on('order_product.id', '=', 'container.order_product_id')
				->join('order')->on('order.id', '=', 'order_product.order_id')
				->where('order.customer_id', '=', $customer_id)
				->where('container.status', '=', Model_Container::STATUS_READY_FOR_DELIVERY_NOTE)
				->where('container.source', 'in', array(Model_Container::SOURCE_FACTORY, Model_Container::SOURCE_JP));
		
		$result = $query->execute();
		
		
		
		return $result[0]['count'] > 0 ? true : false;
	}
 */
	private function returnDeliveryNote() {
		$db = Database::instance();
		$db->begin();
		
		$this->errors = array();
		try {
			$deliveryNote = new Model_DeliveryNote($this->delivery_note_id);
			if (!$deliveryNote->loaded()) {
				$this->errors[] = '納品書 does not exist.';
				return;
			}
				
			if ($deliveryNote->invoice_id != NULL) {
				$this->errors[] = '請求書 has already generated.';
				return;
			}
			
			$this->return_delivery_note_no = $deliveryNote->delivery_note_no;
			
			// update container set status = 20 where id in (select container_id from delivery_note_detail where delivery_note_id = @vDeliveryNoteId and source <> 'ORDER_RETURN');
			$sub = DB::select('container_id')
			->from('delivery_note_detail')
			->where('delivery_note_id', '=', $this->delivery_note_id)
			->where('source', '<>', Model_DeliveryNoteDetail::SOURCE_ORDER_RETURN);
				
			DB::update('container')
			->set(array('status'=>Model_Container::STATUS_READY_FOR_DELIVERY_NOTE))
			->where('id', 'in', $sub)
			->execute();
				
			// update order_return set status = 20 where id in (select reference_id from delivery_note_detail where delivery_note_id = @vDeliveryNoteId and source = 'ORDER_RETURN');
			$sub = DB::select('reference_id')
			->from('delivery_note_detail')
			->where('delivery_note_id', '=', $this->delivery_note_id)
			->where('source', '=', Model_DeliveryNoteDetail::SOURCE_ORDER_RETURN);
		
			DB::update('order_return')
			->set(array('status'=>Model_Container::STATUS_READY_FOR_DELIVERY_NOTE))
			->where('id', 'in', $sub)
			->execute();
				
			DB::delete('delivery_note_detail')
			->where('delivery_note_id', '=', $this->delivery_note_id)
			->execute();
				
			$deliveryNote->delete();
		} catch (Exception $e) {
			$db->rollback();
			$this->errors[] = $e->getMessage();
			return false;
		}
		
		$db->commit();
		
		return true;
	}
	
	private function backToPrevStep() {
		$this->errors = array();
		
		/* if (sizeOf($this->selectedOrderReturnIds) > 0) {
			$this->errors[] = '返品 can\'t be returned.';
			return false;
		} */
		
		$db = Database::instance();
		$db->begin();
		
		try {
			// Return order's products
			foreach ($this->selectedContainerIds as $containerId) {
				$container = new Model_Container($containerId);
				
				if ($container->source == Model_Container::SOURCE_FACTORY) {
					// Back to warehouse
					$container->status = Model_Container::STATUS_INIT;
					$container->save();
					
					// Refresh the flag "has_container_to_accountant"
					$orderProduct = new Model_OrderProduct($container->order_product_id);
					$orderProduct->refreshHasContainerToAccountant();
					$orderProduct->save();
				} else if ($container->source == Model_Container::SOURCE_JP) {
					// Back to auditor
					$orderProduct = new Model_OrderProduct($container->order_product_id);
					$orderProduct->is_reject = Model_OrderProduct::IS_REJECT_YES;
					$orderProduct->jp_status = Model_OrderProduct::STATUS_AUDITOR;
					$orderProduct->save();
					
					$container->delete();
				}
			}
			
			// Return returned product
			foreach ($this->selectedOrderReturnIds as $orderReturnId) {
				$orderReturn = new Model_OrderReturn($orderReturnId);
				$orderReturn->status = Model_OrderReturn::STATUS_INIT;
				$orderReturn->save();
			}
		} catch (Exception $e) {
			$db->rollback();
			$this->errors[] = $e->getMessage();
			return false;
		}
		
		$db->commit();
		
		return true;
	}
	
	private function create_delivery_note($customer_id) {
		$db = Database::instance();
		$db->begin();
		
		$db2 = Database::instance('s1');
		$db2->begin();
		
		$this->errors = array();
		
		/**
		 * Create delivery note
		 * (Currency = RMB)
		 */
		// Find containers by customer
		$containers = array();
		if (sizeOf($this->selectedContainerIds) > 0) {
			$containers = ORM::factory('container')
						->join('order_product')->on('order_product.id', '=', 'container.order_product_id')
						->join('order')->on('order.id', '=', 'order_product.order_id')
						->where('order.customer_id', '=', $customer_id)
						->where('container.status', '=', Model_Container::STATUS_READY_FOR_DELIVERY_NOTE)
						->where('container.source', 'in', array(Model_Container::SOURCE_FACTORY, Model_Container::SOURCE_JP))
						
						->where('container.id', 'in', $this->selectedContainerIds)
						
						->select('order_product.product_cd')
						->select('order_product.market_price')
						->select('order_product.is_tax')
						->select('order_product.order_id')
						->select('order_product.delivery_fee')
						->select('order.delivery_method')
						->order_by('order.id')
						->find_all();
			
			/*$isExist = false;
			foreach ($containers as $container) {
				if ($container->order_id == $this->order_id_for_delivery_address) {
					$isExist = true;
					break;
				}
			}
			
			if (!$isExist) {
				$this->errors[] = 'Selected items do not contain the selected 送貨地址.';
				return false;
			}*/
		}
						
		// Find gift by customer
		/* $giftContainers = ORM::factory('container')
					->join('gift')->on('gift.id', '=', 'container.gift_id')
					->where('gift.customer_id', '=', $customer_id)
					->where('container.status', '=', Model_Container::STATUS_READY_FOR_DELIVERY_NOTE)
					->where('container.source', '=', Model_Container::SOURCE_GIFT)
					->select('gift.product_cd')
					->select('gift.cost')
					->find_all(); */
		
		// Find order return by customer
		$orderReturns = array();
		if (sizeOf($this->selectedOrderReturnIds)) {
			$orderReturns = ORM::factory('orderReturn')
							->where('customer_id', '=', $customer_id)
							->where('status', '=', Model_OrderReturn::STATUS_READY_FOR_DELIVERY_NOTE)
							
							->where('orderreturn.id', 'in', $this->selectedOrderReturnIds)
							
							->find_all();
		}
						
		//if (sizeof($containers) == 0 && sizeOf($giftContainers) == 0 && sizeOf($orderReturns) == 0) {
		if (sizeof($containers) == 0 && sizeOf($orderReturns) == 0) {
			//$this->errors[] = 'No item is available for generating 納品書';
			throw new Exception('NO_ITEM');
		}

		try {
			// Find rate
			$rmbJPYRate = Model_Rate::getCurrentRate(Model_Rate::RATE_FROM_RMB, Model_Rate::RATE_TO_JPY);
			if ($rmbJPYRate == NULL) {
				$this->errors[] = 'Can\'t find rate RMB <-> JPY';
				return false;
			}
			
			$rmbUSDRate = Model_Rate::getCurrentRate(Model_Rate::RATE_FROM_RMB, Model_Rate::RATE_TO_USD);
			if ($rmbUSDRate == NULL) {
				$this->errors[] = 'Can\'t find rate RMB <-> USD';
				return false;
			}
			
			// Find tax rate
			$taxRate = Model_ProfitConfig15::getTaxRate();
			
			$customer = new Model_Customer($customer_id);
			if ($customer->office_address_id == 0) {
				$this->errors[] = 'Please set 公司地址 in [Sales > 客戶列表]';
				return false;
			}
			
			// Create delivery note
			$deliveryNote = new Model_DeliveryNote();
			$deliveryNote->delivery_note_no = $this->generateDeliveryNoteNo($customer->cust_code);
			$deliveryNote->customer_id = $customer_id;
			$deliveryNote->rmb_to_jpy_rate = $rmbJPYRate->rate;
			$deliveryNote->rmb_to_usd_rate = $rmbUSDRate->rate;
			$deliveryNote->tax_rate = $taxRate;
			$deliveryNote->office_address_id = $customer->office_address_id;
			
			$deliveryNote->delivery_method_id = $this->delivery_method_id;
			$deliveryNote->delivery_method = trim($this->delivery_method);
			/* if ($deliveryNote->delivery_method_id == Model_DeliveryMethod::ID_OTHER) {
				$deliveryNote->delivery_method = $this->delivery_method;
			} */
			
			$deliveryNote->created_by = Auth::instance()->get_user()->username;
			$deliveryNote->print_date = $this->print_date;
			$deliveryNote->last_print_date = NULL;
			
			// Create delivery note's items
			$total_tax = 0;
			$total_detail_amt = 0;
			$orderIds = array();
			$deliveryFeeOrderPrdocutIds = array();
			$deliveryNoteDetails = array();
			
/**
 * From order product
 */
			foreach ($containers as $container) {
				$deliveryNoteDetail = new Model_DeliveryNoteDetail();
				$deliveryNoteDetail->delivery_note_id = $deliveryNote->id;
				$deliveryNoteDetail->container_id = $container->id;
				$deliveryNoteDetail->product_cd = $container->product_cd;
				$deliveryNoteDetail->is_tax = $container->is_tax;
				$deliveryNoteDetail->qty = $container->delivery_qty;
				$deliveryNoteDetail->market_price = $container->market_price;
				$deliveryNoteDetail->total = $deliveryNoteDetail->qty * $deliveryNoteDetail->market_price;
				$deliveryNoteDetail->currency = Model_DeliveryNoteDetail::CURRENCY_RMB;
				$deliveryNoteDetail->remark = isset($this->delivery_note_remarks[$container->id]) ? $this->delivery_note_remarks[$container->id] : '';
				$deliveryNoteDetail->source = Model_DeliveryNoteDetail::SOURCE_CONTAINER;
				$deliveryNoteDetails[] = $deliveryNoteDetail;
				
				$total_detail_amt += $deliveryNoteDetail->total;
				
				if ($deliveryNoteDetail->is_tax == Model_OrderProduct::TAX_INCLUDE) {
					$total_tax += $deliveryNoteDetail->total * $taxRate;
				}
				
				// Update container's status
				$container->status = Model_Container::STATUS_DELIVERY_NOTE_GENREATED;
				$container->save();
				
				// Update order product's QTY
				$orderProduct = new Model_OrderProduct($container->order_product_id);
				if ($container->source == Model_Container::SOURCE_FACTORY) {
					// From factory
					$orderProduct->factory_delivery_note_qty += $container->delivery_qty;
					
					if ($orderProduct->factory_delivery_note_qty == $orderProduct->factory_qty) {
						$orderProduct->factory_status = Model_OrderProduct::STATUS_DELIVERY_NOTE_GENERATED;
					}
					
					$orderProduct->save();
				} else {
					// From JP
					$orderProduct->jp_delivery_note_qty += $container->delivery_qty;
					
					if ($orderProduct->jp_delivery_note_qty == $orderProduct->jp_qty) {
						$orderProduct->jp_status = Model_OrderProduct::STATUS_DELIVERY_NOTE_GENERATED;
					}
					
					$orderProduct->save();
				}
				
				$orderIds[$orderProduct->order_id] = true;
				
				if (!array_key_exists($container->order_product_id, $deliveryFeeOrderPrdocutIds)) {
					if ($this->isOrderProdcutGenerateDeliveryNote($container->order_product_id)) {
						// This order product has ever generated delivery note.
						// The delivery fee is included in previous delivery note, so no need to include delivery note in S1
						$deliveryFeeOrderPrdocutIds[$container->order_product_id] = false;
					} else {
						// Generate delivery fee
						$deliveryFeeOrderPrdocutIds[$container->order_product_id] = true;
						
						$deliveryNoteDetail = new Model_DeliveryNoteDetail();
						$deliveryNoteDetail->reference_id = $container->order_product_id;
						$deliveryNoteDetail->reference_table = Model_DeliveryNoteDetail::TABLE_DELIVERY_FEE;
						$deliveryNoteDetail->description = '国内国外送料('.$container->product_cd.')';
						//$deliveryNoteExtraDetail->total = GlobalFunction::roundUpTo($container->delivery_fee * 1.0 / $rmbJPYRate->rate, 2);
						$deliveryNoteDetail->qty = 1;
						$deliveryNoteDetail->market_price = $container->delivery_fee;
						$deliveryNoteDetail->total = $container->delivery_fee;
						$deliveryNoteDetail->currency = Model_DeliveryNoteDetail::CURRENCY_JPY;
						$deliveryNoteDetail->source = Model_DeliveryNoteDetail::SOURCE_DELIVERY_FEE;
						$deliveryNoteDetails[] = $deliveryNoteDetail;
						
						//$total_detail_amt += $deliveryNoteExtraDetail->total;
						$total_detail_amt += GlobalFunction::convertJPY2RMB($container->delivery_fee, $rmbJPYRate->rate); // Delivery fee's currency is "JPY", so need to convert to "RMB"
					}
				}
			}
			
/**
 * Gift
 */
			/* foreach ($giftContainers as $container) {
				$deliveryNoteDetail = new Model_DeliveryNoteDetail();
				$deliveryNoteDetail->delivery_note_id = $deliveryNote->id;
				$deliveryNoteDetail->container_id = $container->id;
				$deliveryNoteDetail->product_cd = $container->product_cd;
				$deliveryNoteDetail->is_tax = Model_OrderProduct::TAX_NOT_INCLUDE;
				$deliveryNoteDetail->qty = $container->delivery_qty;
				$deliveryNoteDetail->market_price = $container->cost;
				$deliveryNoteDetail->total = $deliveryNoteDetail->qty * $deliveryNoteDetail->market_price;
				$deliveryNoteDetail->currency = Model_DeliveryNoteDetail::CURRENCY_RMB;
				$deliveryNoteDetail->source = Model_DeliveryNoteDetail::SOURCE_CONTAINER;
				$deliveryNoteDetails[] = $deliveryNoteDetail;
			
				$total_detail_amt += $deliveryNoteDetail->total;
				
				// Update container's status
				$container->status = Model_Container::STATUS_DELIVERY_NOTE_GENREATED;
				$container->save();
			} */
			
/**
 * Order Return
 */
			foreach ($orderReturns as $orderReturn) {
				$deliveryNoteDetail = new Model_DeliveryNoteDetail();
				$deliveryNoteDetail->delivery_note_id = $deliveryNote->id;
				$deliveryNoteDetail->container_id = 0;
				$deliveryNoteDetail->reference_id = $orderReturn->id;
				$deliveryNoteDetail->reference_table = Model_DeliveryNoteDetail::TABLE_ORDER_RETURN;
				$deliveryNoteDetail->product_cd = $orderReturn->product_cd;
				$deliveryNoteDetail->is_tax = 0;
				$deliveryNoteDetail->qty = $orderReturn->return_qty;
				$deliveryNoteDetail->market_price = $orderReturn->return_pay * -1;
				$deliveryNoteDetail->total = $deliveryNoteDetail->qty * $deliveryNoteDetail->market_price;
				$deliveryNoteDetail->currency = Model_DeliveryNoteDetail::CURRENCY_RMB;
				$deliveryNoteDetail->source = Model_DeliveryNoteDetail::SOURCE_ORDER_RETURN;
				
				// Remark
				$deliveryNoteDetail->remark = $orderReturn->remark;
				if (isset($this->delivery_note_order_return_remarks[$orderReturn->id])) {
					if (!empty($deliveryNoteDetail->remark)) {
						$deliveryNoteDetail->remark .= chr(13);
					}
					
					$deliveryNoteDetail->remark .= $this->delivery_note_order_return_remarks[$orderReturn->id];
				}
				
				$deliveryNoteDetails[] = $deliveryNoteDetail;
					
				$total_detail_amt += $deliveryNoteDetail->total;
				
				// Update order return's status
				$orderReturn->status = Model_OrderReturn::STATUS_COMPLETE;
				$orderReturn->save();
			}
			
			/**
			 * Save to DB
			 */
			$deliveryNote->total_detail_amt = $total_detail_amt;
			$deliveryNote->total_tax = GlobalFunction::roundUpTo($total_tax, 2); // round up to 2 decimal places
			$deliveryNote->total_amt = $total_detail_amt + $total_tax;
			$deliveryNote->save();
			
			foreach ($deliveryNoteDetails as $deliveryNoteDetail) {
				$deliveryNoteDetail->delivery_note_id = $deliveryNote->id;
				$deliveryNoteDetail->save();
			}
			
			/**
			 * Create order in S1
			 * (Currency = JPY)
			 */
			if (sizeOf($containers) > 0) {
				$this->createS1Order($customer, $containers, $rmbJPYRate->rate, $deliveryNote);
			}
			/* foreach ($containers as $container) {
				$includeDeliveryFee = false;
				if ($deliveryFeeOrderPrdocutIds[$container->order_product_id]) {
					$deliveryFeeOrderPrdocutIds[$container->order_product_id] = false;
					$includeDeliveryFee = true;
				}
				
				$this->createS1Order($customer, $container, $rmbJPYRate->rate, $taxRate, $includeDeliveryFee, $deliveryNote->delivery_note_no);
			} */
			
		} catch (Exception $e) {
			$db->rollback();
			$db2->rollback();
			$this->errors[] = $e->getMessage();
			return false;
		}
		
		$db->commit();
		$db2->commit();
		
		return true;
	}
	
	private function getPendingContainer() {
		// Find containers by customer
		$orm = ORM::factory('container')
				->join('order_product')->on('order_product.id', '=', 'container.order_product_id')
				->join('order')->on('order.id', '=', 'order_product.order_id')
				->join('customer')->on('customer.id', '=', 'order.customer_id')
				->join('temp_product_master')->on('temp_product_master.order_product_id', '=', 'order_product.id')
				//->join('product_master', 'LEFT')->on('product_master.no_jp', '=', 'order_product.product_cd')
				->join('delivery_method')->on('delivery_method.id', '=', 'order.delivery_method_id')
				->where('container.status', '=', Model_Container::STATUS_READY_FOR_DELIVERY_NOTE)
				->select('order_product.order_id')
				->select('order_product.product_cd')
				->select('market_price')
				->select('product_desc')
				->select('cust_code')
				->select('order.s1_client_name')
				->select('order.tel')
				->select('order.postal_code')
				->select(array('delivery_method.description', 'delivery_method_description'))
				->select('order.delivery_method')
				->select('order.delivery_address1')
				->select('order.delivery_address2')
				->select('order.delivery_address3');
		
		if ($this->customer_id != 0) {
			$orm->where('order.customer_id', '=', $this->customer_id);
		}
		 
		$this->containers = $orm->find_all();
		 
	 	// Order Return
		$orm = ORM::factory('orderReturn')
				->join('customer')->on('customer.id', '=', 'orderreturn.customer_id')
				->join('pm_product_master', 'LEFT')->on('pm_product_master.no_jp', '=', 'orderreturn.product_cd')
		 		//->join('product_master', 'LEFT')->on('product_master.no_jp', '=', 'orderreturn.product_cd')
		 		->where('orderreturn.status', '=', Model_OrderReturn::STATUS_READY_FOR_DELIVERY_NOTE)
		 		->select('product_desc')
		 		->select('cust_code');
		
		if ($this->customer_id != 0) {
			$orm->where('orderreturn.customer_id', '=', $this->customer_id);
		}
		
		$this->pendingOrderReturns = $orm->find_all();
	}
	
	private function getDeliveryNoteDetail($deliveryNoteId) {
		return ORM::factory('deliveryNoteDetail')
				->with('container')
				->join('order_product')->on('order_product.id', '=', 'container.order_product_id')
				->join('order')->on('order.id', '=', 'order_product.order_id')
				->join('temp_product_master')->on('temp_product_master.order_product_id', '=', 'order_product.id')
				//->join('product_master', 'LEFT')->on('product_master.no_jp', '=', 'deliverynotedetail.product_cd')
				->where('deliverynotedetail.delivery_note_id', '=', $deliveryNoteId)
				->select('order_product.order_id')
				->select('product_desc')
				->select('container.container_no')
				->find_all();
	}
	
	private function createS1Customer($customer) {
		$result = ORM::factory('S1_Customer')->where('cust_cd', '=', $customer->cust_code)->find();
		if (!$result->loaded()) {
			$s1Customer = new Model_S1_Customer();
			$s1Customer->cust_cd = $customer->cust_code;
			$s1Customer->cust_company_name = $this->encode($customer->name);
			$s1Customer->cust_contact_name = $this->encode($customer->contact_person);
			$s1Customer->cust_tel = $customer->tel;
			$s1Customer->cust_post_cd = $customer->postal_code;
			$s1Customer->cust_post_address1 = $this->encode($customer->address1);
			$s1Customer->cust_post_address2 = $this->encode(trim($customer->address2.' '.$customer->address3));
			$s1Customer->create_by = 'S3';
			$s1Customer->create_date = DB::expr('current_timestamp');
			$s1Customer->website = '';
			$s1Customer->save();
		}
	}
	
	private function getNextSaleRef() {
		$result = DB::select(array(DB::expr('max(sale_ref)'), 'max'))
					->from('ben_sale')
					->where('sale_chk_ref', '=', Model_S1_BenSale::SALE_CHK_REF_S3)
					->where('sale_ref', 'like', 's3-%')
					->execute('s1');
		
		if (sizeOf($result) > 0) {
			$maxSeq = intval(substr($result[0]['max'], 3)); // format: s3-XXXX
		} else {
			$maxSeq = 0;
		}
		
		do {
			$maxSeq++;
			$newSaleRef = 's3-'.$maxSeq;
			
			$result = DB::select(array(DB::expr('count(sale_ref)'), 'count'))
						->from('ben_sale')
						->where('sale_ref', '=', $newSaleRef)
						->execute('s1');
		} while ($result[0]['count'] > 0);
			
		return $newSaleRef;
	}
	
	private function createS1Order($customer, $containers, $rmb_to_jpy_rate, $deliveryNote) {
		$nextSaleRef = $this->getNextSaleRef();
		//$order = new Model_Order($container->order_id);
		$isCreateCustomer = false;
		
		$s1_tel = '';
		$s1_postal_code = '';
		$s1_client_name = '';
		if ($this->order_id_for_delivery_address != '') {
			$orderForDeliveryAddress = new Model_Order($this->order_id_for_delivery_address);
			$s1_tel = $orderForDeliveryAddress->tel;
			$s1_postal_code = $orderForDeliveryAddress->postal_code;
			$s1_client_name = $orderForDeliveryAddress->s1_client_name;
		}
	
		// ben_sale
		$sale = new Model_S1_BenSale();
		$sale->sale_ref = $nextSaleRef;
		//$sale->sale_date = DB::expr('current_date');
		$sale->sale_date = $deliveryNote->print_date;
		$sale->sale_group = $customer->getS1SalesGroup();
		$sale->sale_yahoo_id = $customer->cust_code.'-'.$deliveryNote->delivery_note_no;
		$sale->sale_dat = DB::expr('current_date');
		$sale->sale_chk_ref = Model_S1_BenSale::SALE_CHK_REF_S3;
		$sale->sale_discount = 0;
		$sale->sale_tax = 0;
		$sale->s3_delivery_note_no = $deliveryNote->delivery_note_no;
		$sale->sale_ship_fee = 0;
		$sale->sale_email = $customer->email;
		
		if (trim($s1_client_name) != '') {
			$sale->sale_name = $this->encode($s1_client_name);
		} else {
			$sale->sale_name = $this->encode($customer->name);
		}
		
		$isCreateCustomer = true;
		/* if (trim($order->s1_client_name) == '') {
			// Client name not filled in order -> use customer name
			$sale->sale_name = $this->encode($customer->name);
			$isCreateCustomer = true;
		} else {
			$sale->sale_name = $this->encode($order->s1_client_name);
		} */
	
		// ben_sale_prod
		$saleProducts = array();
		foreach ($containers as $container) {
			$saleProduct = new Model_S1_BenSaleProd();
			$saleProduct->sprod_ref = $nextSaleRef;
			$saleProduct->sprod_id = $container->product_cd;
			$saleProduct->sprod_price = GlobalFunction::convertRMB2JPY($container->market_price, $rmb_to_jpy_rate);
			$saleProduct->sprod_unit = $container->delivery_qty;
			
			$product = ORM::factory('tempProductMaster')
						->where('tempproductmaster.order_product_id', '=', $container->order_product_id)
						->find();
			
			$saleProduct->sprod_name = $product->loaded() ? $this->encode($product->product_desc) : '';
			
			$saleProducts[] = $saleProduct;
		}
	
		// ben_debt
		$saleDebt = new Model_S1_BenDebt();
		$saleDebt->debt_ref = $nextSaleRef;
		
		if (trim($s1_postal_code) != '') {
			$saleDebt->debt_post_co = $s1_postal_code;
		} else {
			$saleDebt->debt_post_co = $customer->postal_code;
		}
		
		if (trim($s1_tel) != '') {
			$saleDebt->debt_tel = $s1_tel;
		} else {
			$saleDebt->debt_tel = $customer->tel;
		}
		
		
		$deliveryMethod = new Model_DeliveryMethod($deliveryNote->delivery_method_id);
		$deliveryDescription = $deliveryMethod->description;
		if ($deliveryNote->delivery_method != '') {
			$deliveryDescription = $deliveryDescription.' - '.$deliveryNote->delivery_method;
		}
		/* if ($deliveryNote->delivery_method_id != Model_DeliveryMethod::ID_OTHER) {
			$deliveryMethod = new Model_DeliveryMethod($deliveryNote->delivery_method_id);
			$deliveryDescription = $deliveryMethod->description;
		} else {
			$deliveryDescription = $deliveryNote->delivery_method;
		} */
		$saleDebt->debt_remark = $this->encode('出貨單 No: '.$deliveryNote->delivery_note_no.chr(13).'発送方法: '.$deliveryDescription);
		if ($this->s1_remark != '') {
			//$saleDebt->debt_remark .= chr(13).$this->s1_remark;  ricky 20140521
			$saleDebt->debt_remark .= chr(13).$this->encode($this->s1_remark);
		}

		if ($this->order_id_for_delivery_address == '') {
			$saleDebt->debt_cust_address1 = $this->encode($customer->address1);
			$saleDebt->debt_cust_address2 = $this->encode($customer->address2);
			$saleDebt->debt_cust_address3 = $this->encode($customer->address3);
		} else {
			$orderForDeliveryAddress = new Model_Order($this->order_id_for_delivery_address);
			$saleDebt->debt_cust_address1 = $this->encode($orderForDeliveryAddress->delivery_address1);
			$saleDebt->debt_cust_address2 = $this->encode($orderForDeliveryAddress->delivery_address2);
			$saleDebt->debt_cust_address3 = $this->encode($orderForDeliveryAddress->delivery_address3);
		}
		
		/* if ($order->delivery_address1 == '' && $order->delivery_address2 == '' && $order->delivery_address3 == '') {
			// Order delivery date not filled in -> use customer's address
			$saleDebt->debt_cust_address1 = $this->encode($customer->address1);
			$saleDebt->debt_cust_address2 = $this->encode($customer->address2);
			$saleDebt->debt_cust_address3 = $this->encode($customer->address3);
		} else {
			$saleDebt->debt_cust_address1 = $this->encode($order->delivery_address1);
			$saleDebt->debt_cust_address2 = $this->encode($order->delivery_address2);
			$saleDebt->debt_cust_address3 = $this->encode($order->delivery_address3);
		} */
	
		// ben_bal
		$saleBal = new Model_S1_BenBal();
		$saleBal->bal_ref = $nextSaleRef;
		$saleBal->bal_pay = 0;
		$saleBal->bal_dat = DB::expr('current_date');
		$saleBal->bal_pay_type = 'Bank';
		$saleBal->bal_ship_type = '';

		/**
		 * Save to DB
		 */
		$sale->save();
		foreach ($saleProducts as $saleProduct) {
			$saleProduct->save();
		}
		$saleDebt->save();
		$saleBal->save();

		if ($isCreateCustomer) {
			$this->createS1Customer($customer);
		}
	}
	
	/* private function createS1Order_bak($customer, $container, $rmb_to_jpy_rate, $taxRate, $includeDeliveryFee, $deliveryNoteNo) {
		$nextSaleRef = $this->getNextSaleRef();
		$order = new Model_Order($container->order_id);
		$isCreateCustomer = false;
		
		// ben_sale
		$sale = new Model_S1_BenSale();
		$sale->sale_ref = $nextSaleRef;
		$sale->sale_date = DB::expr('current_date');
		$sale->sale_group = Model_Accountant_DeliveryNoteForm::S1_SALES;
		$sale->sale_email = $customer->email;
		$sale->sale_yahoo_id = $customer->cust_code.'-'.$container->order_id;
		$sale->sale_dat = DB::expr('current_date');
		$sale->sale_chk_ref = Model_S1_BenSale::SALE_CHK_REF_AUTO;
		$sale->sale_discount = 0;
		$sale->sale_tax = $container->is_tax == Model_OrderProduct::TAX_INCLUDE ? $taxRate * 100 : 0;
		$sale->s3_delivery_note_no = $deliveryNoteNo;
		
		if ($includeDeliveryFee) {
			$sale->sale_ship_fee = ceil($container->delivery_fee); // Delivery fee's currency has already been JPY
		}
		
		if (trim($order->s1_client_name) == '') {
			// Client name not filled in order -> use customer name
			$sale->sale_name = $this->encode($customer->name);
			$isCreateCustomer = true;
		} else {
			$sale->sale_name = $this->encode($order->s1_client_name);
		}
		
		// ben_sale_prod
		$saleProduct = new Model_S1_BenSaleProd();
		$saleProduct->sprod_ref = $nextSaleRef;
		$saleProduct->sprod_id = $container->product_cd;
		$saleProduct->sprod_price = ceil($container->market_price * $rmb_to_jpy_rate);
		$saleProduct->sprod_unit = $container->delivery_qty;
			
		$product = Model_ProductMaster::getProductByNoJp($container->product_cd);
		$saleProduct->sprod_name = $product->loaded() ? $this->encode($product->product_desc) : '';
		
		// ben_debt
		$saleDebt = new Model_S1_BenDebt();
		$saleDebt->debt_ref = $nextSaleRef;
		$saleDebt->debt_tel = $customer->tel;
		$saleDebt->debt_post_co = $customer->postal_code;
		$saleDebt->debt_remark = $this->encode($container->delivery_method);
		
		if ($order->delivery_address1 == '' && $order->delivery_address2 == '' && $order->delivery_address3 == '') {
			// Order delivery date not filled in -> use customer's address
			$saleDebt->debt_cust_address1 = $this->encode($customer->address1);
			$saleDebt->debt_cust_address2 = $this->encode($customer->address2);
			$saleDebt->debt_cust_address3 = $this->encode($customer->address3);
		} else {
			$saleDebt->debt_cust_address1 = $this->encode($order->delivery_address1);
			$saleDebt->debt_cust_address2 = $this->encode($order->delivery_address2);
			$saleDebt->debt_cust_address3 = $this->encode($order->delivery_address3);
		}
		
		// ben_bal
		$saleBal = new Model_S1_BenBal();
		$saleBal->bal_ref = $nextSaleRef;
		$saleBal->bal_pay = 0;
		$saleBal->bal_dat = DB::expr('current_date');
		$saleBal->bal_pay_type = 'Bank';
		$saleBal->bal_ship_type = '';
			
		/*
		 * Save to DB
		 /
		$sale->save();
		$saleProduct->save();
		$saleDebt->save();
		$saleBal->save();
		
		if ($isCreateCustomer) {
			$this->createS1Customer($customer);
		}
	} */
	
	private function generateDeliveryNoteNo($cust_code) {
		$runningSequence = new Model_CustomerRunningSequence($cust_code);
		if (!$runningSequence->loaded()) {
			// Not exist
			$runningSequence = new Model_CustomerRunningSequence();
			$runningSequence->cust_code = $cust_code;
			$runningSequence->delivery_note_seq = 0;
			$runningSequence->invoice_seq = 0;
		}
		
		$runningSequence->delivery_note_seq++;
		$runningSequence->save();
		
		return 'D-'.$cust_code.'-'.str_pad($runningSequence->delivery_note_seq, 8, '0', STR_PAD_LEFT);
	}
	
	private function isOrderProdcutGenerateDeliveryNote($order_product_id) {
		$result = DB::select(array(DB::expr('COUNT(delivery_note_detail.id)'), 'count'))
					->from('delivery_note_detail')
					->join('container')->on('container.id', '=', 'delivery_note_detail.container_id')
					->where('container.order_product_id', '=', $order_product_id)
					->execute();
		return $result[0]['count'] == 0 ? false : true;
	}
	
	private function encode($value) {
		return mb_convert_encoding($value, "EUC-JP","UTF-8");
	}

// Overrided function
	public function getData($limit, $offset) {
		return $this->getCriteria()
					->select('customer.cust_code')
					->order_by('deliverynote.id', 'desc')
					->limit($limit)
					->offset($offset)
					->find_all();
	}
	
	public function getCriteria() {
		$orm = ORM::factory('deliveryNote')
				->join('customer')->on('customer.id', '=', 'deliverynote.customer_id');
		
		if ($this->customer_id != 0) {
			$orm->where('deliverynote.customer_id', '=', $this->customer_id);
		}
		
		return $orm;
	}
	
	public function getQueryString() {
		return '&customer_id='.$this->customer_id;
	}
}