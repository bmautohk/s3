<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Auditor extends Controller_CustomTemplate {
	
	public $template = 'template/main';

	public function action_index() {
		if ($this->hasPrivilege('auditor_gz_list')) {
			$this->list_factory('gz');
		} else if ($this->hasPrivilege('auditor_ben_list')) {
			$this->list_factory('ben');
		} else if ($this->hasPrivilege('auditor_jp_list')) {
			$this->list_jp();
		} else {
			$this->request->redirect('main/no_permission');
		}
	}
	
	public function action_list() {
		$factory = $this->request->param('factory');
		
		if ($factory == GlobalConstant::FORM_FACTORY_JP) {
			$this->list_jp();
		} else {
			$this->list_factory($factory);
		}
	}
	
	public function action_export() {
		$factory = $this->request->param('factory');
		
		if ($factory == GlobalConstant::FORM_FACTORY_JP) {
			$this->export_jp();
		} else {
			$this->export_factory($factory);
		}
	}
	
	public function action_save() {
		$factory = $this->request->param('factory');
		
		if ($factory == GlobalConstant::FORM_FACTORY_JP) {
			$this->save_jp();
		} else {
			$this->save_factory($factory);
		}
	}
	
	private function export_factory($factory) {
		$this->checkPrivilege('auditor_'.$factory.'_list');
		
		$form = new Model_Auditor_FactoryOrderProductForm($factory);
		$form->populate($_REQUEST);
		
		$form->exportAction();
		
		$this->auto_render = FALSE;
	}
	
	private function export_jp() {
		$this->checkPrivilege('auditor_jp_list');
		
		$form = new Model_Auditor_JpOrderProductForm();
		$form->populate($_REQUEST);
		
		$form->exportAction();
		
		$this->auto_render = FALSE;
	}
	
	private function list_factory($factory) {
		$this->checkPrivilege('auditor_'.$factory.'_list');
		
		$form = new Model_Auditor_FactoryOrderProductForm($factory);
		$form->populate($_REQUEST);
		
		$form->searchAction();
		
		// Display
		$view = View::factory('auditor/auditor_product_list');
		$view->set('form', $form);
		$this->template->set('content', $view);
		$this->template->set('submenu', 'list_'.$factory);
	}
	
	private function save_factory($factory) {
		$this->checkPrivilege('auditor_'.$factory.'_list', Model_RoleMatrix::PERMISSION_WRITE);
		
		$form = new Model_Auditor_FactoryOrderProductForm($factory);
		$form->populate($_POST);
		
		if ($form->action == Model_Auditor_FactoryOrderProductForm::ACTION_GO_TO_TRANSLATOR) {
			if ($form->processGoToTranslatorAction()) {
				$this->template->set('success', __('transfer.translator'));
			} else {
				$this->template->set('errors', $form->errors);
			}
		} else if ($form->action == Model_Auditor_FactoryOrderProductForm::ACTION_BACK_TO_KAITOSTAFF) {
			if ($form->processBackToKaitostaffAction()) {
				$this->template->set('success', __('transfer.kaitostaff'));
			} else {
				$this->template->set('errors', $form->errors);
			}
		}
		
		// Display
		$view = View::factory('auditor/auditor_product_list');
		$view->set('form', $form);
		$this->template->set('content', $view);
		$this->template->set('submenu', 'list_'.$factory);
	}
	
	private function list_jp() {
		$this->checkPrivilege('auditor_jp_list');
		
		$form = new Model_Auditor_JpOrderProductForm();
		$form->populate($_REQUEST);
		
		$form->searchAction();
		
		// Display
		$view = View::factory('auditor/auditor_product_list');
		$view->set('form', $form);
		$this->template->set('content', $view);
		$this->template->set('submenu', 'list_jp');
	}
	
	private function save_jp() {
		$this->checkPrivilege('auditor_jp_list', Model_RoleMatrix::PERMISSION_WRITE);
		
		$form = new Model_Auditor_JpOrderProductForm();
		$form->populate($_POST);
		
		if ($form->action == Model_Auditor_JpOrderProductForm::ACTION_GO_TO_ACCOUNTANT) {
			if ($form->processGoToAccountantAction()) {
				$this->template->set('success', __('transfer.accountant'));
			} else {
				$this->template->set('errors', $form->errors);
			}
		} else if ($form->action == Model_Auditor_JpOrderProductForm::ACTION_BACK_TO_KAITOSTAFF) {
			if ($form->processBackToKaitostaffAction()) {
				$this->template->set('success', __('transfer.kaitostaff'));
			} else {
				$this->template->set('errors', $form->errors);
			}
		} else {
			$form->searchAction();
		}

		// Display
		$view = View::factory('auditor/auditor_product_list');
		$view->set('form', $form);
		$this->template->set('content', $view);
		$this->template->set('submenu', 'list_jp');
	}

}
