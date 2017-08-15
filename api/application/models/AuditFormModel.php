<?php

class AuditFormModel extends BaseModel {

	public $table = "audits";

	public $relations = [
		['table' => 'clients', 'key' => 'client_id'],
		['table' => 'locations', 'key' => 'location_id'],
		['table' => 'form_templates', 'key' => 'form_template_id'],
		['table' => 'users','key' => 'performed_by', 'model' => "UserModel"]
	];
}