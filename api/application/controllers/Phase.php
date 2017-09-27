<?php

class Phase extends Base_Controller {

	public $table = "phases";
	public $model = "PhaseModel";
	public $validation_rules = [];

	public $api_excluded_fields = ["updated_at"];

	public function __construct()
	{
		parent::__construct();
	
		$this->gatekeep(["User","Admin","Root"]);
	}

	public function save_phase_list() {
		$updated_phases = json_decode($this->input->post("phases"));
		$owner_id = $this->input->post("owner_id");
		$owner_type = $this->input->post("type");

		$master_list = $this->db->get("phases")->result();

		$existing_list = $this->db->get_where("owner_phases",[
			"owner_id" => $owner_id,
			"owner_type" => $owner_type,
		])->result_array();
		$existing_ids = [];

		foreach ($existing_list as $phase) {
			$existing_ids[] = $phase['phase_id'];
		}

		//go through old list and delete anything that isn't included in new list
		foreach ($existing_ids as $existing_id) {
			if (!in_array($existing_id, $updated_phases)) {
				$this->db
					->where([
						"phase_id" => $existing_id,
						"owner_id" => $owner_id,
						"owner_type" => $owner_type
					])
					->delete("owner_phases");
			}
		}

		//go through new list and insert any phase that isn't already present
		foreach ($updated_phases as $phase_id) {
			$exists = $this->db->get_where("owner_phases",["phase_id" => $phase_id,"owner_type" => $owner_type, "owner_id" => $owner_id])->row();

			if (!$exists) {
				$this->db->insert("owner_phases",[
					"owner_type" => $owner_type,
					"owner_id" => $owner_id,
					"phase_id" => $phase_id,
					"updated_at" => date("Y-m-d H:i:s")
				]);
			}
		}

		echo json_encode([
			"status" => "success"
		]);
	}

	public function rollback_active() {

		$owner_id = $this->input->post("owner_id");
		$owner_type = $this->input->post("owner_type");

		$active_phase = $this->db
			->select("*")
			->from("owner_phases")
			->join("phases","phases.id = owner_phases.phase_id")
			->where([
				"active" => 1,
				"owner_type" => $owner_type,
				"owner_id" => $owner_id
			])
			->get()
			->row();

		if ($active_phase) {

			//find phase before this and set as active
			$prev_phase = $this->db
				->select("owner_phases.*, priority")
				->from("owner_phases")
				->join("phases","phases.id = owner_phases.phase_id")
				->where([
					"priority <" => $active_phase->priority,
					"owner_type" => $owner_type,
					"owner_id" => $owner_id
				])
				->order_by("priority", "DESC")
				->get()
				->row();

			if ($prev_phase) {

				$this->db
					->set([
						"active" => 1,
						"activated_at" => date("Y-m-d H:i:s")
					])
					->set("completed_at",'null',false)
					->where("id",$prev_phase->id)
					->update("owner_phases");
			}


			//make sure all phases ahead are incomplete and inactive
			$next_phases = $this->db
				->select("owner_phases.*, priority")
				->from("owner_phases")
				->join("phases","phases.id = owner_phases.phase_id")
				->where([
					"priority >=" => $active_phase->priority,
					"owner_type" => $owner_type,
					"owner_id" => $owner_id
				])
				->get()
				->result();

			foreach ($next_phases as $phase) {
				$this->db
				->set("active",0)
				->set("completed_at",'null',false)
				->where("id",$phase->id)
				->update("owner_phases");
			}
		}
		else {
			//no active phases, figure out if they're ALL complete or NONE are complete
			$phases = $this->db->get_where("owner_phases",[
				"owner_type" => $owner_type,
				"owner_id" => $owner_id,
				"completed_at" => null
			])->result();

			if (!$phases) {
				//they are all complete, so rollback to last phase
				$last_phase = $this->db
					->select("owner_phases.*, priority")
					->from("owner_phases")
					->join("phases","phases.id = owner_phases.phase_id")
					->order_by("priority", "DESC")
					->where([
						"owner_type" => $owner_type,
						"owner_id" => $owner_id
					])
					->get()
					->row();

				$this->db->set("active",1)
					->where("id", $last_phase->id)
					->update("owner_phases");
			}
		}
		
		echo json_encode(["status" => "success"]);
	}

	public function advance_active() {

		$owner_id = $this->input->post("owner_id");
		$owner_type = $this->input->post("owner_type");

		//get current phase
		$active_phase = $this->db
			->select("owner_phases.*, priority")
			->from("owner_phases")
			->join("phases","phases.id = owner_phases.phase_id")
			->where([
				"active" => 1,
				"owner_type" => $owner_type,
				"owner_id" => $owner_id
			])
			->get()
			->row();

		//mark completion time of current phase and unactivate it
		if ($active_phase) {
			$this->db
				->set([
					"active" => 0,
					"completed_at" => date("Y-m-d H:i:s")
				])
				->where("id",$active_phase->id)
				->update("owner_phases");

			//get next phase in priority
			$next_phase = $this->db
				->select("owner_phases.*, priority")
				->from("owner_phases")
				->join("phases","phases.id = owner_phases.phase_id")
				->where([
					"priority >" => $active_phase->priority,
					"owner_type" => $owner_type,
					"owner_id" => $owner_id
				])
				->order_by("priority","ASC")
				->get()
				->row();

			if ($next_phase) {

				$this->db
					->set([
						"active" => 1,
						"activated_at" => date("Y-m-d H:i:s")
					])
					->where("id",$next_phase->id)
					->update("owner_phases");
			}
		}
		else {
			$first_phase = $this->db
				->select("owner_phases.*, priority")
				->from("owner_phases")
				->join("phases","phases.id = phase_id")
				->order_by("priority","ASC")
				->where([
					"owner_type" => $owner_type,
					"owner_id" => $owner_id,
				])
				->get()
				->row();

			if ($first_phase) {

				$this->db->set("active",1)
					->where("id",$first_phase->id)
					->update("owner_phases");
			}
		}

		echo json_encode(["status" => "success"]);
	}

}
