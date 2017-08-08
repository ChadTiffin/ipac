<?php

class Report extends Base_Controller {

	public $table = "reports";

	public $validation_rules = [];
	public $model = "ReportModel";

	public function __construct()
	{
		parent::__construct();
	
		$this->gatekeep(["Demo", "User","Admin","Root"]);
	}

	public function find($id,$field='id') {
		//$report = $this->db->get_where($this->table,['id' => $id])->row_array();

		$report = $this->db
			->select("*")
			->from($this->table)
			->join("report_templates",$this->table."."."report_template_id = report_templates.id")
			->where($this->table.".id",$id)
			->get()->row_array();

		$report['client'] = $this->db->get_where("clients",["id" => $report['client_id']])->row_array();
		/*$report['sections'] = $this->db
			->select("*")
			->from("report_templates")
			->join("report_template_sections","report_template_sections.report_template_id = report_templates.id")
			->join("section_templates", "section_templates.id = report_template_sections.section_template_id")
			->where("report_templates.id",$report['report_template_id'])
			->get()->result_array();*/

		$report['sections'] = $this->db
			->select("*")
			->from("report_template_sections")
			->join("section_templates", "section_templates.id = report_template_sections.section_template_id")
			->where("report_template_sections.report_template_id",$report['report_template_id'])
			->order_by("order_index","ASC")
			->get()->result_array();

		$new_sections = [];
		foreach ($report['sections'] as $section) {
			$report_section = $this->db->get_where("report_sections",["report_id" => $id,"section_template_id" => $section['section_template_id']])->row_array();

			$section['findings'] = $report_section['body_text'];

			$new_sections[] = $section;
		}

		$report['sections'] = $new_sections;

		echo json_encode($report);
	}

	public function save_report()
	{
		$post = $this->input->post();

		$report = json_decode($post['report']);
		$sections = json_decode($post['sections']);

		if (is_numeric($post['id'])) {
			//update
			$this->db->set($report)
				->where("id",$post['id'])
				->update($this->table);

			$this->db->where("report_id",$post['id'])
				->delete("report_sections");
			
		}
		else {
			//insert

			$this->db->insert($this->table,$report);
		}

		$this->db->insert_batch("report_sections",$sections);

		echo json_encode([
			'status' => 'success'
		]);

	}

}
