<?php

class SectionTemplate extends Base_Controller {

	public $table = "section_templates";

	public $model = "SectionTemplateModel";

	public $validation_rules = [];

	public function __construct()
	{
		parent::__construct();
	
		$this->gatekeep(["Demo", "User","Admin","Root"]);
	}

}
