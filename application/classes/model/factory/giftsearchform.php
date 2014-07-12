<?php
class Model_Factory_GiftSearchForm extends Model_PageForm {
	public $factory;
	
	public $search_action;
	
	public $gifts;
	
	public $page_url = 'factory/gift_list/factory';
	
	public function __construct($factory) {
		$this->factory = $factory;
		$this->page_url .= '/'.$factory;
	}
	
	public function populate($post) {
		$this->search_action = isset($post['search_action']) ? $post['search_action'] : NULL;
	}
	
	public function process() {
		$this->gifts= $this->search();
	}
	
// Overrided function
	public function getData($limit, $offset) {
		return $this->getCriteria()
		->select('customer.cust_code')
		->order_by('delivery_date', 'desc')
		->limit($limit)
		->offset($offset)
		->find_all();
	}
	
	public function getCriteria() {
		$orm = ORM::factory('gift')
				->join('customer')->on('customer.id', '=', 'gift.customer_id')
				->where('factory', '=', Model_Gift::getFactoryCode($this->factory));
		
		return $orm;
	}
	
	public function getQueryString() {
		return '';
	}
}