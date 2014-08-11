<?php defined('SYSPATH') or die('No direct script access.');

class Model_OrderProduct extends ORM {
	public $_table_name = 'order_product';
	
	const TAX_NOT_INCLUDE = 0;
	const TAX_INCLUDE = 1;
	
	const STATUS_DELETE = -10;
	const STATUS_CANCEL = 0;
	const STATUS_SALES = 10;
	const STATUS_KAITOSTAFF = 20;
	const STATUS_AUDITOR = 30;
	const STATUS_TRANSLATOR = 40;
	const STATUS_FACTORY = 50;
	const STATUS_WAREHOUSE= 60;
	const STATUS_ACCOUNTANT= 70;
	const STATUS_DELIVERY_NOTE_GENERATED = 71;
	const STATUS_INVOICE_GENERATED = 72;
	const STATUS_COMPLETE = 99;
	
	const FACTORY_GZ = 'G';
	const FACTORY_BEN = 'B';
	
	const CURRENCY_JPY = 'JPY';
	const CURRENCY_USD = 'USD';
	
	const IS_REJECT_YES = "Y";
	const IS_REJECT_NO = "N";
	
	protected $_belongs_to = array('order' => array('model' => 'order', 'foreign_key' => 'order_id'),
									//'productMaster' => array('model' => 'productMaster', 'foreign_key' => 'product_cd')
							);
	
	protected $_has_one = array('containerSummary' => array('model' => 'containerSummary', 'foreign_key' => 'order_product_id'),
								'productMaster' => array('model' => 'tempProductMaster', 'foreign_key' => 'order_product_id'));

	protected $_table_columns = array(
			"id" => array("type" => "int"),
			"order_id" => array("type" => "int"),
			"product_cd" => array("type" => "string"),
			"qty" => array("type" => "int"),
			"market_price" => array("type" => "double"),
			"kaito" => array("type" => "double"),
			"is_tax" => array("type" => "string"),
			"is_shipping_fee" => array("type" => "string"),
			"delivery_fee" => array("type" => "double"),
			"profit" => array("type" => "double"),
			"factory" => array("type" => "string"),
			"factory_qty" => array("type" => "int"),
			"factory_entry_qty" => array("type" => "int"),
			"factory_delivery_qty" => array("type" => "int"),
			"factory_delivery_note_qty" => array("type" => "int"),
			"factory_invoice_qty" => array("type" => "int"),
			"warehouse_borrow_qty" => array("type" => "int"),
			"warehouse_return_qty" => array("type" => "int"),
			"jp_qty" => array("type" => "int"),
			"jp_delivery_note_qty" => array("type" => "int"),
			"jp_invoice_qty" => array("type" => "int"),
			"kaito_remark" => array("type" => "string"),
			"jp_auditor_remark" => array("type" => "string"),
			"factory_auditor_remark" => array("type" => "string"),
			"translator_remark" => array("type" => "string"),
			"factory_remark" => array("type" => "string"),
			"propose_delivery_date" => array("type" => "date"),
			"jp_status" => array("type" => "string"),
			"factory_status" => array("type" => "string"),
			"is_reject" => array("type" => "string"),
			"has_container_to_accountant" => array("type" => "string"),
			"translator_first_update_date" => array("type" => "date"),
			"translator_last_update_date" => array("type" => "date"),
	);
	
	public function rules() {
		return array(
				'product_cd' => array(
						array('not_empty')
				),
				'qty' => array(
						array('not_empty'),
						array('digit')
				),
				'market_price' => array(
						array('not_empty')
				),
				'delivery_fee' => array(
						array('not_empty'),
						array('numeric')
				),
		);
	}
	
	public static function getFactoryCode($name) {
		if ($name == 'ben') {
			return Model_OrderProduct::FACTORY_BEN;
		} else if ($name == 'gz') {
			return Model_OrderProduct::FACTORY_GZ;
		} else {
			return '';
		}
	}
	
	public static function getTaxOptions() {
		return array(self::TAX_INCLUDE=>'拔', self::TAX_NOT_INCLUDE=>'込');
	}
	
	public static function getShippingFeeOptions() {
		return array('0'=>'拔', '1'=>'込');
	}
	
	public function getTaxDescription() {
		if ($this->is_tax == self::TAX_INCLUDE) {
			return '拔';
		} else {
			return '込';
		}
	}
	
	public function getShippingFeeDescription() {
		if ($this->is_shipping_fee == 0) {
			return '拔';
		} else {
			return '込';
		}
	}

	public function getSubTotal() {
		if ($this->qty == NULL || $this->market_price == NULL) {
			return '';
		} else {
			return $this->qty * $this->market_price;
		}
	}
	
	public function getSubTotalWithDeliveryFeeTax($rmb_to_jpy_rate, $tax_rate) {
		$subTotal_rmb = $this->market_price * $this->qty;
		
		if ($this->is_tax == self::TAX_INCLUDE) {
			$subTotal_rmb = $subTotal_rmb * (1 + $tax_rate);
		}
		
		if (!empty($this->delivery_fee)) {
			$subTotal_rmb += GlobalFunction::convertJPY2RMB($this->delivery_fee, $rmb_to_jpy_rate);
		}
		
		return $subTotal_rmb;
	}

