<?php

class Auth extends Base_Controller {

	public $user_model = "UserModel";
	public $password_min_length = PSW_MIN_LENGTH;

	public function __construct()
	{
		parent::__construct();
	
		$this->load->model($this->user_model);
	}


	public function login()
	{
		$username = $this->input->post("email");
		$password = $this->input->post("password");

		$usermodel = $this->user_model;
		$user_details = $this->$usermodel->authenticate($username, $password);

		if ($user_details && $password != "") {
			//USER IS AUTHENTICATED

			//check they're not suspended
			if ($user_details->user_level == "Suspended") {
				$response = [
					'status' => 'failed',
					'msg' => "Your account has been suspended by an Administrator"
				];
			}
			else {

				//create new API key
				//$key = hash("sha256", mt_rand(10000,1000000000).time().$user_details->id);

				//update last login
				$q = "UPDATE ".$this->$usermodel->table." SET last_login=NOW() WHERE id=?";
				$r = $this->db->query($q, array($user_details->id));

				unset($user_details->pw_hash);
				unset($user_details->allow_access);
				unset($user_details->deleted);

				$response = [
					'status' => "success",
					'userType' => $user_details->user_level,
					"apiKey" => $user_details->api_key,
					'userDetails' => $user_details,
					'msg' => "Login successful."
				];
			}
		}
		else {
			$response = [
				'status' => "denied",
				'msg' => "Login attempt failed."
			];
		}

		echo json_encode($response);
	}

	public function logout() {
		$usermodel = $this->user_model;

		echo json_encode([
			"status" => "success",
			"msg" => "You have been logged out"
		]);
	}

	public function new_api_key() {
		$this->gatekeep(["Admin","Root"]);

		$user_id = $this->input->post("user_id");
		$key = hash("sha256", mt_rand(10000,1000000000).time().$user_id);

		$this->db->where("id",$user_id)
			->set("api_key",$key)
			->update("users");

		echo json_encode([
			"status" => "success",
			"newKey" => $key
		]);
	}

	public function password($request)
	{
		$usermodel = $this->user_model;

		if ($request == "reset") {

			$post = $this->input->post();

			$this->load->library('form_validation');

			$this->form_validation
				->set_rules('password','Password','required|min_length['.$this->password_min_length."]")
				->set_error_delimiters('', ' ');

			if ($this->form_validation->run() == false) {

				$response = [
					'status' => "failed",
					'msg' => validation_errors()
				];
			}
			elseif ($post['confirm'] != $post['password']) {
				$response = [
					'status' => "failed",
					'msg' => "Your password doesn't match the confirmation field"
				];
			}
			else {
				//validate token
				$booTokenValid = $this->user_model->validateToken($post['token']);

				if ($booTokenValid) {
					//check length

					$result = $this->$usermodel->changePassword($user_details->id,$post['confirm']);

					//delete token
					$r = $this->db->where('id',$token_record->id)
						->delete("user_tokens");

					$response = [
						'status' => "success",
						'msg' => "Password changed successfully."
					];
				}
				else {
					$response = [
						'status' => "failed",
						'msg' => 'Invalid token. Please go initiate another password reset request.'
					];
				}
			}

		}
		elseif ($request == "reset-request") {
			$post = $this->input->post();

			//find user by email
			$user = $this->db->get_where($this->$usermodel->table, ['email' => $post['email']])->row();

			if ($user) {
				$token = $this->$usermodel->generateUserToken($user->id,2);

				$this->$usermodel->sendEmail($user->email, 'emails/password_reset', $token, "Password Reset Request for ".APP_NAME);

			}
			$response = [
				'status' => "success",
				"msg" => "If we have your email on file we have sent you password instructions, which you should receive within 15 minutes."
			];
		}
		elseif ($request == 'change') {
			$this->gatekeep(["User","Admin","Root"]);

			$post = $this->input->post();

			if ($post['new-password'] != $post['confirm-password']) {
				$response = [
					'status' => "failed",
					'msg' => "Passwords don't match."
				];
			}
			elseif (strlen($post['new-password']) < PSW_MIN_LENGTH) {
				$response = [
					'status' => "failed",
					'msg' => "Password has to be at least ".PSW_MIN_LENGTH." characters long."
				];
			}
			else {
				//validate old password
				$headers = getallheaders();

				$user_id = 0;
				$user_email = "";
				if (isset($headers['x-api-key']))  {
					$user = $this->db->get_where("users",["api_key" => $headers['x-api-key']])->row();
					$user_id = $user->id;
					$user_email = $user->email;
				}

				if ($this->$usermodel->authenticate($user_email,$post['password'])) {
					$result = $this->$usermodel->changePassword($user_id,$post['new-password']);

					$response = [
						'status' => "success",
						'msg' => "Password changed successfully."
					];
				}
				else {
					$response = [
						'status' => "failed",
						'msg' => 'Password incorrect.'
					];
				}
			}
		}

		echo json_encode($response);
	}

}
