<?php defined('SYSPATH') or die('No direct access allowed.');

class Auth_ORM2 extends Kohana_Auth_ORM {
	
	protected function _login($username, $password, $remember) {
		$user = ORM::factory('user')
					->where('username', '=', $username)
					->where('password', '=', DB::expr('password(\''.$password.'\')'))
					->find();
	
		if (!$user->loaded()) {
			// Login failed
			return FALSE;
		}
		
		// Update user's last login date
		$user->last_login_date = DB::expr('CURRENT_TIMESTAMP');
		$user->update();
		
		// Finish the login
		$authUser = new Model_Auth_User();
		$authUser->username = $user->username;
		$authUser->role_code = $user->role_code;
		$this->complete_login($authUser);
		
		return TRUE;
		
		/* 
		// If the passwords match, perform a login
		if ($user->has('roles', ORM::factory('role', array('name' => 'login'))) AND $user->password === $password)
		{
			if ($remember === TRUE)
			{
				// Token data
				$data = array(
						'user_id'    => $user->pk(),
						'expires'    => time() + $this->_config['lifetime'],
						'user_agent' => sha1(Request::$user_agent),
				);
	
				// Create a new autologin token
				$token = ORM::factory('user_token')
				->values($data)
				->create();
	
				// Set the autologin cookie
				Cookie::set('authautologin', $token->token, $this->_config['lifetime']);
			}
	
			// Finish the login
			$this->complete_login($user);
	
			return TRUE;
		} */
	}
	
	protected function complete_login($user)
	{
		$user->complete_login();
		
		// Regenerate session_id
		$this->_session->regenerate();

		// Store username in session
		$this->_session->set($this->_config['session_key'], $user);
		
		return TRUE;
	}
}