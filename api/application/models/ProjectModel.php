<?php

class ProjectModel extends BaseModel {

	public $table = "projects";

	public $soft_delete = true;

	/*public $relations = [
		['table' => 'users', 'key' => 'project_lead_id'],
		["table" => "owner_phases", "key" => "owner_id", "hasMany" => true],
		["table" => "tasks", "key" => "owner_id", 'hasMany' => true]
	];*/

	public $relations = [
		['table' => 'users', 'key' => 'project_lead_id'],
		["model" => "OwnerPhaseModel","table" => "owner_phases", "key" => "owner_id", "hasMany" => true],
		["table" => "tasks", "key" => "owner_id", 'hasMany' => true]
	];

}