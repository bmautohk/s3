<?php
class Model_Kaitostaff_OrderProduct extends Model_OrderProduct {
	public $selected;
	public $gz_qty;
	public $ben_qty;
	
	public function rules() {
		return array(
				'id' => array(
						array('Model_Kaitostaff_OrderProduct::checkValidQty', array(':model')),
						array('Model_Kaitostaff_OrderProduct::checkInputQty', array(':model')),
				),
		);
	}
	
	public function populate($post) {
		$this->values($post);
		
		$this->selected = isset($post['selected']) ? true : false;
		$this->gz_qty = isset($post['gz_qty']) ? $post['gz_qty'] : NULL;
		$this->ben_qty = isset($post['ben_qty']) ? $post['ben_qty'] : NULL;
	}
	
	public function goToAuditor() {
		// Update factory status
		if (!empty($this->gz_qty)) {
			$this->factory = Model_OrderProduct::FACTORY_GZ;
			$this->factory_qty = $this->gz_qty;
			$this->factory_status = Model_OrderProduct::STATUS_AUDITOR;
			$this->is_reject = Model_OrderProduct::IS_REJECT_NO;
		} else if (!empty($this->ben_qty)) {
			$this->factory = Model_OrderProduct::FACTORY_BEN;
			$this->factory_qty = $this->ben_qty;
			$this->factory_status = Model_OrderProduct::STATUS_AUDITOR;
			$this->is_reject = Model_OrderProduct::IS_REJECT_NO;
		} else {
			// No QTY passed to factory
			$this->factory_qty = NULL;
			$this->factory_status = Model_OrderProduct::STATUS_COMPLETE;
		}
		
		// Update JP status
		if (!empty($this->jp_qty)) {
			$this->jp_status = Model_OrderProduct::STATUS_AUDITOR; // Go to next step
		} else {
			// No QTY passed to JP
			$this->jp_qty = NULL;
			$this->jp_status = Model_OrderProduct::STATUS_COMPLETE;
		}
		
		$this->_valid = true; // Skip validation
		$this->save();
	}
	
	public function backToSales() {
		$this->jp_qty = NULL;
		$this->factory_qty = NULL;
		$this->factory = NULL;
		$this->jp_status = Model_OrderProduct::STATUS_SALES;
		$this->factory_status = Model_OrderProduct::STATUS_SALES;
		$this->is_reject = Model_OrderProduct::IS_REJECT_YES;
		
		$this->_valid = true; // Skip validation
		$this->save();
	}
	
	public function isSaveEnable() {
		return $this->jp_status == Model_OrderProduct::STATUS_KAITOSTAFF || $this->factory_status == Model_OrderProduct::STATUS_KAITOSTAFF;
	}
	
	public function isEnableInputJP() {
		return $this->jp_status == Model_OrderProduct::STATUS_KAITOSTAFF;
	}
	
	public function isEnableInputFactory() {
		return $this->factory_status == Model_OrderProduct::STATUS_KAITOSTAFF;
	}
	
	public function getGzQty() {
		if ($this->factory == Model_OrderProduct::FACTORY_GZ) {
			return $this->factory_qty;
		} else if ($this->factory == Model_OrderProduct::FACTORY_BEN) {
			return '';
		} else {
			return $this->gz_qty;
		}
	}
	
	public function getBenQty() {
		if ($this->factory == Model_OrderProduct::FACTORY_GZ) {
			return '';
		} else if ($this->factory == Model_OrderProduct::FACTORY_BEN) {
			return $this->factory_qty;
		} else {
			return $this->ben_qty;
		}
	}
	
	public static function checkValidQty($model) {
		// Only can fill in on of the qty
		return empty($model->gz_qty) || empty($model->ben_qty);
	}
	
	public static function checkInputQty($model) {
		if ($model->factory_status > Model_OrderProduct::STATUS_KAITOSTAFF) {
			// Factory QTY has already been assigned
			return $model->qty == $model->jp_qty + $model->factory_qty;
		} else {
			return $model->qty == $model->jp_qty + $model->gz_qty + $model->ben_qty;
		}
	}
	
	public function isRejectedByNextStep() {
		if ($this->is_reject == Model_OrderProduct::IS_REJECT_YES
				&& ($this->jp_status == Model_OrderProduct::STATUS_KAITOSTAFF
				|| $this->factory_status == Model_OrderProduct::STATUS_KAITOSTAFF)) {
			return true;
		} else {
			return false;
		}
	}
}