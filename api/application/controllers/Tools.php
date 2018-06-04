<?php

class Tools extends Base_Controller {

	public function __construct()
	{
		parent::__construct();
	
	}

	public function add_recommendations_retroactively() {
		$audits = $this->db
			->where("id >",341)
			->get("audits")->result_array();

		ini_set('max_execution_time', 1200);

		foreach ($audits as $audit) {
			$new_form = [];

			$form = json_decode($audit['form_values'],true);

			if ($form) {

				foreach ($form as $section) {

					if (isset($section['fields'])) {

						$new_qs = [];
						foreach ($section['fields'] as $q) {
							$form_q_stripped = str_replace(" ","",str_replace(":","",strtolower($q['question'])));
							$csv = fopen("recommendations.csv","r");

							while (($row = fgetcsv($csv, 0, ",")) !== FALSE) {
								//find matching question

								$q_stripped = strtolower($row[0]);
								$q_stripped = str_replace(":", "", $q_stripped);
								$q_stripped = str_replace(" ", "", $q_stripped);

								if ($form_q_stripped == $q_stripped) {
									//question found, add recommendation
									$q['recommendation'] = $row[1];
									break;
								}
							}
							$new_qs[] = $q;

							fclose($csv);
						}

						$section['fields'] = $new_qs;
					}

					if (isset($section['subSections'])) {

						$new_subsections = [];

						foreach ($section['subSections'] as $subsection) {
							if (isset($subsection['fields'])) {

								$new_qs = [];
								foreach ($subsection['fields'] as $q) {
									$form_q_stripped = str_replace(" ","",str_replace(":","",strtolower($q['question'])));
									$csv = fopen("recommendations.csv","r");

									while (($row = fgetcsv($csv, 0, ",")) !== FALSE) {
										//find matching question

										$q_stripped = strtolower($row[0]);
										$q_stripped = str_replace(":", "", $q_stripped);
										$q_stripped = str_replace(" ", "", $q_stripped);

										if ($form_q_stripped == $q_stripped) {
											//question found, add recommendation
											//strip "recommendation:" text
											$rec = str_replace("Recommendation: ", "", $row[1]);

											$q['recommendation'] = $rec;
											break;
										}
									}
									$new_qs[] = $q;

									fclose($csv);
								}

								$subsection['fields'] = $new_qs;
							}
							$new_subsections[] = $subsection;
						}

						$section['subSections'] = $new_subsections;
					}

					$new_form[] = $section;
				}

				$this->db->set("form_values",json_encode($new_form))
					->set("updated_at",date("Y-m-d H:i:s"))
					->where("id",$audit['id'])
					->update('audits');
			}
		}
	}

	public function add_recommendations() {
		
		$form = file_get_contents("audit_form.json");

		$form = json_decode($form,true);

		$new_form = [];

		foreach ($form as $section) {

			if (isset($section['fields'])) {

				$new_qs = [];
				foreach ($section['fields'] as $q) {
					$form_q_stripped = str_replace(" ","",str_replace(":","",strtolower($q['question'])));
					$csv = fopen("recommendations.csv","r");

					while (($row = fgetcsv($csv, 0, ",")) !== FALSE) {
						//find matching question

						$q_stripped = strtolower($row[0]);
						$q_stripped = str_replace(":", "", $q_stripped);
						$q_stripped = str_replace(" ", "", $q_stripped);

						if ($form_q_stripped == $q_stripped) {
							//question found, add recommendation
							$q['recommendation'] = $row[1];
							break;
						}
					}
					$new_qs[] = $q;

					fclose($csv);
				}

				$section['fields'] = $new_qs;
			}

			if (isset($section['subSections'])) {

				$new_subsections = [];

				foreach ($section['subSections'] as $subsection) {
					if (isset($subsection['fields'])) {

						$new_qs = [];
						foreach ($subsection['fields'] as $q) {
							$form_q_stripped = str_replace(" ","",str_replace(":","",strtolower($q['question'])));
							$csv = fopen("recommendations.csv","r");

							while (($row = fgetcsv($csv, 0, ",")) !== FALSE) {
								//find matching question

								$q_stripped = strtolower($row[0]);
								$q_stripped = str_replace(":", "", $q_stripped);
								$q_stripped = str_replace(" ", "", $q_stripped);

								if ($form_q_stripped == $q_stripped) {
									//question found, add recommendation
									//strip "recommendation:" text
									$rec = str_replace("Recommendation: ", "", $row[1]);

									$q['recommendation'] = $rec;
									break;
								}
							}
							$new_qs[] = $q;

							fclose($csv);
						}

						$subsection['fields'] = $new_qs;
					}
					$new_subsections[] = $subsection;
				}

				$section['subSections'] = $new_subsections;
			}

			$new_form[] = $section;
		}

		echo json_encode($new_form);
		
	}

	public function form_generator() {
		$this->load->view("form_builder");	
	}

	public function app_version() {
		$version = $this->db
			->order_by("id","DESC")
			->get("app_version")
			->row_array();

		echo json_encode($version);
	}

	public function update_app() {
		$version = $this->db
			->order_by("id","DESC")
			->get("app_version")
			->row_array();

		if (!$version['manifest_generated']) {
			$this->generate_cache_manifest(false);

			$this->db
				->set("manifest_generated",1)
				->where("id",$version['id'])
				->update("app_version");
		}

		echo json_encode($version);
	}

	//generates new appcache manifest, and 
	public function generate_cache_manifest($output = true) {
		ini_set('display_errors', 'On');
		error_reporting(E_ALL);

		$css_dir = "/home/ipac/public_html/static/css";
		$js_dir = "/home/ipac/public_html/static/js";

		//echo $css_dir;

		$manifest = "manifest.appcache";

		$version = $this->db
			->order_by("id","DESC")
			->get("app_version")
			->row_array();

$manifest_header = 
"CACHE MANIFEST
# V".$version['version']." @ ".time()."
https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css
https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/fonts/fontawesome-webfont.woff2?v=4.7.0
https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css
https://cdn.tinymce.com/4/tinymce.js
";

		file_put_contents($manifest, $manifest_header);

		if (file_exists($css_dir)) {
			$dir = new DirectoryIterator($css_dir);
			foreach ($dir as $fileinfo) {
				$filename = $fileinfo->getFilename();

				if ($filename != '.' && $filename != "..") {
					echo $filename."<br>";
					
					file_put_contents($manifest, "static/css/".$filename."\n",FILE_APPEND);
				}
			}
		}

		if (file_exists($js_dir)) {
			$dir = new DirectoryIterator($js_dir);
			foreach ($dir as $fileinfo) {
				$filename = $fileinfo->getFilename();

				if ($filename != '.' && $filename != "..") {
					echo $filename."<br>";
					
					file_put_contents($manifest, "static/js/".$filename."\n",FILE_APPEND);
				}
			}
		}

		file_put_contents($manifest, "NETWORK:\n*",FILE_APPEND);

		if ($output) {

			echo "Appcache file generated:<br><br>";

			echo "<pre>";
			echo file_get_contents($manifest);
		}
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
