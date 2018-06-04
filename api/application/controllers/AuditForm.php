<?php

class AuditForm extends Base_Controller {

	public $table = "audits";

	public $validation_rules = [
		/*["field" => "client_id","label" => "Client","rules"=>"required|greater_than[0]"],
		["field" => "location_id","label" => "Location","rules"=>"required|greater_than[0]"],
		["field" => "audit_date","label" => "Audit Date","rules"=>"required"],
		["field" => "form_template_id","label" => "Form Template","rules"=>"required|greater_than[0]"]*/
	];

	public $model = "AuditFormModel";

	public function __construct()
	{
		parent::__construct();
	
		$this->gatekeep(["Demo", "User","Admin","Root"]);

	}

	public function save() {
		$data = $this->input->post();

		if (isset($data['id'])) {
			//get audit to make sure its not locked
			$audit = $this->db->get_where($this->table,["id" => $data['id']])->row();

			if ($audit->locked == 1 && isset($data['form_values'])) {

				echo json_encode([
					"status" => "fail",
					"msg" => "You can't edit this Audit's fields, it is locked!",
					"reason" => "locked"
				]);
			}
			else {
				parent::save();
			}
		}
		else
			parent::save();
	}

	public function delete() {
		$id = $this->input->post("id");

		//get all photo reference
		$audit = $this->db->get_where($this->table,["id"=>$id])->row_array();

		$form_values = json_decode($audit['form_values'],true);

		$deleted_files = 0;

		//delete images associated with audit
		if ($form_values) {
			foreach ($form_values as $section) {
				if (isset($section['fields'])) {
					foreach ($section['fields'] as $field) {
						if ($field['type'] == "images" && isset($field['value'])) {

							foreach ($field['value'] as $image) {
								if (file_exists(UPLOAD_FOLDER.$image)){
									unlink(UPLOAD_FOLDER.$image);
									$deleted_files++;
								}

								$this->db->where("filename",$image)
									->delete("uploads");
							}
							
						}
					}
				}

				if (isset($section['subSections'])) {
					foreach ($section['subSections'] as $subsection) {
						if (isset($subsection['fields'])) {
							foreach ($subsection['fields'] as $field) {
								if ($field['type'] == "images" && isset($field['value'])) {
									foreach ($field['value'] as $image) {
										if (file_exists(UPLOAD_FOLDER.$image)) {
											unlink(UPLOAD_FOLDER.$image);
											$deleted_files++;
										}

										$this->db->where("filename",$image)
											->delete("uploads");
									}
								}
							}
						}
					}
				}
			}	
		}

		$this->db->where("id",$id)->delete($this->table);

		echo json_encode(["status" => "success","deleted_files" => $deleted_files]);
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
				$record['form_values'] = json_encode($record['form_values']);

				if (isset($record['id']) && $record['id'] != 'undefined' && $record['id'] != 0) {

					$audit = $this->db->get_where($this->table,["id" => $record['id']])->row();

					if ($audit->locked == 0) {

						$this->db
							->set($record)
							->where('id',$record['id'])
							->update($this->table);

						$record_id = $record['id'];
						$numUpdates++;
					}
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

	public function word($audit_id) {
		$audit = $this->db->select("*")
			->from($this->table)
			->join("clients","clients.id = client_id")
			->join("locations","location_id=locations.id")
			->join("users","performed_by=users.id")
			->where($this->table.".id",$audit_id)
			->get()->row();

		$this->load->model("ImageModel");

		$filename = "Audit - ".$audit->location_name;

		header("Content-Type: application/vnd.ms-word");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("content-disposition: attachment;filename=$filename.doc");

		$this->load->view("audit_text",$audit);
	}

}
