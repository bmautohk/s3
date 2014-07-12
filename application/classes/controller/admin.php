<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Admin extends Controller_CustomTemplate {
	
	public $template = 'template/main';
	
	public function action_index() {
		if ($this->hasPrivilege('admin_staff')) {
			$this->action_staff();
		} else if ($this->hasPrivilege('admin_role_matrix')) {
			$this->action_role_matrix();
		} else if ($this->hasPrivilege('admin_supplier')) {
			$this->action_supplier();
		} else if ($this->hasPrivilege('admin_bank')) {
			$this->action_bank();
		}else if ($this->hasPrivilege('admin_profit')) {
			$this->action_profit();
		} else if ($this->hasPrivilege('admin_profit_config')) {
			$this->action_profit_config();
		} else if ($this->hasPrivilege('admin_rate')) {
			$this->action_rate();
		} else {
			$this->request->redirect('main/no_permission');
		}
	}
	
/* ******************************************************
 * Staff
 *******************************************************/
	public function action_staff() {
		$this->checkPrivilege('admin_staff');
		
		$form = new Model_Admin_UserForm();
		$form->searchAction();
		
		$view = View::factory('admin/staff_list');
		$view->set('form', $form);
		$this->template->set('content', $view);
		$this->template->set('submenu', 'staff');
	}
	
	public function action_staff_new() {
		$this->checkPrivilege('admin_staff', Model_RoleMatrix::PERMISSION_WRITE);
		
		$form = new Model_Admin_UserForm();
		$form->action = Model_Admin_UserForm::ACTION_ADD;
		
		$view = View::factory('admin/staff_maint');
		$view->set('form', $form);
		$this->template->set('content', $view);
		$this->template->set('submenu', 'staff');
	}
	
	public function action_staff_edit() {
		$this->checkPrivilege('admin_staff', Model_RoleMatrix::PERMISSION_WRITE);
		
		$id = $this->request->param('id');
		
		$form = new Model_Admin_UserForm();
		$form->action = Model_Admin_UserForm::ACTION_EDIT;
		$form->retrieve($id);
		
		$view = View::factory('admin/staff_maint');
		$view->set('form', $form);
		$this->template->set('content', $view);
		$this->template->set('submenu', 'staff');
	}
	
	public function action_staff_delete() {
		$this->checkPrivilege('admin_staff', Model_RoleMatrix::PERMISSION_WRITE);

		$form = new Model_Admin_UserForm();
		$form->populate($_POST);
		if ($form->deleteAction()) {
			$this->template->set('success', __('delete.success'));
		} else {
			$this->template->set('errors', $form->errors);
		}
	
		$view = View::factory('admin/staff_list');
		$view->set('form', $form);
		$this->template->set('content', $view);
		$this->template->set('submenu', 'staff');
	}
	
	public function action_staff_save() {
		$this->checkPrivilege('admin_staff', Model_RoleMatrix::PERMISSION_WRITE);
		
		$form = new Model_Admin_UserForm();
		$form->populate($_POST);
		
		if ($form->action == Model_Admin_UserForm::ACTION_ADD) {
			// Create new user
			if ($form->addAction()) {
				$this->template->set('success', __('save.success'));
				$view = View::factory('admin/staff_list');
			} else {
				$this->template->set('errors', $form->errors);
				$view = View::factory('admin/staff_maint');
			}
		} else if ($form->action == Model_Admin_UserForm::ACTION_EDIT) {
			// Update existing user
			if ($form->editAction()) {
				$this->template->set('success', __('save.success'));
				$view = View::factory('admin/staff_list');
			} else {
				$this->template->set('errors', $form->errors);
				$view = View::factory('admin/staff_maint');
			}
		} else {
			$form->searchAction();
			$view = View::factory('admin/staff_list');
		}
		
		$view->set('form', $form);
		$this->template->set('content', $view);
		$this->template->set('submenu', 'staff');
	}
	
/* ******************************************************
 * Role Matrix
*******************************************************/
	public function action_role_matrix() {
		$form = new Model_Admin_RoleMatrixForm();
		$form->populate($_POST);
		
		if (HTTP_Request::POST == $this->request->method()) {
			$this->checkPrivilege('admin_role_matrix', Model_RoleMatrix::PERMISSION_WRITE);
			
			if ($form->processSaveAction()) {
				$this->template->set('success', __('save.success'));
			} else {
				$this->template->set('errors', $form->errors);
			}
		} else {
			$this->checkPrivilege('admin_role_matrix');
			$form->processInitAction();
		}
	
		$view = View::factory('admin/role_matrix');
		$view->set('form', $form);
		$this->template->set('content', $view);
		$this->template->set('submenu', 'role_matrix');
	}
	
/* ******************************************************
 * Supplier
*******************************************************/
	public function action_supplier() {
		$action = isset($_POST['action']) ? $_POST['action'] : NULL;
		$model = new Model_Supplier();
	
		if ($action == 'add') {
			$this->checkPrivilege('admin_supplier', Model_RoleMatrix::PERMISSION_WRITE);
			try {
				$model->values($_POST);
				$model->save();
				
				$this->template->set('success', __('save.success'));
				
				// Clear form
				$model = new Model_Supplier();
				
			} catch (ORM_Validation_Exception $e) {
				$errors = $e->errors('admin');
				$this->template->set('errors', $errors);
			}
		} else {
			$this->checkPrivilege('admin_supplier');
		}
	
		// Retrieve all suppliers
		$suppliers = ORM::factory('supplier')->find_all();
	
		$view = View::factory('admin/supplier');
		$view->set('model', $model);
		$view->set('suppliers', $suppliers);
		$this->template->set('content', $view);
		$this->template->set('submenu', 'supplier');
	}

/* ******************************************************
 * Bank
*******************************************************/
	public function action_bank() {
		$action = isset($_POST['action']) ? $_POST['action'] : NULL;
		$form = new Model_Admin_BankForm();
		$form->populate($_POST);
		
		if (HTTP_Request::POST == $this->request->method()) {
			$this->checkPrivilege('admin_bank', Model_RoleMatrix::PERMISSION_WRITE);
			if ($form->saveAction()) {
				$this->template->set('success', __('save.success'));
			} else {
				$this->template->set('errors', $form->errors);
			}
		} else {
			$id = $this->request->param('id');
			if (isset($id)) {
				$this->checkPrivilege('admin_bank', Model_RoleMatrix::PERMISSION_WRITE);
				$form->retrieve($id);
			} else {
				$this->checkPrivilege('admin_bank');
				$form->searchAction();
			}
		}

		$view = View::factory('admin/bank');
		$view->set('form', $form);
		$this->template->set('content', $view);
		$this->template->set('submenu', 'bank');
	}
	
/* ******************************************************
 * Profit Config 1.5
*******************************************************/
	public function action_profit() {
		$this->checkPrivilege('admin_profit');
	
		$form = new Model_Admin_ProfitConfig15Form();
		$form->searchAction();
	
		$view = View::factory('admin/profit');
		$view->set('form', $form);
		$this->template->set('content', $view);
		$this->template->set('submenu', 'profit');
	}
	
	public function action_profit_edit() {
		$this->checkPrivilege('admin_profit', Model_RoleMatrix::PERMISSION_WRITE);
	
		$code = $this->request->param('id');
	
		$form = new Model_Admin_ProfitConfig15Form();
		if (!$form->retrieve($code)) {
			$this->request->redirect('main/record_not_found');
			
		}
		
		$view = View::factory('admin/profit_maint');
		$view->set('form', $form);
		$this->template->set('content', $view);
		$this->template->set('submenu', 'profit');
	}
	
	public function action_profit_save() {
		$this->checkPrivilege('admin_profit', Model_RoleMatrix::PERMISSION_WRITE);
	
		$form = new Model_Admin_ProfitConfig15Form();
		$form->populate($_POST);

		// Update existing profit config
		if ($form->editAction()) {
			$this->template->set('success', __('save.success'));
			$view = View::factory('admin/profit');
		} else {
			$this->template->set('errors', $form->errors);
			$view = View::factory('admin/profit_maint');
		}
		
		$view->set('form', $form);
		$this->template->set('content', $view);
		$this->template->set('submenu', 'profit');
	}
	
/* ******************************************************
 * Profit Config
*******************************************************/
	public function action_profit_config() {
		$form = new Model_Admin_ProfitConfigForm();
		$form->populate($_POST);
	
		$isShowPrintButton = false;
		if ($form->action == 'save') {
			$this->checkPrivilege('admin_profit_config', Model_RoleMatrix::PERMISSION_WRITE);
			if ($form->processSaveAction()) {
				$this->template->set('success', __('save.success'));
				$isShowPrintButton = true;
			} else {
				$this->template->set('errors', $form->errors);
			}
		} else {
			$this->checkPrivilege('admin_profit_config');
			$form->processSearchAction();
		}
	
		// Display
		$view = View::factory('admin/profit_config');
		$view->set('form', $form);
		$this->template->set('content', $view);
		$this->template->set('submenu', 'profit_config');
	}
	
/* ******************************************************
 * Rate
*******************************************************/
	public function action_rate() {
		$action = isset($_POST['action']) ? $_POST['action'] : NULL;
		
		if ($action != NULL && $action == 'save') {
			$this->checkPrivilege('admin_rate', Model_RoleMatrix::PERMISSION_WRITE);
			
			$form = new Model_Rate();
			$form->values($_POST);

			// Save to DB
			try {
				$form->save();
				
				// Clear form
				$form = new Model_Rate();
				
				$this->template->set('success', __('save.success'));
			} catch (ORM_Validation_Exception $e) {
				$errors = $e->errors('admin');
				$this->template->set('errors', $errors);
			}
		} else {
			$this->checkPrivilege('admin_rate');
			$form = new Model_Rate();
		}
		
		// Find all existing rates
		$rates = ORM::factory('rate')->find_all();
		
		$view = View::factory('admin/rate');
		$view->set('form', $form);
		$view->set('rates', $rates);
		$this->template->set('content', $view);
		$this->template->set('submenu', 'rate');
	}
	
	public function action_rate_delete() {
		$this->checkPrivilege('admin_rate', Model_RoleMatrix::PERMISSION_WRITE);
		
		$id = $this->request->param('id');
		
		try {
			ORM::factory('rate')->where('id', '=', $id)->find()->delete();
			$this->template->set('success', __('delete.success'));
		} catch (Kohana_Exception $e) {
			$this->template->set('errors', array($e->getMessage()));
		}
		
		$this->action_rate();
	}
}