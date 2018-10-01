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

		if ($report) {

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

			$report['user'] = $this->db
				->select("first_name, last_name, email")
				->from("users")
				->where("id",$report['updated_by'])
				->get()->row();

			$new_sections = [];
			foreach ($report['sections'] as $section) {
				$report_section = $this->db->get_where("report_sections",["report_id" => $id,"section_template_id" => $section['section_template_id']])->row_array();

				$section['findings'] = $report_section['body_text'];
				$section['images'] = $report_section['images'];

				$new_sections[] = $section;
			}

			$report['sections'] = $new_sections;

			return $report;
		}
		else
			return false;
	}

	public function find($id,$field='id') {
		$report = $this->findQuery($id, $field);

		echo json_encode($report);
	}

	public function save_report()
	{
		$post = $this->input->post();

		$report = json_decode($post['report'],true);
		$sections = json_decode($post['sections']);


		//get user
		$headers = getallheaders();

		if (isset($headers['x-api-key']))  {
			$user = $this->db->get_where("users",["api_key" => $headers['x-api-key']])->row();
			$report['updated_by'] = $user->id;
		}

		$report['updated_at'] = date("Y-m-d H:i:s");

		$report['extra_images'] = json_encode($report['extra_images']);

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

	public function pdf($id,$type = null) {

		$this->load->library("fpdf/PDF_TOC");

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
			'user' => $report['user'],
			'date' => [
				"report_issued" => $report['date_issued'],
				"audit_performed" => $report['audit_date']
			]
		];

		$pdf = new PDF_TOC("P","in","letter",base_url());

		$pdf->page_num = 0;

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

		$pdf->SetAutoPageBreak(true, 1.5);

		//Preface Page
		/////////////////////////////
		$pdf->SetTextColor(1,1,1);

		$pdf->AddPage();
		$pdf->page_num++;

		$this->setTextFormat($pdf,"text");

		$rendered_text = $m->render($report['preface_text'],$variables);

		$pdf->WriteHTML($rendered_text,$this->pdf_line_height,$this->pdf_margin_side);

		//Table of contents Page
		/////////////////////////////
		/*$pdf->addReportPage($pdf->page_num);
		$pdf->page_num++;

		$this->writeHeading($pdf,"Table Of Contents");
		
		$this->setTextFormat($pdf,"text");
		$section_page_num = $pdf->page_num;
		$text_line = 1.9;
		foreach ($report['sections'] as $section) {

			$pdf->Text($this->pdf_margin_side,$text_line,$section['heading']);
			$pdf->Cell(0,$this->pdf_line_height*1.5,$section_page_num,0,2,"R");
			//$pdf->Text($)
			$text_line += $this->pdf_line_height*1.5;

			$section_page_num++;
		}*/
		//////////////////////////////

		//SECTION PAGES
		//////////////////////

		//echo "<pre>";
		//var_dump($report['sections']);

		if (!$type) {

			$toc = [];

			$pdf->page_num = 1;

			foreach ($report['sections'] as $section) {
				$pdf->page_num++;
				$pdf->addReportPage($pdf->page_num);
				
				$toc[] = [$section['heading'],$pdf->page_num];

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

					$imageMaxDimension = 250/72;

					$imageWidth = 250/72;

					if (strlen($section['images']) > 2) {

						$images = json_decode($section['images']);
					
						$pdf->Ln();

						$rowMaxHeight = 0;

						$index = 0;
						foreach ($images as $image) {
							
							//check if line wrap needs to occur
			                if ($pdf->getPageWidth() - $pdf->GetX() - $imageWidth <= 0) {
			                    $pdf->SetY($pdf->getY() + $rowMaxHeight);
			                    $rowMaxHeight = 0; //reset rowMaxHeight because its a new image row
			                }

			                //check if page break needs to occur
			                /*
			                1. Look ahead up to 2 images
			                2. Calculate max row height for those 2 images
			                3. determine if there is enough space left on the page to fit rowMaxHeight
			                */

			                $futureRowHeight = 0;
			                if (isset($images[$index+1])) {

			                	if (file_exists(UPLOAD_FOLDER.$images[$index+1])) {
			                		//get image dimensions
									$future1_size = getimagesize(UPLOAD_FOLDER.$images[$index+1]);

									$futureRowHeight = $imageWidth * ($future1_size[1]/$future1_size[0]);
			                	}

			                	if (isset($images[$index+2])) {
			                		if (file_exists(UPLOAD_FOLDER.$images[$index+2])) {
				                		//get image dimensions
										$future2_size = getimagesize(UPLOAD_FOLDER.$images[$index+2]);

										if ($futureRowHeight < $imageWidth * ($future2_size[1]/$future2_size[0]))
											$futureRowHeight =  $imageWidth * ($future2_size[1]/$future2_size[0]);
				                	}
			                	}
			                }

			                if ($pdf->getPageHeight() - $pdf->GetY() - $futureRowHeight - 1.5 <= 0) {
			                	$pdf->page_num++;
			                    $pdf->addReportPage($pdf->page_num);
			                    //$this->last_element = null;
			                    //$setLastElement = false;
			                    $rowMaxHeight = 0; //reset rowMaxHeight because its a new image row
			                }

							if (file_exists(UPLOAD_FOLDER.$image)) {

			                	//get image dimensions
								$size = getimagesize(UPLOAD_FOLDER.$image);

								$width = $size[0];
								$height = $size[1];

								$height_ratio = $height/$width;

			                	if ($rowMaxHeight < $imageWidth * $height_ratio)
			                		$rowMaxHeight = $imageWidth * $height_ratio;
			                	
			                	$pdf->Image(UPLOAD_FOLDER.$image, $pdf->GetX(), $pdf->GetY(), $imageWidth, 0);
							             
			    				$pdf->SetX($pdf->GetX()+$imageWidth);
			    			}

			    			$index++;
						}

						$pdf->SetY($pdf->GetY() + $rowMaxHeight);
					}

					$this->setTextFormat($pdf,"text");
					$rendered_text = $m->render($section['findings'],$variables);

					$pdf->WriteHTML($rendered_text,$this->pdf_line_height,$this->pdf_margin_side);
					$pdf->Ln();
				}
			}

			$pdf->SetPage(2);

			$pdf->addReportPage();

			$this->writeHeading($pdf,"Table Of Contents");
			
			$this->setTextFormat($pdf,"text");
			$section_page_num = $pdf->page_num;
			$text_line = 1.9;
			foreach ($toc as $section) {

				$pdf->Text($this->pdf_margin_side,$text_line,$section[0]);
				$pdf->Cell(0,$this->pdf_line_height*1.5,$section[1],0,2,"R");
				//$pdf->Text($)
				$text_line += $this->pdf_line_height*1.5;
			}

			$pdf->SetPage($pdf->GetPageCount());
		}
		elseif ($type == "cover") {

		}

		$pdf->Output("I");
		
	}

	public function text($id, $download = null) {

		$report = $this->findQuery($id,"id");
		$company = $this->db->get("company_settings")->result_array();

		$company_vars = [];
		foreach ($company as $setting) {
			$company_vars[$setting['setting']] = $setting['value'];
		}

		$this->load->model("ImageModel");

		//attach image tokens
		$mutated = [];
		foreach ($report['sections'] as $section) {
			if (strlen($section['images']) > 2) {

				$images = json_decode($section['images']);

				$section['image_urls'] = [];
				foreach ($images as $image) {
					$image_record = $this->db->get_where("uploads",["filename" => $image])->row();

					$token = $this->ImageModel->generateUploadToken($image_record->id,4200); //6mon expiry


					if (file_exists(UPLOAD_FOLDER.$image)) {
						$size = getimagesize(UPLOAD_FOLDER.$image);
					

						$width = $size[0];
						$height = $size[1];

						//set dimensions
						$setWidth = 250;
						$setHeight = $setWidth/$width * $height;

						$section['image_urls'][] = [
							'url' => base_url()."image/serve/".$token['token']."/".$image,
							'width' => $setWidth,
							'height' => $setHeight
						];
					}
				}

			}

			$mutated[] = $section;
		}
		$report['sections'] = $mutated;

		$variables = [
			'client' => $report['client'],
			'company' => $company_vars,
			'location'=> $report['location'],
			'user' => $report['user'],
			'date' => [
				"report_issued" => $report['date_issued'],
				"audit_performed" => $report['audit_date']
			]
		];

		$data = [
			'sections' => $report['sections'],
			'm' => new Mustache_Engine,
			'variables' => $variables
		];

		if ($download) {
			$filename = $report['report_title'];

			header("Content-Type: application/vnd.ms-word");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("content-disposition: attachment;filename=$filename.doc");
		}

		$this->load->view("report_text",$data);
	}

}
