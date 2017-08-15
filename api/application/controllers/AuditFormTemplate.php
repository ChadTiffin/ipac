<?php

class AuditFormTemplate extends Base_Controller {

	public $table = "form_templates";

	public $validation_rules = [];

	public function __construct()
	{
		parent::__construct();
	
		$this->gatekeep(["Demo", "User","Admin","Root"]);
	}

}
