<?php
class Model_Admin_RoleMatrixForm {
	public static $pages = array(
			'sales_order_list'=>'Sales > 訂單list',
			'sales_order_return'=>'Sales > 客人退貨',
			'sales_shipping_fee'=>'Sales > 輸入經費',
			'sales_deposit_settlement'=>'Sales > Deposit入金',
			'sales_customer'=>'Sales > 客戶列表',
			'kaitostaff_list'=>'大步哥 > 分貨',
			'auditor_gz_list'=>'Auditor > 工場分貨確認',
			'auditor_ben_list'=>'Auditor > Ben分貨確認',
			'auditor_jp_list'=>'Auditor > 國內分貨確認',
			'translator_gz_list'=>'高原 > 工場翻譯',
			'translator_ben_list'=>'高原 > Ben翻譯',
			'factory_gz_list'=>'工場 > gz工場List',
			'factory_gz_gift'=>'工場 > gz工場Gift',
			'factory_ben_list'=>'工場 > ben工場List',
			'factory_ben_gift'=>'工場 > ben工場Gift',
			'warehouse_list'=>'倉管員 > 貨倉List',
			'warehouse_gift_list'=>'倉管員 ＞ Gift List',
			'warehouse_container_return_list'=>'倉管員 > 返品、戻す商品',
			'warehouse_kaito_product_list'=>'倉管員 > 海渡商品',
			'warehouse_order_return_confirm'=>'倉管員 > 客人退貨確認',
			'accountant_delivery_note'=>'入金管理 > 納品書打印',
			'accountant_invoice'=>'入金管理 > 請求書打印',
			'accountant_shipping_fee_delivery_note'=>'入金管理 > 輸入経費請求書打印',
			'accountant_deposit_settlement'=>'入金管理 > deposit 確認',
			'accountant_shipping_fee_settlement'=>'入金管理 > 經費入金確認',
			'accountant_invoice_settlement'=>'入金管理 > invoice入金確認',
			'admin_staff'=>'Admin > staff 管理',
			'admin_role_matrix'=>'Admin > 權限管理',
			'admin_supplier'=>'Admin > 供應商管理',
			'admin_bank'=>'Admin > Bank Account管理',
			'admin_profit'=>'Admin > 1.5 管理',
			'admin_profit_config'=>'Admin > Profit設定',
			'admin_rate'=>'Admin > rate 管理',
	);
	
	public $roles;
	public $permissions;
	
	public $errors;
	
	public function populate($post) {
		$this->permissions = isset($post['permissions']) ? $post['permissions'] : NULL;
	}
	
	public function processInitAction() {
		$this->roles = ORM::factory('role')
						->order_by('role_name')
						->find_all();
		
		$roleMatrixes = ORM::factory('roleMatrix')
						->find_all();
		
		$this->permissions = array();
		foreach ($roleMatrixes as $roleMatrix) {
			$this->permissions[$roleMatrix->role_code][$roleMatrix->page] = $roleMatrix->permission;
		}
	}
	
	public function processSaveAction() {
		$result = $this->save();
		$this->processInitAction();
		
		return $result;
	}
	
	private function save() {
		$roleMatrixes = array();
		foreach ($this->permissions as $role_code=>$pages) {
			foreach ($pages as $page=>$permission) {
				if ($permission != '') {
					$roleMatrix = new Model_RoleMatrix();
					$roleMatrix->role_code = $role_code;
					$roleMatrix->category = substr($page, 0, strpos($page, '_'));
					$roleMatrix->page = $page;
					$roleMatrix->permission = $permission;
					$roleMatrixes[] = $roleMatrix;
				}
			}
		}
		
		$db = Database::instance();
		$db->begin();
		
		try {
			$query = DB::query(Database::DELETE, 'truncate role_matrix');
			$query->execute();
			
			foreach ($roleMatrixes as $roleMatrix) {
				$roleMatrix->save();
			}
		} catch (Exception $e) {
			$db->rollback();
			$this->errors[] = $e->getMessage();
			return false;
		}
		
		$db->commit();
		
		return true;
	}
}