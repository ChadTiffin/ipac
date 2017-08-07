<?php

class Setting extends Base_Controller {

	public $table = "company_settings";

	public $validation_rules = [];

	public function __construct()
	{
		parent::__construct();
	
		$this->gatekeep(["Demo", "User","Admin","Root"]);
	}

}
