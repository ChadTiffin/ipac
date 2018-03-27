<html>
<body>

<?php

foreach ($sections as $section):

	echo "<h2>".$section['heading']."</h2>";

	if ($section['description_text']) 
		echo $m->render($section['description_text'],$variables);
	

	if ($section['has_guidelines']) {
		echo "<h3>Guidelines and Requirements</h3>";

		echo $m->render($section['guidelines_text'],$variables);
	}

	if ($section['has_findings']) {
		echo "<h3>Findings</h3>";

		if (strlen($section['images']) > 2) {

			$images = json_decode($section['images']);
		
			foreach ($images as $image) {

				if (file_exists(UPLOAD_FOLDER.$image)) 
					echo "<img src='".UPLOAD_FOLDER.$image."' style='max-width:300px'>";

			}
		}

		echo $m->render($section['findings'],$variables);
	}

	
endforeach; ?>
	
</body>
</html>