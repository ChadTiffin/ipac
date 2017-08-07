<?php

class ReportTemplate extends Base_Controller {

	public $table = "report_templates";

	public $validation_rules = [];

	public function __construct()
	{
		parent::__construct();
	
		$this->gatekeep(["Demo", "User","Admin","Root"]);
	}

	public function find($id,$field='id') {
		/*$report_template = $this->db
			->select("*")
			->from("report_templates")
			->join("report_template_sections","report_templates.id = report_template_sections.report_template_id")
			->join("section_templates","report_template_sections.section_template_id = section_templates.id")
			->where($this->table.".".$field,$id)
			->get()
			->result_array();*/

		$report_template = $this->db->get_where($this->table,[$field => $id])->row_array();

		$report_template['section_templates'] = $this->db
			->select("*")
			->from("report_template_sections")
			->join("section_templates","section_templates.id = report_template_sections.section_template_id")
			->where("report_template_id",$id)
			->get()
			->result_array();

		echo json_encode($report_template);
	}

	public function save()
	{
		$post = $this->input->post();

		$sections = json_decode($post['includedSections'],true);

		if ($post['id'] == "new") {
			$this->db->insert($this->table,[
				"template_name" => $post['template_name'],
				'preface_text' => $post['preface_text']
			]);
		}
		else {
			$this->db
				->set("template_name",$post['template_name'])
				->set("preface_text",$post['preface_text'])
				->where("id",$post['id'])
				->update($this->table);

			$this->db
				->where("report_template_id",$post['id'])
				->delete("report_template_sections");
		}

		$cnt_order = 0;
		foreach ($sections as $section) {

			$section['section_template_id'] = $section['id'];
			$section['report_template_id'] = $post['id'];

			unset($section['id']);
			unset($section['heading']);
			unset($section['description_text']);
			unset($section['has_guidelines']);
			unset($section['has_findings']);
			unset($section['guidelines_text']);
			unset($section['updated_at']);

			$section['order_index'] = $cnt_order;
			$cnt_order++;

			$this->db->insert("report_template_sections",$section);
		}

		echo json_encode([
			'status' => 'success'
		]);
	}
}
