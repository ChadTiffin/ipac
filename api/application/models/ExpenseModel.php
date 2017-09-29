<?php

class ExpenseModel extends BaseModel {

	public $table = "expenses";

	public $soft_delete = true;

	public $relations = [
		['table' => 'users', 'key' => 'user_id'],
		['table' => 'uploads', 'key' => 'upload_id'],
		["table" => "locations", "key" => "location_id"],
		["table" => "clients", "key" => "owner_id"],
		["table" => "projects", "key" => "owner_id"],
	];
}