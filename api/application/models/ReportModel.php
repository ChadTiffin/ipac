<?php

class ReportModel extends BaseModel {

	public $table = "reports";

	public $relations = [
		['table' => 'clients', 'key' => 'client_id'],
		['table' => 'report_templates', 'key' => 'report_template_id'],
		['table' => 'locations','key' => 'location_id']
	];
}