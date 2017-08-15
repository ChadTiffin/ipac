<?php

class AuditForm extends Base_Controller {

	public $table = "audits";

	public $validation_rules = [
		["field" => "client_id","label" => "Client","rules"=>"required|greater_than[0]"],
		["field" => "location_id","label" => "Location","rules"=>"required|greater_than[0]"],
		["field" => "audit_date","label" => "Audit Date","rules"=>"required"],
		["field" => "form_template_id","label" => "Form Template","rules"=>"required|greater_than[0]"]
	];

	public $model = "AuditFormModel";

	public function __construct()
	{
		parent::__construct();
	
		$this->gatekeep(["Demo", "User","Admin","Root"]);

	}

	public function upload_image() {
		$config['upload_path']          = UPLOAD_FOLDER;
		$config['allowed_types']        = 'png|jpg|jpeg';
		$config['max_size']             = 6000;
		$config['file_name']            = time().".jpg";

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

			$this->db->insert("uploads",[
				'original_filename' => $data['orig_name'],
				'filename' => $data['file_name'],
				'date_uploaded' => date('Y-m-d H:i:s')
			]);

			//resize image

			$config = [];
			$config['image_library'] = 'GD2';
			$config['source_image'] = UPLOAD_FOLDER.$data['file_name'];
			$config['maintain_ratio'] = TRUE;
			$config['width']         = 640;
			$config['height']       = 480;

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
				]);
			}
		
			
		}
	}

}
