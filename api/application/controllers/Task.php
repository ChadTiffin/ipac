<?php

class Task extends Base_Controller {

	public $table = "tasks";
	public $model = "TaskModel";
	public $validation_rules = [
		["field" => "assigned_to","label" => "Assigned To", "rules" => "required"]
	];

	public function __construct()
	{
		parent::__construct();
	
		$this->gatekeep(["Demo", "User","Admin","Root"]);
	}

	public function filter($owner_type, $id) {

		$this->db
			->order_by("due_date","ASC")
			->order_by("priority","DESC");

		if ($owner_type == "client" || $owner_type == "project") {
			$this->db
				->where("owner_type", $owner_type)
				->where("owner_id", $id);
		}
		else
			$this->db->where("assigned_to", $id);
		

		$tasks = $this->db->get("tasks")->result_array();

		$tasks_appended = [];
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

			$task['owner'] = $this->db->get_where($task['owner_type']."s",["id" => $task['owner_id']])->row_array();

			$task['is_complete'] = 0;
			if ($task['completed_at'] !== null) {
				$task['is_complete'] = 1;
			}

			$tasks_appended[] = $task;
		}

		echo json_encode($tasks_appended);
	}

}
