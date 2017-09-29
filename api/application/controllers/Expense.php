<?php

class Expense extends Base_Controller {

	public $table = "expenses";
	public $model = "ExpenseModel";
	public $validation_rules = [];

	public function __construct()
	{
		parent::__construct();
	
		$this->gatekeep(["User","Admin","Root"]);
	}

}
