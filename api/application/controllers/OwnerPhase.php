<?php

class OwnerPhase extends Base_Controller {

	public $table = "owner_phases";
	public $model = "OwnerPhaseModel";

	public $validation_rules = [];

	public function __construct()
	{
		parent::__construct();
	
		$this->gatekeep(["Demo", "User","Admin","Root"]);
	}

}
