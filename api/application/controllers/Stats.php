<?php

class Stats extends CI_Controller {

	public function nos() {

		$questions = [
			["Policies are written and readily available to staff","Policies are written and readily availble to staff"],
			["Cleaning blood/body fluid spills"],
			["Point of care HH stations"],
			["Qualifications and training of DHCP involved in reprocessing"],
			["There is a one-way work flow from dirty to clean to prevent cross-contamination"],
			["Dirty devices are kept separate from clean devices"],
			["Internal and external pouch/pack chemical indicators (CI) are placed appropriately in and/ or on each package (if not part of the pouch/pack wrap)"],
			["Sterilizer is tested with a biological indicator (BI) each day and for each type of cycle the sterilizer is used"],
			["Dental devices are not used until the BI is checked","Dental devices are not used until the BI is checked or evaluation of a Class 5 or 6 CI and the specific cycle physical parameters "],
			["Records are kept to document that all sterilization parameters have been met (e.g., BIs, CIs, time/temperature/pressure readings)"],
			["A dental device is not used until the CI(s) is/are checked"],
			["Sterilizer mechanical printout is checked and signed each cycle by the person responsible for releasing the load"],
			["Packaged, sterilized devices are stored securely in a manner that keeps them clean, dry and prevents contamination (e.g., drawer, upper cupboard)"]
		];

		$audits = $this->db->get_where("audits",[
				"form_template_id" => 1
			])->result();

		$results = [];

		echo "<table>
		<tr>
			<th>Question</th>
			<th>Total 'No' Answers</th>
			<th>Total Answers</th>
		</tr>";

		foreach ($questions as $q) {

			echo "<tr>";

			$qTotal = 0;
			$qTotalNos = 0;

			foreach ($q as $qVersion) {

				$qVersionNormalized = preg_replace("/[^A-Za-z0-9]/", '', $qVersion);
				$qVersionNormalized = strtolower($qVersionNormalized);

				foreach ($audits as $index =>  $audit) {
					$auditValues = json_decode($audit->form_values);

					foreach ($auditValues as $auditSection) {
						if (isset($auditSection->fields)) {
							//search questions
							foreach ($auditSection->fields as $field) {
								$fieldQNormalized = preg_replace("/[^A-Za-z0-9]/", '', $field->question);
								$fieldQNormalized = strtolower($fieldQNormalized);

								if ($fieldQNormalized == $qVersionNormalized && $field->value == "no") {
									$qTotalNos++;
									$qTotal++;
								}
								elseif ($fieldQNormalized == $qVersionNormalized)
									$qTotal++;
							}
						}
						if (isset($auditSection->subSections)) {
							foreach ($auditSection->subSections as $subSection) {
								if (isset($subSection->fields)) {
									foreach ($subSection->fields as $field) {

										$fieldQNormalized = preg_replace("/[^A-Za-z0-9]/", '', $field->question);
										$fieldQNormalized = strtolower($fieldQNormalized);

										if ($fieldQNormalized == $qVersionNormalized && $field->value == "no") {
											$qTotalNos++;
											$qTotal++;
										}
										elseif ($fieldQNormalized == $qVersionNormalized)
											$qTotal++;

									}
								}

							}
						}
					}
				}
			}

			echo "<th style='text-align:left'>".$q[0]."</th>
				<td>$qTotalNos</td>
				<td>$qTotal</td>";

			echo "</tr>";

		}

		echo "</table>";
	}
}
