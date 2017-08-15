<?php

class Image extends Base_Controller {

	public $table = "images";

	public function __construct()
	{
		parent::__construct();
	}

	public function image($filename) {
		$show_image = true;

		$split = explode('.', $filename);
		$filetype = end($split);

		if ($show_image) {

			//output image file
			$this->output
				->set_content_type($filetype)
				->set_output(file_get_contents(UPLOAD_FOLDER.$filename));
		}
		else {
			show_404();
		}
	}

	public function delete() {
		$filename = $this->input->post("filename");

		$this->db
			->where("filename",$filename)
			->delete("uploads");

		unlink(UPLOAD_FOLDER.$filename);

		echo json_encode([
			'status' => 'success'
		]);
	}

}
