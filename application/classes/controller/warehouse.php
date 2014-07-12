<?php
class Controller_Warehouse extends Controller_CustomTemplate {

	public $template = 'template/main';

	public function action_index() {
		if ($this->hasPrivilege('warehouse_list')) {
			$this->action_list();
		} else if ($this->hasPrivilege('warehouse_ingood')) {
			$this->action_ingood();
		} else {
			$this->request->redirect('main/no_permission');
		}
	}
	
/* ***************************************************************************************************************
 * 貨倉List
 ****************************************************************************************************************/
	public function action_list() {
		$this->checkPrivilege('warehouse_list');
		
		$form = new Model_Warehouse_SearchForm();
		$form->populate($_REQUEST);
		
		$form->searchAction();
		
		// Display
		$view = View::factory('warehouse/list');
		$view->set('form', $form);
		$this->template->set('content', $view);
		$this->template->set('submenu', 'list');
	}
	
	public function action_export() {
		$this->checkPrivilege('warehouse_list');
		
		$form = new Model_Warehouse_SearchForm();
		$form->populate($_REQUEST);
	
		$form->exportAction();
	
		$this->auto_render = FALSE;
	}
	
	public function action_order_edit() {
		$this->checkPrivilege('warehouse_list', Model_RoleMatrix::PERMISSION_WRITE);
		
		$order_id = $this->request->param('id');
		
		$form = new Model_Warehouse_OrderEditForm();
		$form->retrieve($order_id);
		
		// Display
		$view = View::factory('warehouse/order_edit');
		$view->set('form', $form);
		$this->template->set('content', $view);
		$this->template->set('submenu', 'list');
	}
	
	public function action_order_edit_save() {
		$this->checkPrivilege('warehouse_list', Model_RoleMatrix::PERMISSION_WRITE);

		$form = new Model_Warehouse_OrderEditForm();
		$form->populate($_POST);
		
		if ($form->saveAction()) {
			$this->template->set('success', __('save.success'));
		} else {
			$this->template->set('errors', $form->errors);
		}
	
		// Display
		$view = View::factory('warehouse/order_edit');
		$view->set('form', $form);
		$this->template->set('content', $view);
		$this->template->set('submenu', 'list');
	}
	
	public function action_container_list() {
		$this->checkPrivilege('warehouse_list');
		
		$order_product_id = $this->request->param('id');
		
		$form = new Model_Warehouse_ContainerForm();
		$form->retrieve($order_product_id);
		
		// Display
		$view = View::factory('warehouse/container_list');
		$view->set('form', $form);
		$this->template->set('content', $view);
		$this->template->set('submenu', 'list');
	}
	
	public function action_container_return() {
		$this->checkPrivilege('warehouse_list');
		
		$container_id = $this->request->param('id');
		
		$form = new Model_Warehouse_ContainerForm();
		$form->initContainerReturnAction($container_id);
		
		// Display
		$view = View::factory('warehouse/container_return');
		$view->set('form', $form);
		$this->template->set('content', $view);
		$this->template->set('submenu', 'list');
	}
	
	public function action_container_return_save() {
		$this->checkPrivilege('warehouse_list', Model_RoleMatrix::PERMISSION_WRITE);
		
		$form = new Model_Warehouse_ContainerForm();
		$form->populate($_POST);
		
		if ($form->addContainerReturnAction()) {
			$this->template->set('success', __('save.success'));
		} else {
			$this->template->set('errors', $form->errors);
		}
		
		// Display
		$view = View::factory('warehouse/container_return');
		$view->set('form', $form);
		$this->template->set('content', $view);
		$this->template->set('submenu', 'list');
	}
	
