<?php

ini_set('display_errors', 'On');
error_reporting(E_ALL);

require_once '../application/libraries/dompdf/autoload.inc.php';

require_once '../application/libraries/Mustache/src/Mustache/Autoloader.php';
Mustache_Autoloader::register();

use Dompdf\Dompdf;

class Report extends Base_Controller {

	public $table = "reports";

	public $validation_rules = [];
	public $model = "ReportModel";

	public $pdf_line_height = 0.05;
	public $pdf_font_family = "Arial";
	public $pdf_margin_side = 0.8;
	public $pdf_margin_vertical = 0.3;

	public function __construct()
	{
		parent::__construct();
	
		$this->gatekeep(["Demo", "User","Admin","Root"]);
	}

	private function findQuery($id,$field='id') {
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

		$report['location'] = $this->db->get_where("locations",["id"=>$report['location_id']])->row_array();

		$report['sections'] = $this->db
			->select("*")
			->from("report_template_sections")
			->join("section_templates", "section_templates.id = report_template_sections.section_template_id","right")
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

		return $report;
	}

	public function find($id,$field='id') {
		$report = $this->findQuery($id, $field);

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

	private function setTextFormat($pdf, $format) {
		if ($format == 'text') {
			$pdf->SetFont($this->pdf_font_family, '', 9);
			$this->pdf_line_height = 0.18;
			$pdf->SetTextColor(1,1,1);
		}
		elseif ($format == 'heading') {
			$pdf->SetFont($this->pdf_font_family,'', 16);
			$this->pdf_line_height = 0.25;

			$pdf->SetTextColor(60,85,75);
		}
		elseif ($format == 'subheading') {
			$pdf->SetFont($this->pdf_font_family,'', 12);
			$this->pdf_line_height = 0.20;
			$pdf->SetLineWidth(0.01);
			$pdf->SetTextColor(60,85,75);
		}
	}

	private function writeSmallCapsCell($pdf,$char_width, $uppercase_fontsize, $x, $text) {
		$lowercaseVshift = $uppercase_fontsize * 0.00065;
		$chars = str_split($text);
		$shift_state_upper = false;
		$pdf->setX($x);
		foreach ($chars as $char) {
			if ($char == "i" || $char == "I" || $char == ' ') {
				//var_dump($char);
				$adjusted_char_width = $char_width * 0.5;
			}
			else
				$adjusted_char_width = $char_width;

			if (ctype_upper($char)) { //uppercase character
				$adjusted_char_width = $adjusted_char_width*1.2;

				$pdf->SetFont($this->pdf_font_family,'',$uppercase_fontsize);

				if (!$shift_state_upper) {
					$pdf->SetY($pdf->getY() - $lowercaseVshift,false);
					$shift_state_upper = true;
				}
				
			}
			else { //lowercase character
				$pdf->SetFont($this->pdf_font_family,'',$uppercase_fontsize*0.85);

				if ($shift_state_upper) {
					$pdf->SetY($pdf->getY() + $lowercaseVshift,false);
					$shift_state_upper = false;
				}
			}

			$pdf->Cell($adjusted_char_width,$this->pdf_line_height,strtoupper($char),0,0,"L");
			$x += $adjusted_char_width;
			$pdf->setX($x);
		}
	}

	private function writeSubHeading($pdf,$text) {
		$this->setTextFormat($pdf,"subheading");
		$pdf->Ln();

		$char_width = 0.11;
		$uppercase_fontsize = 12;

		$this->writeSmallCapsCell($pdf, $char_width,$uppercase_fontsize,$this->pdf_margin_side, $text);

		$pdf->setY($pdf->getY()+$this->pdf_line_height);
		$pdf->Line($this->pdf_margin_side,$pdf->GetY(),$pdf->GetPageWidth() - $this->pdf_margin_side,$pdf->GetY());
		//$pdf->setY($pdf->getY()+($this->pdf_line_height / 2));
	}

	private function writeHeading($pdf,$text) {
		$this->setTextFormat($pdf,"heading");
		$pdf->SetY(1.5);

		$char_width = 0.13;
		$uppercase_fontsize = 16;

		//calculate x to center
		$num_chars = strlen($text);
		$heading_width = $num_chars*$char_width;
		$padding_left = (($pdf->getPageWidth() - $heading_width)/2) - $this->pdf_margin_side;

		$this->writeSmallCapsCell($pdf, $char_width,$uppercase_fontsize,$this->pdf_margin_side+$padding_left, $text);

		$pdf->setY($pdf->getY()+$this->pdf_line_height);
	}

	public function pdf($id) {

		$this->load->library("fpdf/Pdfhtml");

		$this->load->model("ReportModel");

		$report = $this->findQuery($id,"id");
		$company = $this->db->get("company_settings")->result_array();

		$company_vars = [];
		foreach ($company as $setting) {
			$company_vars[$setting['setting']] = $setting['value'];
		}

		$variables = [
			'client' => $report['client'],
			'company' => $company_vars,
			'location'=> $report['location'],
			'report_date' => $report['date_issued']
		];

		$page_num = 0;

		$pdf = new Pdfhtml("P","in","letter",base_url());

		$m = new Mustache_Engine;

		$pdf->SetMargins($this->pdf_margin_side,$this->pdf_margin_vertical, $this->pdf_margin_side);

		//create cover page
		/////////////////////////////
		$pdf->SetTextColor(230,220,210);
		$pdf->AddPage();
		$pdf->Image("assets/cover_page.jpg",0,0,0,11,'');

		$pdf->SetFont($this->pdf_font_family,'', 16);
		$pdf->Text($this->pdf_margin_side,3,$report['report_title']);

		$pdf->SetFont($this->pdf_font_family,'', 12);
		$pdf->Text($this->pdf_margin_side,4,$report['date_issued']);

		$pdf->Image("assets/logo.jpg",6,9.5,0,1,'');

		//Preface Page
		/////////////////////////////
		$pdf->SetTextColor(1,1,1);

		$pdf->AddPage();
		$page_num++;

		$this->setTextFormat($pdf,"text");

		$rendered_text = $m->render($report['preface_text'],$variables);

		$pdf->WriteHTML($rendered_text,$this->pdf_line_height,$this->pdf_margin_side);

		//Table of contents Page
		/////////////////////////////
		$pdf->addReportPage();
		$page_num++;

		$this->writeHeading($pdf,"Table Of Contents");
		
		$this->setTextFormat($pdf,"text");
		$section_page_num = $page_num;
		$text_line = 1.9;
		foreach ($report['sections'] as $section) {

			$pdf->Text($this->pdf_margin_side,$text_line,$section['heading']);
			$pdf->Cell(0,$this->pdf_line_height*1.5,$section_page_num,0,2,"R");
			//$pdf->Text($)
			$text_line += $this->pdf_line_height*1.5;

			$section_page_num++;
		}
		//////////////////////////////

		//SECTION PAGES
		//////////////////////

		//echo "<pre>";
		//var_dump($report['sections']);

		foreach ($report['sections'] as $section) {
			$pdf->addReportPage();

			$this->writeHeading($pdf,$section['heading']);

			if ($section['description_text']) {
				$this->setTextFormat($pdf,"text");

				$rendered_text = $m->render($section['description_text'],$variables);
				$pdf->WriteHTML($rendered_text,$this->pdf_line_height,$this->pdf_margin_side);
				$pdf->Ln();
			}

			if ($section['has_guidelines']) {
				$this->writeSubHeading($pdf,"Guidelines and Requirements");

				$this->setTextFormat($pdf,"text");
				$rendered_text = $m->render($section['guidelines_text'],$variables);
				$pdf->WriteHTML($rendered_text,$this->pdf_line_height,$this->pdf_margin_side);
				$pdf->Ln();
			}

			if ($section['has_findings']) {

				$this->writeSubHeading($pdf,"Findings");

				$this->setTextFormat($pdf,"text");
				$rendered_text = $m->render($section['findings'],$variables);
				$pdf->WriteHTML($rendered_text,$this->pdf_line_height,$this->pdf_margin_side);
				$pdf->Ln();
			}
		}


		$pdf->Output("I");
		
	}

}
