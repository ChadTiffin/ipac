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

	private function addPDFPage($pdf) {
		$pdf->AddPage();

		$margin_side = 0.5;
		$margin_vertical = 0.3;

		//create header
		$pdf->Image("assets/logo.png",$margin_side,$margin_vertical,0,1,'');

		$pdf->SetLineWidth(0.025);
		$pdf->setDrawColor(60,85,75);
		$pdf->Line($margin_side,$margin_vertical+1.05,8.5-$margin_side,$margin_vertical+1.05);
		// end header

		//create footer
		$pdf->Image("assets/report_footer.png",0,9.3,8.5,0,'PNG');
	}

	public function download($type,$id) {
		/*if ($type == "pdf") {
			$this->load->library("fpdf");

			$this->load->model("ReportModel");

			$report = $this->ReportModel->find("id",$id);
			$company = $this->db->get("company_settings")->result_array();

			$margin_side = 0.5;
			$margin_vertical = 0.3;

			$pdf = new fpdf("P","in","letter");

			//create cover page
			$pdf->AddPage();
			$pdf->Image("assets/cover_page.png",0,0,8.5,11,'');

			$pdf->Text(3,$margin_side,$report_title);
			$pdf->Text(4,$margin_side,$report_date);

			$this->addPDFPage($pdf);

			$pdf->SetFont('Arial','',10);

			$pdf->Cell(0,0,'Hello World !',1);

			$pdf->Output("I");
		}*/

		if ($type == 'pdf') {
			$this->load->library("tcpdf/tcpdf");

			$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		}
	}

}