	public function action_add_delivery_note() {
		$this->checkPrivilege('warehouse_list', Model_RoleMatrix::PERMISSION_WRITE);
		
		$container_id = $this->request->param('id');
		
		$form = new Model_Warehouse_ContainerForm();
		$form->populate($_POST);
		
		if ($form->addDeliveryNoteAction($container_id)) {
			$this->template->set('success', __('save.success'));
		} else {
			$this->template->set('errors', $form->errors);
		}
		
		// Display
		$view = View::factory('warehouse/container_list');
		$view->set('form', $form);
		$this->template->set('content', $view);
		$this->template->set('submenu', 'list');
	}
	
/* ***************************************************************************************************************
 * Borrow Product 
 ****************************************************************************************************************/
	public function action_borrow() {
		$this->checkPrivilege('warehouse_list', Model_RoleMatrix::PERMISSION_WRITE);
		
		$order_product_id = $this->request->param('id');
		
		$form = new Model_Warehouse_BorrowForm($order_product_id);
		$form->populate($_POST);
		
		$form->process();
		
		// Display
		$view = View::factory('warehouse/borrow');
		$view->set('form', $form);
		$this->template->set('content', $view);
		$this->template->set('submenu', 'list');
	}
	
	public function action_borrow_save() {
		$this->checkPrivilege('warehouse_list', Model_RoleMatrix::PERMISSION_WRITE);
		
		$order_product_id = $_POST['order_product_id'];
		
		$form = new Model_Warehouse_BorrowForm($order_product_id);
		$form->populate($_POST);
		
		$form->process();
		
		if (empty($form->errors)) {
			$this->template->set('success', __('save.success'));
		} else {
			$this->template->set('errors', $form->errors);
		}
		
		// Display
		$view = View::factory('warehouse/borrow');
		$view->set('form', $form);
		$this->template->set('content', $view);
		$this->template->set('submenu', 'list');
	}
	
/* ***************************************************************************************************************
 * Return Product
 ****************************************************************************************************************/
	public function action_return() {
		$this->checkPrivilege('warehouse_list', Model_RoleMatrix::PERMISSION_WRITE);
		
		$order_product_id = $this->request->param('id');
		
		$form = new Model_Warehouse_ReturnForm($order_product_id);
		$form->populate($_POST);
		
		$form->process();
		
		// Display
		$view = View::factory('warehouse/return');
		$view->set('form', $form);
		$this->template->set('content', $view);
		$this->template->set('submenu', 'list');
	}
	
	public function action_return_save() {
		$this->checkPrivilege('warehouse_list', Model_RoleMatrix::PERMISSION_WRITE);
		
		$order_product_id = $_POST['order_product_id'];
		
		$form = new Model_Warehouse_ReturnForm($order_product_id);
		$form->populate($_POST);
		
		$form->process();
		
		if (empty($form->errors)) {
			$this->template->set('success', __('save.success'));
		} else {
			$this->template->set('errors', $form->errors);
		}
		
		// Display
		$view = View::factory('warehouse/return');
		$view->set('form', $form);
		$this->template->set('content', $view);
		$this->template->set('submenu', 'list');
	}
	
/* ***************************************************************************************************************
 * Gift List
****************************************************************************************************************/
	public function action_gift_list() {
		$this->checkPrivilege('warehouse_gift_list');
	
		$form = new Model_Warehouse_GiftForm();
		$form->populate($_REQUEST);
	
		$form->searchAction();
	
		// Display
		$view = View::factory('warehouse/gift_list');
		$view->set('form', $form);
		$this->template->set('content', $view);
		$this->template->set('submenu', 'gift_list');
	}
	
	public function action_gift_export() {
		$this->checkPrivilege('warehouse_gift_list');
	
		$form = new Model_Warehouse_GiftForm();
		$form->populate($_REQUEST);
	
		$form->exportAction();
	
		$this->auto_render = FALSE;
	}
	
	/* public function action_gift_add_delivery_note() {
		$this->checkPrivilege('warehouse_list', Model_RoleMatrix::PERMISSION_WRITE);
	
		$gift_id = $this->request->param('id');

		$form = new Model_Warehouse_GiftForm();

		if ($form->addDeliveryNoteAction($gift_id)) {
			$this->template->set('success', __('save.success'));
		} else {
			$this->template->set('errors', $form->errors);
		}
	
		// Display
		$view = View::factory('warehouse/gift_list');
		$view->set('form', $form);
		$this->template->set('content', $view);
		$this->template->set('submenu', 'gift_list');
	} */
	
/* ***************************************************************************************************************
 * 入貨管理
 ****************************************************************************************************************/
	public function action_ingood() {
		$this->checkPrivilege('warehouse_ingood');
		
		$form = new Model_Warehouse_IngoodForm();
		$form->populate($_REQUEST);
		
		$form->searchAction();
		
		// Display
		$view = View::factory('warehouse/ingood');
		$view->set('form', $form);
		$this->template->set('content', $view);
		$this->template->set('submenu', 'ingood');
	}
	
