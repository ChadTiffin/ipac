<?php

class Client extends Base_Controller {

	public $table = "clients";
	public $model = "ClientModel";
	public $validation_rules = [];

	public function __construct()
	{
		parent::__construct();
	
		$this->gatekeep(["Demo", "User","Admin","Root"]);
	}

}
