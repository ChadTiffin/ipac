<?php

class Project extends Base_Controller {

	public $table = "projects";
	public $model = "ProjectModel";
	public $validation_rules = [];

	public function __construct()
	{
		parent::__construct();
	
		$this->gatekeep(["Demo", "User","Admin","Root"]);
	}

	public function find($id,$field = 'id') {
		$project = $this->db->get_where("projects",[$field => $id, "deleted" => 0])->row_array();

		if ($project) {
			$project['created_by_user'] = $this->db->get_where("users", ["id" => $project['created_by']])->row_array();

			$project['project_lead_user'] = $this->db->get_where("users", ["id" => $project['project_lead_id']])->row_array();

			$tasks = $this->db
				->order_by("due_date","ASC")
				->order_by("priority","DESC")
				->get_where("tasks",["owner_type" => "project","owner_id" => $project['id']])
				->result_array();

			$project['tasks'] = [];
			foreach ($tasks as $task) {
				$task['created_by_user'] = $this->db
					->select("first_name, last_name, email, user_level, username")
					->get_where("users",["id" => $task['created_by']])
					->row_array();

				$task['assigned_to_user'] = $this->db
					->select("first_name, last_name, email, user_level, username")
					->get_where("users",["id" => $task['assigned_to']])
					->row_array();

				$task['completed_by_user'] = $this->db
					->select("first_name, last_name, email, user_level, username")
					->get_where("users",["id" => $task['completed_by']])
					->row_array();

				$task['is_complete'] = 0;
				if ($task['completed_at'] !== null) {
					$task['is_complete'] = 1;
				}

				$project['tasks'][] = $task;
			}

			$project['phases'] = $this->db
				->select("owner_phases.*, priority, label")
				->from("owner_phases")
				->join("phases","phase_id = phases.id")
				->where("owner_type", "project")
				->where("owner_id",$id)
				->order_by("priority","ASC")
				->get()
				->result_array();

			echo json_encode($project);
		}
		else 
			echo json_encode([
				"status" => "deleted",
				"message" => "This project has been deleted"
			]);
		
	}

}
