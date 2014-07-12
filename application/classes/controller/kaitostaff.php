<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Kaitostaff extends Controller_CustomTemplate {
	
	public $template = 'template/main';
	
	public function action_index() {
		if ($this->hasPrivilege('kaitostaff_list')) {
			$this->action_list();
		} else {
			$this->request->redirect('main/no_permission');
		}
	}
	
	public function action_list() {
		$this->checkPrivilege('kaitostaff_list');
		
		$form = new Model_Kaitostaff_OrderProductForm();
		$form->populate($_REQUEST);
		
		$form->searchAction();
		
		// Display
		$view = View::factory('kaitostaff/list');
		$view->set('form', $form);
		$this->template->set('content', $view);
		$this->template->set('submenu', 'list');
	}
	
	public function action_export() {
		$this->checkPrivilege('kaitostaff_list');
		
		$form = new Model_Kaitostaff_OrderProductForm();
		$form->populate($_REQUEST);
		
		$form->exportAction();
		
		$this->auto_render = FALSE;
	}
	
	public function action_save() {
		$this->checkPrivilege('kaitostaff_list', Model_RoleMatrix::PERMISSION_WRITE);
		
		$form = new Model_Kaitostaff_OrderProductForm();
		$form->populate($_POST);
		
		$form->saveForm();
		
		if (empty($form->errors)) {
			// Success
			if ($form->action == Model_Kaitostaff_OrderProductForm::ACTION_GO_TO_AUDITOR) {
				$this->template->set('success', __('transfer.auditor'));
			} else if ($form->action == Model_Kaitostaff_OrderProductForm::ACTION_BACK_TO_SALES) {
				$this->template->set('success', __('transfer.sales'));
			}
		} else {
			$this->template->set('errors', $form->errors);
		}

		// Display
		$view = View::factory('kaitostaff/list');
		$view->set('form', $form);
		$this->template->set('content', $view);
		$this->template->set('submenu', 'list');
	}

}
