<?php
class Model_Translator_OrderProduct extends Model_OrderProduct {
	public $selected;

	public function populate($post) {
		$this->values($post);
		
		$this->selected = isset($post['selected']) ? true : false;
	}
	
	public function goToFactory() {
		$this->factory_status = Model_OrderProduct::STATUS_FACTORY; // Go to next step
		$this->is_reject = Model_OrderProduct::IS_REJECT_NO;
		
		if ($this->translator_first_update_date == NULL) {
			$this->translator_first_update_date = date("Y-m-d");
		}
		
		$this->translator_last_update_date = date("Y-m-d");
		
		$this->_valid = true; // Skip validation
		$this->save();
	}
	
	public function backToAuditor() {
		$this->factory_status = Model_OrderProduct::STATUS_AUDITOR;
		$this->is_reject = Model_OrderProduct::IS_REJECT_YES;
		
		$this->_valid = true; // Skip validation
		$this->save();
	}
	
	public function getFactoryQty() {
		return $this->factory_qty;
	}
	
	public function isSaveEnable() {
		return $this->factory_status == Model_OrderProduct::STATUS_TRANSLATOR;
	}
	
	public function isRejectedByNextStep() {
		if ($this->is_reject == Model_OrderProduct::IS_REJECT_YES 
				&& $this->factory_status == Model_OrderProduct::STATUS_TRANSLATOR) {
			return true;
		} else {
			return false;
		}
	}
}