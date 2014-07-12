<?php
class Controller_Factory extends Controller_CustomTemplate {

	public $template = 'template/main';

	public function action_index() {
		if ($this->hasPrivilege('factory_gz_list')) {
			$this->action_list(GlobalConstant::FORM_FACTORY_GZ);
		} else if ($this->hasPrivilege('factory_gz_gift')) {
			$this->action_gift_list(GlobalConstant::FORM_FACTORY_GZ);
		} else if ($this->hasPrivilege('factory_ben_list')) {
			$this->action_list(GlobalConstant::FORM_FACTORY_GZ);
		} else if ($this->hasPrivilege('factory_ben_gift')) {
			$this->action_gift_list(GlobalConstant::FORM_FACTORY_GZ);
		} else {
			$this->request->redirect('main/no_permission');
		}
	}
	
/* ******************************************************
 * Factory Product List
*******************************************************/
	public function action_list($inputFactory=NULL) {
		if ($inputFactory == NULL) {
			$factory = $this->request->param('factory');
		} else {
			$factory = $inputFactory;
		}
		
		$this->checkPrivilege('factory_'.$factory.'_list');
		
		$form = new Model_Factory_SearchForm($factory);
		$form->populate($_REQUEST);
		
		$form->searchAction();
		
		// Display
		$view = View::factory('factory/product_list');
		$view->set('form', $form);
		$this->template->set('content', $view);
		$this->template->set('submenu', 'list_'.$factory);
	}
	
	public function action_export() {
		$factory = $this->request->param('factory');
		
		$this->checkPrivilege('factory_'.$factory.'_list');
		
		$form = new Model_Factory_SearchForm($factory);
		$form->populate($_REQUEST);
		
		$form->exportAction();
		
		$this->auto_render = FALSE;
	}
	
	public function action_shipping() {
		$factory = $this->request->param('factory');
		$id = $this->request->param('id');
		
		$this->checkPrivilege('factory_'.$factory.'_list', Model_RoleMatrix::PERMISSION_WRITE);
		
		$form = new Model_Factory_ShippingForm($factory, $id);
		$form->populate($_POST);
		
		$form->retrieveAction();

		// Display
		$view = View::factory('factory/shipping');
		$view->set('form', $form);
		$this->template->set('content', $view);
		$this->template->set('submenu', 'list_'.$factory);
	}
	
	public function action_shipping_save() {
		$factory = $this->request->param('factory');
		
		$this->checkPrivilege('factory_'.$factory.'_list', Model_RoleMatrix::PERMISSION_WRITE);
		
		$form = new Model_Factory_ShippingForm($factory);
		$form->populate($_POST);
		
		if ($form->processSaveAction()) {
			if ($form->is_accept == 1) {
				$this->template->set('success', __('save.success'));
			} else {
				$this->template->set('success', __('transfer.translator'));
				$this->action_list();
				return;
			}
		} else {
			$this->template->set('errors', $form->errors);
		}

		// Display
		$view = View::factory('factory/shipping');
		$view->set('form', $form);
		$this->template->set('content', $view);
		$this->template->set('submenu', 'shipping');
		$this->template->set('submenu', 'list_'.$factory);
	}
	
	public function action_entry() {
		$factory = $this->request->param('factory');
		$id = $this->request->param('id');
		
		$this->checkPrivilege('factory_'.$factory.'_list', Model_RoleMatrix::PERMISSION_WRITE);
		
		$form = new Model_Factory_EntryForm($factory, $id);
		$form->populate($_POST);
	
		if (HTTP_Request::POST == $this->request->method()) {
			if ($form->processSaveAction()) {
				$this->template->set('success', __('save.success'));
			} else {
				$this->template->set('errors', $form->errors);
			}
		} else {
			$form->initData();
		}
		
		// Display
		$view = View::factory('factory/entry');
		$view->set('form', $form);
		$this->template->set('content', $view);
		$this->template->set('submenu', 'list_'.$factory);
	}
	
/* ******************************************************
 * Gift
*******************************************************/
	public function action_gift_list($inputFactory=NULL) {
		if ($inputFactory == NULL) {
			$factory = $this->request->param('factory');
		} else {
			$factory = $inputFactory;
		}
		
		$this->checkPrivilege('factory_'.$factory.'_gift');
		
		$form = new Model_Factory_GiftSearchForm($factory);
		$form->populate($_POST);
		
		$form->process();
		
		// Display
		$view = View::factory('factory/gift_list');
		$view->set('form', $form);
		$this->template->set('content', $view);
		$this->template->set('submenu', 'gift_'.$factory);
	}
	
	public function action_gift_add() {
		$factory = $this->request->param('factory');
		
		$this->checkPrivilege('factory_'.$factory.'_gift', Model_RoleMatrix::PERMISSION_WRITE);
		
		$form = new Model_Factory_GiftForm($factory);
		$form->populate($_POST);
		
		$form->init();
		
		// Display
		$view = View::factory('factory/gift_maint');
		$view->set('form', $form);
		$this->template->set('content', $view);
		$this->template->set('submenu', 'gift_'.$factory);
	}
	
	public function action_gift_edit() {
		$factory = $this->request->param('factory');
		$gift_id = $this->request->param('id');
	
		$this->checkPrivilege('factory_'.$factory.'_gift', Model_RoleMatrix::PERMISSION_WRITE);
	
		$form = new Model_Factory_GiftForm($factory);
		
		if ($form->retrieve($gift_id)) {
			// Display
			$view = View::factory('factory/gift_maint');
			$view->set('form', $form);
			$this->template->set('content', $view);
			$this->template->set('submenu', 'gift_'.$factory);
		} else {
			$this->template->set('content', "Gift not found");
		}
	}
	
	public function action_gift_save() {
		$factory = $this->request->param('factory');
		
		$this->checkPrivilege('factory_'.$factory.'_gift', Model_RoleMatrix::PERMISSION_WRITE);
		
		$form = new Model_Factory_GiftForm($factory);
		$form->populate($_POST);
	
		if ($form->saveAction()) {
			$this->template->set('success', __('save.success'));
		} else {
			$this->template->set('errors', $form->errors);
		}
		
		// Display
		$view = View::factory('factory/gift_maint');
		$view->set('form', $form);
		$this->template->set('content', $view);
		$this->template->set('submenu', 'gift_'.$factory);
	}
	
	public function action_search_container_no() {
		$keyword = $_GET['term'];
	
		$products = ORM::factory('containerNo')
					->where('container_no', 'like', '%'.$keyword.'%')
					->order_by('container_no')
					->find_all();
	
		$result = array();
		foreach ($products as $product) {
			$item['label'] = $product->container_no;
			$item['value'] = $product->container_no;
			$result[] = $item;
		}
	
		echo json_encode($result);
		
		$this->auto_render = false; // Don't render template
	}
	
	public function action_search_gift_container_no() {
		$keyword = $_GET['term'];
	
		$products = ORM::factory('gift')
		->where('container_no', 'like', '%'.$keyword.'%')
		->order_by('container_no')
		->distinct(true)
		->find_all();
	
		$result = array();
		foreach ($products as $product) {
			$item['label'] = $product->container_no;
			$item['value'] = $product->container_no;
			$result[] = $item;
		}
	
		echo json_encode($result);
	
		$this->auto_render = false; // Don't render template
	}
}