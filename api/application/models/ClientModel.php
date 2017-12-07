<?php

class ClientModel extends BaseModel {

	public $table = "clients";

	public $soft_delete = true;

	public $relations = [
		['table' => 'locations', 'key' => 'client_id', 'hasMany'=>true],
		['table' => 'owner_phases', 'key' => 'owner_id', 'hasMany'=>true, 'model' => 'OwnerPhaseModel']
	];
}