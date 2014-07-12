<?php
class Model_Auditor_FactoryOrderProduct extends Model_OrderProduct {
public $selected;
	public $auditor_remark;

	public function populate($post) {
		$this->values($post);
		
		$this->selected = isset($post['selected']) ? true : false;
		$this->auditor_remark = isset($post['auditor_remark']) ? $post['auditor_remark'] : NULL;
	}
	
	public function goToTranslator() {
		$this->factory_status = Model_OrderProduct::STATUS_TRANSLATOR; // Go to next step
		$this->is_reject = Model_OrderProduct::IS_REJECT_NO;
		$this->factory_auditor_remark = $this->auditor_remark;
		
		$this->_valid = true; // Skip validation
		$this->save();
	}
	
	public function backToKaitoStaff() {
		$this->factory = NULL;
		$this->factory_qty = NULL;
		$this->factory_status = Model_OrderProduct::STATUS_KAITOSTAFF;
		$this->is_reject = Model_OrderProduct::IS_REJECT_YES;
		$this->factory_auditor_remark = $this->auditor_remark;
		
		if ($this->jp_qty == NULL || $this->jp_qty == 0) {
			// No QTY is assigned to JP
			$this->jp_qty = NULL;
			$this->jp_status = Model_OrderProduct::STATUS_KAITOSTAFF;
		}
		
		$this->_valid = true; // Skip validation
		$this->save();
	}
	
	public function getFactoryQty() {
		return $this->factory_qty;
	}
	
	public function isSaveEnable() {
		return $this->factory_status == Model_OrderProduct::STATUS_AUDITOR;
	}
	
	public function getAuditorRemark() {
		return $this->auditor_remark == NULL ? $this->factory_auditor_remark : $this->auditor_remark;
	}
	
	public function isRejectedByNextStep() {
		if ($this->is_reject == Model_OrderProduct::IS_REJECT_YES
				&& $this->factory_status == Model_OrderProduct::STATUS_AUDITOR) {
			return true;
		} else {
			return false;
		}
	}
}