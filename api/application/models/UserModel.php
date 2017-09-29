<?php

class UserModel extends BaseModel {

	public $table = "users";
	public $soft_delete = true;

	public $hidden_fields = ["pw_hash",'api_key','deleted'];

	private $auth_level_mapping = [
		"User",
		"Admin",
		"Root"
	];

	//check user's password is valid
	public function authenticate($username, $password)
	{
		$user = $this->db
			->where("email", $username)
			->or_where("username",$username)
			->get("users")
			->row();

		if ($user) {
			if (password_verify($password, $user->pw_hash)) {
				return $user;
			}
			else {
				return false;
			}
		}
		else {
			return false;
		}
		
	}

	//accepts an array of user_levels that can get past this gate
	public function accessAllowed(array $req_account_level)
	{
		
		//check for API key
		if (isset($_REQUEST['key']))
			$user = $this->db->get_where("users",["api_key" => $_REQUEST['key']])->row();
		else {
			$headers = getallheaders();

			if (isset($headers['x-api-key']))  {
				$user = $this->db->get_where("users",["api_key" => $headers['x-api-key']])->row();
			}
			else {
				$user = false;
			}
		}

		if (!$user) 
			return false;
		else {
			if (in_array($user->user_level, $req_account_level)) 
				return true;
			else
				return false;
		}
	}

	public function getUserByAuth() {
		$user = false;
		//check for API key
		if (isset($_REQUEST['key']))
			$user = $this->db->get_where("users",["api_key" => $_REQUEST['key']])->row();
		else {
			$headers = getallheaders();

			if (isset($headers['x-api-key']))  {
				$user = $this->db->get_where("users",["api_key" => $headers['x-api-key']])->row();
			}
		}

		return $user;
	}

	public function createPasswordHash($plaintext)
	{
		return password_hash($plaintext, PASSWORD_BCRYPT);
	}

	public function changePassword($user_id, $new_password)
	{
		//hash new password
		$hash = $this->createPasswordHash($new_password);
		
		// make sure password meets minimum characters
		if (strlen($new_password) >= PSW_MIN_LENGTH) {
			$result = $this->db->set([
							'pw_hash' => $hash
						])
						->where('id', $user_id)
						->update($this->table);

			return true;
		}
		else {
			return "Password must be at least ".PSW_MIN_LENGTH." characters.";
		}
	}

	public function sendEmail($email, $view, $view_data, $subject,$from=null)
	{
		$this->load->library('email');
		$config['mailtype'] = 'html';
		$this->email->initialize($config);

		if ($from == null) {
			$from_email = 'noreply@'.DOMAIN_NAME;
		}
		else {
			$from_email = $from;
		}

		$this->email->from($from_email)
			->to($email)
			->subject($subject)
			->message($this->load->view($view,$view_data,true))
			->send();

		return true;
	}

	public function generateUserToken($user_id, $expiry_hrs = 2)
	{
		$login_token = hash('sha256', mt_rand(1000000,99999999).time());
		$token_expiry = date("Y-m-d H:i:s", time()+(60*60*$expiry_hrs));

		//save the token
		$this->db->insert("user_tokens",[
			'token' => $login_token,
			'issued' => date("Y-m-d H:i:s"),
			'expiry' => $token_expiry,
			'user_id' => $user_id
		]);

		return [
			'token' => $login_token,
			'expiry' => $token_expiry
		];
	}

	public function validateToken($token)
	{
		//validate token
		$now = date("Y-m-d H:i:s");
		$token_record = $this->db->get_where("user_tokens",['token' => $token, 'expiry >' => $now])->row();

		$booTokenValid = false;
		if ($token_record) {
			$user_details = $this->db->get_where('users',['id' => $token_record->user_id])->row();

			if ($user_details) {
				$booTokenValid = true;
			}
		}

		return $booTokenValid;
	}

}