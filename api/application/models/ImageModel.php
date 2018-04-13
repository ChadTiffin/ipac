<?php

class ImageModel extends CI_Model {

	public function generateUploadToken($upload_id,$expiry_mins = 60)
	{
		$token = hash('sha256', mt_rand(1000000,99999999).time());
		$token_expiry = date("Y-m-d H:i:s", time()+(60*$expiry_mins));

		//save the token
		$this->db->insert("upload_tokens",[
			'token' => $token,
			'issued' => date("Y-m-d H:i:s"),
			'expiry' => $token_expiry,
			'upload_id' => $upload_id
		]);

		return [
			'token' => $token,
			'expiry' => $token_expiry
		];
	}
}