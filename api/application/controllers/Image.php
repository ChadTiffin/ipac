<?php

class Image extends Base_Controller {

	public $table = "images";

	public function __construct()
	{
		parent::__construct();
	}

	private function generateUploadToken($upload_id,$expiry_mins = 3)
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

	private function validateUploadToken($token, $upload_id) {
		//validate token
		$now = date("Y-m-d H:i:s");
		$token_record = $this->db->get_where("upload_tokens",['token' => $token, 'expiry >' => $now, 'upload_id' => $upload_id])->row();

		if ($token_record) 
			return true;
		else
			return false;
	} 

	public function upload($type = "audit-image") {
		$this->gatekeep(["User","Admin","Root"]);

		$config['upload_path']          = UPLOAD_FOLDER;
		$config['allowed_types']        = 'png|jpg|jpeg';
		$config['max_size']             = 6000;
		$config['file_name']            = time()."-".mt_rand(1000,9999);

		$this->load->library('upload', $config);

		if ( ! $this->upload->do_upload('file'))
		{

			echo json_encode([
				'status' => 'failed',
				'msg' => "There was a problem uploading",
				'errors' => $this->upload->display_errors()
			]);
		}
		else
		{
			$data = $this->upload->data();

			//resize settings
			$config = [];
			$config['image_library'] = 'GD2';
			$config['source_image'] = UPLOAD_FOLDER.$data['file_name'];
			$config['maintain_ratio'] = TRUE;

			$permissions = [];

			if ($type == "audit-image") {
				$permissions = ["Root","Admin","User"];

				$config['width']         = 640;
				$config['height']       = 480;
			}
			elseif ($type == "expense") {
				$permissions = ["Root","Admin","{{Owner}}"];

				$config['width']         = 1600;
				$config['height']       = 900;
			}

			$this->load->model("UserModel");
			$user = $this->UserModel->getUserByAuth();

			$this->db->insert("uploads",[
				'original_filename' => $data['orig_name'],
				'filename' => $data['file_name'],
				'date_uploaded' => date('Y-m-d H:i:s'),
				'uploaded_by' => $user->id,
				"permissions" => json_encode($permissions)
			]);

			//resize image

			$this->load->library('image_lib', $config);

			if ( ! $this->image_lib->resize())
			{
				$errors = $this->image_lib->display_errors();

				echo json_encode([
					'status' => 'failed',
					'message' => $errors,
				]);
			}
			else {
				echo json_encode([
					'status' => 'success',
					'filename' => $data['file_name'],
					"id" => $this->db->insert_id()
				]);
			}
			
		}
	}

	public function get_image_tokens() {
		$images = json_decode($this->input->get("images"));

		//clean out expired tokens
		$this->db->where("expiry <",date("Y-m-d H:i:s"))
			->delete("upload_tokens");

		$user = $this->UserModel->getUserByAuth();

		if ($images) {
			$response = [];
			foreach ($images as $image) {
				$image_record = $this->db->get_where("uploads",["filename" => $image])->row();

				//check permissions
				if ($image_record && json_decode($image_record->permissions)) {
					//check that the right permissions exist for this
					$perms = json_decode($image_record->permissions);

					//user type exists within permissions array or if {{Owner}} is in perms id, then user id matches id of uploaded_by
					if (in_array($user->user_level, $perms) || in_array("{{Owner}}", $perms) && $image_record->uploaded_by == $user->id) {
						$token = $this->generateUploadToken($image_record->id);

						$response[] = [
							"filename" => $image,
							"token" => $token['token']
						];
					}
				}
				else {
					//no token needed
					$response[] = [
						"filename" => $image
					];
				}
			}
			echo json_encode($response);
		}
		else {
			echo json_encode([
				'status' => "failed",
				"msg" => "No images provided"
			]);
		}
		
	}

	public function serve($filename) {

		$show_image = true;

		$image_record = $this->db->get_where("uploads",["filename" => $filename])->row();

		if (json_decode($image_record->permissions)) {
			//this image is restricted, check for token

			$token = $this->input->get("token");

			$tokenValid = $this->validateUploadToken($token, $image_record->id);

			if (!$tokenValid)
				$show_image = false;
		}

		$split = explode('.', $filename);
		$filetype = end($split);

		if ($show_image) {

			//output image file
			$this->output
				->set_content_type($filetype)
				->set_output(file_get_contents(UPLOAD_FOLDER.$filename));
		}
		else {
			$this->output->set_status_header(404);
		}
	}

	public function delete() {
		$this->gatekeep(["User","Admin","Root"]);

		$filename = $this->input->post("filename");

		$this->db
			->where("filename",$filename)
			->delete("uploads");

		if (file_exists(UPLOAD_FOLDER.$filename))
			unlink(UPLOAD_FOLDER.$filename);

		echo json_encode([
			'status' => 'success'
		]);
	}

	public function rotate() {
		$this->gatekeep(["User","Admin","Root"]);

		$post = $this->input->post();

		if ($post['direction'] == 'left') 
			$degrees = 90;
		else
			$degrees = -90;

		$image_path = UPLOAD_FOLDER.$post['filename'];

		$fileType = pathinfo($image_path, PATHINFO_EXTENSION);

		if($fileType == 'png' || $fileType == 'PNG'){

		   $source = imagecreatefrompng($image_path);
		   $bgColor = imagecolorallocatealpha($source, 255, 255, 255, 127);
		   // Rotate
		   $rotate = imagerotate($source, $degrees, $bgColor);
		   imagesavealpha($rotate, true);
		   imagepng($rotate,$image_path);

		}

		if($fileType == 'jpg' || $fileType == 'jpeg'){

		   $source = imagecreatefromjpeg($image_path);
		   // Rotate
		   $rotate = imagerotate($source, $degrees, 0);
		   imagejpeg($rotate,$image_path);
		}

		// Free the memory
		imagedestroy($source);
		imagedestroy($rotate);

		echo json_encode([
			'status' => 'success'
		]);
	
	}

}
