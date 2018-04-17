
<html>
<head>
	<style type="text/css">
		h2, h3 {
			color:#3c554b;
		}

		hr {
			margin-top: 0;
			margin-bottom: 10px;
		}

		h2 {
			page-break-before: always;
		}

		table {
			width: 100%;
			border-collapse: collapse;
		}

		th {
			text-align: right;
			width: 40%;
			border-right: 1px solid black;
		}

		th, td {
			padding: 5px;
		}
	</style>
</head>
<body style="font-family:sans-serif;max-width: 900px;margin:auto;font-size:11pt">

<h1>Audit - <?=$company?>, <?=$location_name?></h1>
<p><?=$audit_date?>, by <?=$first_name?> <?=$last_name?></p>
<?php

foreach (json_decode($form_values) as $index => $section): ?>

	<h2><?php if (isset($section->heading)) echo $section->heading; ?></h2>

	<?php $this->load->view("audit_text_components/section_fields",['section' => $section]);

	if (isset($section->subSections)):

		foreach ($section->subSections as $section):

			$this->load->view("audit_text_components/section_fields",['section' => $section]);
				
		endforeach;

	endif ?>

	
<?php endforeach; ?>
	
</body>
</html>