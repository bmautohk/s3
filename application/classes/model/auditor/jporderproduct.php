<?php
class Model_Auditor_JpOrderProduct extends Model_OrderProduct {
	public $selected;
	public $auditor_remark;

	public function populate($post) {
		$this->values($post);
		
		$this->selected = isset($post['selected']) ? true : false;
		$this->auditor_remark = isset($post['auditor_remark']) ? $post['auditor_remark'] : NULL;
	}
	
	public function goToTranslator() {
		$this->jp_status = Model_OrderProduct::STATUS_TRANSLATOR; // Go to next step
		$this->is_reject = Model_OrderProduct::IS_REJECT_NO;
		$this->jp_auditor_remark = $this->auditor_remark;
		
		$this->_valid = true; // Skip validation
		$this->save();
	}
	
	public function backToKaitostaff() {
		$this->jp_qty = NULL;
		$this->jp_status = Model_OrderProduct::STATUS_KAITOSTAFF;
		$this->is_reject = Model_OrderProduct::IS_REJECT_YES;
		$this->jp_auditor_remark = $this->auditor_remark;
		
		if ($this->factory_qty == NULL || $this->factory_qty == 0) {
			// No QTY is assigned to factory
			$this->factory_qty = NULL;
			$this->factory_status = Model_OrderProduct::STATUS_KAITOSTAFF;
		}
		
		$this->_valid = true; // Skip validation
		$this->save();
	}
	
	public function getFactoryQty() {
		return $this->jp_qty;
	}
	
	public function isSaveEnable() {
		return $this->jp_status == Model_OrderProduct::STATUS_AUDITOR;
	}
	
	public function getAuditorRemark() {
		return $this->auditor_remark == NULL ? $this->jp_auditor_remark : $this->auditor_remark;
	}
	
	public function isRejectedByNextStep() {
		if ($this->is_reject == Model_OrderProduct::IS_REJECT_YES
				&& $this->jp_status == Model_OrderProduct::STATUS_AUDITOR) {
			return true;
		} else {
			return false;
		}
	}
}