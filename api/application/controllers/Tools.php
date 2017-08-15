<?php

class Tools extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
	
	}

	public function form_generator() {
		$this->load->view("form_builder");	
	}

	public function convert_form() {
		$csv_string = $this->input->post("csv",",","");

		$csv_lines = explode(PHP_EOL, $csv_string);

		$json = [];
		$section = [];
		$subsection = [];
		$last_heading = "h";
		
		foreach ($csv_lines as $line) {
			//echo $line."<br>";

			$line = str_getcsv($line);

			if ($line[0] == 'h') {
				$depth = 0;

				if (!empty($subsection)) {
					$section['subSections'][] = $subsection;
					$subsection = [];
				}

				$json[] = $section;
				$section = [];

				$section['heading'] = $line[1];
				$last_heading = $line[0];
			}
			elseif ($line[0] == 's') {
				if (!empty($subsection)) {
					$section['subSections'][] = $subsection;
					$subsection = [];
				}

				$subsection['heading'] = $line[1];
				$last_heading = $line[0];
			}
			elseif ($line[0] == 'q') {
				if ($last_heading == 'h') 
					$section['fields'][] = [
						"question" => $line[1],
						"type" => "yes/no",
						"hasNotes" => true
					];
				elseif ($last_heading == 's') {
					$subsection['fields'][] = [
						'question' => $line[1],
						'type' => "yes/no",
						"hasNotes" => true
					];
				}
			}
			
		}
		$json[] = $section;

		echo json_encode($json);
	}

}