	public function action_ingood_add_delivery_note() {
		$this->checkPrivilege('warehouse_ingood', Model_RoleMatrix::PERMISSION_WRITE);
		
		$container_id = $this->request->param('id');
		
		$form = new Model_Warehouse_IngoodForm();
		$form->populate($_POST);
		$form->container_id = $container_id;

		if ($form->addDeliveryNoteAction()) {
			$this->template->set('success', __('save.success'));
		} else {
			$this->template->set('errors', $form->errors);
		}
		
		// Display
		$view = View::factory('warehouse/ingood');
		$view->set('form', $form);
		$this->template->set('content', $view);
		$this->template->set('submenu', 'ingood');
	}
	
/* ***************************************************************************************************************
 * 返品、戻す商品
****************************************************************************************************************/
	public function action_container_return_list() {
		$this->checkPrivilege('warehouse_container_return_list');
		
		$form = new Model_Warehouse_ContainerReturnSearchForm();
		$form->populate($_REQUEST);
		
		$form->searchAction();
		
		// Display
		$view = View::factory('warehouse/container_return_list');
		$view->set('form', $form);
		$this->template->set('content', $view);
		$this->template->set('submenu', 'container_return_list');
	}
	
	public function action_container_return_export() {
		$this->checkPrivilege('warehouse_container_return_list');
	
		$form = new Model_Warehouse_ContainerReturnSearchForm();
		$form->populate($_REQUEST);
	
		$form->exportAction();
	
		$this->auto_render = FALSE;
	}
	
/* ***************************************************************************************************************
 * 海渡商品
 ****************************************************************************************************************/
	public function action_kaito_product_list() {
		$this->checkPrivilege('warehouse_kaito_product_list');
		
		$form = new Model_Warehouse_KaitoProductForm();
		$form->populate($_REQUEST);
		
		$form->searchAction();
		
		// Display
		$view = View::factory('warehouse/kaito_product_list');
		$view->set('form', $form);
		$this->template->set('content', $view);
		$this->template->set('submenu', 'kaito_product_list');
	}
	
	public function action_kaito_product_export() {
		$this->checkPrivilege('warehouse_kaito_product_list');
	
		$form = new Model_Warehouse_KaitoProductForm();
		$form->populate($_REQUEST);
	
		$form->exportAction();
	
		$this->auto_render = FALSE;
	}
	
/* ***************************************************************************************************************
 * 客人退貨確認
 ****************************************************************************************************************/
	public function action_order_return_confirm() {
		$form = new Model_Warehouse_OrderReturnConfirmForm();
		$form->populate($_REQUEST);
	
		if (HTTP_Request::POST == $this->request->method()) {
			$this->checkPrivilege('warehouse_order_return_confirm', Model_RoleMatrix::PERMISSION_WRITE); // Check privilege
				
			if ($form->action == 'confirm') {
				if ($form->processConfirmAction()) {
					$this->template->set('success', 'The record is confirmed.');
				} else {
					$this->template->set('errors', $form->errors);
				}
			} else if ($form->action == 'cancel') {
				if ($form->processCancelAction()) {
					$this->template->set('success', 'The record is cancelled.');
				} else {
					$this->template->set('errors', $form->errors);
				}
			}
				
			// Display
			$view = View::factory('warehouse/order_return_confirm_list');
			$view->set('form', $form);
			$this->template->set('content', $view);
			$this->template->set('submenu', 'order_return_confirm');
		} else {
			$this->checkPrivilege('warehouse_order_return_confirm'); // Check privilege
			$form->processSearchAction();
				
			// Display
			$view = View::factory('warehouse/order_return_confirm_list');
			$view->set('form', $form);
			$this->template->set('content', $view);
			$this->template->set('submenu', 'order_return_confirm');
		}
	}
}