<?php

class Base_Controller extends CI_Controller {

	public $table = "";
	public $model = "";
	public $validation_rules = [];

	public $api_excluded_fields = [];

	function __construct() {
		parent::__construct();

		if ($this->model != "") {
			$this->load->model($this->model);
		}

		if (isset($_SERVER['HTTP_ORIGIN']))
			header('Access-Control-Allow-Origin: '.$_SERVER['HTTP_ORIGIN']);
		else {
			//header('Access-Control-Allow-Origin: http://localhost:8081');
			header('Access-Control-Allow-Origin: https://budget.chadtiffin.com');	
		}

		header('Access-Control-Allow-Credentials: true');
		header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding, X-API-KEY");
		header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
		header("Access-Control-Max-Age: 600");
		if ( "OPTIONS" === $_SERVER['REQUEST_METHOD'] ) {
			die();
		}

	}

	protected function gatekeep($user_types) {
		$this->load->model("UserModel");
		if (!$this->UserModel->accessAllowed($user_types)) {
			echo json_encode([
				'status' => "unauthorized",
				'msg' => "Unauthorized"
			]);
			die;
		}

		//$user = $this->db->get_where("users",["api_key" => $_REQUEST['key']])->row();
		//$_SESSION['user_id'] = $user->id;

		/*unset($_REQUEST['key']);
		unset($_GET['key']);
		unset($_POST['key']);*/
	}

	public function find($id,$field = 'id')
	{

		if ($this->model != "") {
			$model = $this->model;
			$result = $this->$model->find($field, $id);
		}
		else {
			$result = $this->db->get_where($this->table,[$field => $id])->row_array();
		}

		foreach ($this->api_excluded_fields as $field) {
			unset($result[$field]);
		}

		echo json_encode($result);
	}

	public function save()
	{

		unset($_REQUEST['key']);
		unset($_POST['key']);

		$this->load->library("form_validation");
		$this->form_validation->set_error_delimiters('', '');

		$validation_passes = true;

		$data = $this->input->post();

		unset($data['key']);

		//check for any requests for server timestamp
		$new_data = [];
		foreach ($data as $key => $value) {
			if ($value == "{{{server_now}}}")
				$value = date("Y-m-d H:i:s");
			elseif ($value == "{{{current_user}}}") {
				$headers = getallheaders();

				if (isset($headers['x-api-key']))  {
					$user = $this->db->get_where("users",["api_key" => $headers['x-api-key']])->row();
					$value = $user->id;
				}
				else {
					$user = false;
					$value = 0;
				}

			}
			elseif ($value == "null")
				$value = null;

			$new_data[$key] = $value;
		}
		$data = $new_data;

		if ($this->validation_rules != null) {

			$this->form_validation->set_data($data);
			$this->form_validation->set_rules($this->validation_rules);
			$validation_passes = $this->form_validation->run();
		}

		if (!$validation_passes) {
			//fails

			$form_errors = [];
			foreach ($this->validation_rules as $field) {
				if (form_error($field['field']) != "")
					$form_errors[$field['field']] = form_error($field['field']);
			}

			echo json_encode([
				"status" => "fail",
				"errors" => $form_errors
			]);
		}
		else {

			$data['updated_at'] = date("Y-m-d H:i:s");

			if (isset($data['id']) && $data['id'] != 'undefined' && $data['id'] != 0) {

				$this->db
					->set($data)
					->where('id',$data['id'])
					->update($this->table);

				$record_id = $data['id'];
			}
			else {

				$this->db->insert($this->table,$data);

				$record_id = $this->db->insert_id();
			}

			echo json_encode([
				"status" => "success",
				'id' => $record_id
			]);
		}
	}

	public function save_batch() {
		$this->load->library("form_validation");
		$this->form_validation->set_error_delimiters('', '');

		$validation_passes = true;

		unset($_REQUEST['key']);
		unset($_GET['key']);
		unset($_POST['key']);

		$post = $this->input->post();

		unset($post['key']);

		$form_errors = [];

		$numInserts = 0;
		$numUpdates = 0;
		foreach (json_decode($post['records'],true) as $record) {
			$this->form_validation->set_data($record);
			
			if ($this->validation_rules != null) {
				$this->form_validation->set_rules($this->validation_rules);
				$validation_passes = $this->form_validation->run();
			}

			if (!$validation_passes) {

				foreach ($this->validation_rules as $field) {
					if (form_error($field['field']) != "")
						$form_errors[$field['field']] = form_error($field['field']);
				}

				echo json_encode([
					"status" => "fail",
					"errors" => $form_errors
				]);
				die;
			}
			else {

				$record['updated_at'] = date("Y-m-d H:i:s");

				if (isset($record['id']) && $record['id'] != 'undefined' && $record['id'] != 0) {

					$this->db
						->set($record)
						->where('id',$record['id'])
						->update($this->table);

					$record_id = $record['id'];
					$numUpdates++;
				}
				else {

					$this->db->insert($this->table,$record);

					$record_id = $this->db->insert_id();
					$numInserts++;
				}
			}
		}
		echo json_encode([
			"numInserts" => $numInserts,
			"numUpdates" => $numUpdates,
			"status" => "success"
		]);

	}

	public function get()
	{

		/*
		$filters should look like this:
		$filters = [
			[field, value,and/or],
			[field, value,and/or 'like'],
			['field >=',value,and/or]
		]

		$order = [field, order]
		*/

		$order = [];
		if ($this->input->get("order")) {
			$order = json_decode($this->input->get("order"));

			$this->db->order_by($order[0],$order[1]);
		}

		if ($this->input->get("filters")) {
			$filters = json_decode($this->input->get("filters"),true);

			if ($this->model != "") {
				$model = $this->model;
				$records = $this->$model->get(false, $filters,$order);
			}
			else {
				
				foreach ($filters as $filter) {

					if (isset($filter[2]) && strtolower($filter[2]) == 'and') {
						if (isset($filter[3]) && strtolower($filter[3]) == 'like')
							$this->db->like($filter[0],$filter[1]);
						else
							$this->db->where($filter[0],$filter[1]);
					}
					elseif (isset($filter[2]) && strtolower($filter[2]) == 'or') {
						if (isset($filter[3]) && strtolower($filter[3]) == 'like')
							$this->db->or_like($filter[0],$filter[1]);
						else
							$this->db->or_where($filter[0],$filter[1]);
					}
					else
						$this->db->where($filter[0],$filter[1]);
				}

				//var_dump($this->db->get_compiled_select());die;
				$records = $this->db
					->get($this->table)
					->result_array();
			}
		}
		else {
			if ($this->model != "") {
				$model = $this->model;
				$records = $this->$model->get();
			}
			else {
				$records = $this->db->get($this->table)->result_array();
			}
		}

		if (!empty($this->api_excluded_fields)) {

			$response = [];
			foreach ($records as $item) {
				foreach ($this->api_excluded_fields as $field) {
					unset($item[$field]);
				}

				$response[] = $item;
			}
		}
		else {
			$response = $records;
		}

		echo json_encode($response);

	}

	public function delete()
	{
		$id = $this->input->post("id");

		if ($this->model != "") {

			$model = $this->model;
			$this->$model->delete($id);
		}
		else
			$this->db->delete($this->table,["id" => $id]);

		echo json_encode([
			"status" => "success"
		]);
	}

}
