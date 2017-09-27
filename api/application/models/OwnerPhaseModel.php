<?php

class OwnerPhaseModel extends BaseModel {

	public $table = "owner_phases";

	public $relations = [
		["table" => "phases", "key" => "phase_id"]
	];

}