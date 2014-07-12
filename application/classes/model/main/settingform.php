<?php
class Model_Main_SettingForm {
	public $current_password;
	public $new_password;
	public $new_password2;
	
	public $errors;
	
	public function populate($post) {
		$this->current_password = isset($post['current_password']) ? $post['current_password'] : NULL;
		$this->new_password = isset($post['new_password']) ? $post['new_password'] : NULL;
		$this->new_password2 = isset($post['new_password2']) ? $post['new_password2'] : NULL;
	}
	
	public function changePasswordAction() {
		$user = Auth::instance()->get_user();
		
		$userModel = ORM::factory('user')
						->where('username', '=', $user->username)
						->where('password', '=', DB::expr('password(\''.$this->current_password.'\')'))
						->find();
		
		if (!$userModel->loaded()) {
			// Wrong current password
			$this->errors[] = 'Current password is wrong.';
			return false;
		}
		
		// Check new password = re-enter password
		if ($this->new_password != $this->new_password2) {
			$this->errors[] = 'New password and re-enter password are not the same.';
			return false;
		}
		
		try {
			$userModel->password = DB::expr('password(\''.$this->new_password.'\')');
			$userModel->save();
		} catch (Exception $e) {
			$db->rollback();
			$this->errors[] = $e->getMessage();
			return false;
		}
		
		return true;
	}
}