	public function getProcessingStep() {
		switch ($this->factory_status) {
			case self::STATUS_CANCEL:
				return "Cancel";
			case self::STATUS_SALES:
				return "";
			case self::STATUS_KAITOSTAFF:
				return "大步哥";
			case self::STATUS_AUDITOR:
				return "Auditor";
			case self::STATUS_TRANSLATOR:
				return "高原";
			case self::STATUS_FACTORY:
				return "工場";
			case self::STATUS_WAREHOUSE:
				return "工場";
			default:
				if ($this->factory_status >= self::STATUS_ACCOUNTANT) {
					return "工場";
				} else {
					return "";
				}
		}
	}

	public function getItemLevelStatusDescription() {
		$minStatus = min($this->factory_status, $this->jp_status);
		
		if ($minStatus == self::STATUS_COMPLETE) {
			return '完成';
		}
		if ($minStatus >= self::STATUS_INVOICE_GENERATED) {
			return '請求済';
		} else if ($minStatus >= self::STATUS_DELIVERY_NOTE_GENERATED) {
			return '発送済';
		} else if ($minStatus == self::STATUS_CANCEL) {
			return "Cancel";
		} else {
			return '未完成';
		}
	}
	
	public function isEnableSalesEdit() {
		return $this->order->status != Model_Order::STATUS_VOID && $this->jp_status == Model_OrderProduct::STATUS_SALES && $this->factory_status == Model_OrderProduct::STATUS_SALES;
	}
	
	public function isEnableCancel() {
		return $this->factory_status == Model_OrderProduct::STATUS_SALES && $this->jp_status == Model_OrderProduct::STATUS_SALES;
	}
	
	public function getSubTotalWithDifferentCurrency($rmb_to_jpy_rate, $rmb_to_usd_rate, $tax_rate) {
		if ($this->qty == NULL || $this->market_price == NULL) {
			return array(
					0 => 0,
					1 => 0,
					2 => 0
				);
		}
		
		$result = array();
		
		$subTotal_rmb = $this->market_price * $this->qty;
		$subTotal_jpy = GlobalFunction::convertRMB2JPY($this->market_price, $rmb_to_jpy_rate) * $this->qty;
		
		if ($this->is_tax == self::TAX_INCLUDE) {
			$subTotal_rmb = $subTotal_rmb * (1 + $tax_rate);
			$subTotal_jpy = $subTotal_jpy * (1 + $tax_rate);
		}
		
		if (!empty($this->delivery_fee)) {
			$subTotal_rmb += GlobalFunction::convertJPY2RMB($this->delivery_fee, $rmb_to_jpy_rate);
			$subTotal_jpy += $this->delivery_fee;
		}
		
		$subTotal_usd = $subTotal_rmb * $rmb_to_usd_rate;
		
		$result[0] = $subTotal_rmb;
		$result[1] = $subTotal_jpy;
		$result[2] = $subTotal_usd;
		
		return $result;
	}
	
	public function getDisplayProfitWithDifferentCurrency($rmb_to_jpy_rate, $rmb_to_usd_rate) {
		$profit_rmb = $this->profit;
		if ($profit_rmb == '') {
			return '';
		}
		$subTotal_jpy = round($profit_rmb * $rmb_to_jpy_rate);
		$profit_usd = $profit_rmb * $rmb_to_usd_rate;
		
		return GlobalFunction::displayNumber($profit_rmb).' / '.number_format($subTotal_jpy).' / '.GlobalFunction::displayNumber($profit_usd);
	}
	
	public function getProfit($currency, $rate) {
		if ($currency == self::CURRENCY_JPY) {
			$profit = GlobalFunction::convertRMB2JPY($this->profit, $rate);
		} else {
			$profit = $this->profit * $rate;
		}
		
		return $profit;
	}
	
	public function getFormatProfit($currency, $rate) {
		$profit = $this->getProfit($currency, $rate);
		if ($currency == self::CURRENCY_JPY) {
			return GlobalFunction::displayJPYNumber($profit);
		} else {
			return GlobalFunction::displayNumber($profit);
		}
	}
	
	public function isEnableBorrow() {
		return $this->factory_status != 99 && $this->factory_qty > $this->warehouse_borrow_qty;
	}
	
	public function isEnableReturn() {
		return $this->factory_status != 99 && $this->warehouse_borrow_qty > $this->warehouse_return_qty;
	}
	
	public function refreshHasContainerToAccountant() {
		$result = DB::select(array(DB::expr('COUNT(container.id)'), 'count'))
				->from('container')
				->where('order_product_id', '=', $this->id)
				->where('status', '=', Model_Container::STATUS_INIT)
				->execute();
		
		$this->has_container_to_accountant = $result[0]['count'] > 0 ? 'Y' : 'N';
	}
}