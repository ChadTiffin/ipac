
<html>
<body style="font-family:sans-serif;max-width: 900px;margin:auto;font-size:11pt">
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
</style>

<?php

foreach ($sections as $section): ?>

<?php

	echo "<h2 style='color:#3c554b;font-variant:small-caps;'>".$section['heading']."</h2>";

	if ($section['description_text']) 
		echo $m->render($section['description_text'],$variables);
	

	if ($section['has_guidelines']) {
		echo "<h3 style='color:#3c554b;font-variant:small-caps;margin-bottom:0;padding-bottom:0'>Guidelines and Requirements</h3>";
		echo "<hr style='background-color:#3c554b;color:#3c554b;margin-top:0;margin-bottom:10px;height:1px;border:none'>";

		echo $m->render($section['guidelines_text'],$variables);
	}

	if ($section['has_findings']) {
		echo "<h3 style='color:#3c554b;font-variant:small-caps;margin-bottom:0;padding-bottom:0'>Findings</h3>";
		echo "<hr style='background-color:#3c554b;color:#3c554b;margin-top:0;margin-bottom:10px;height:1px;border:none'>";
		//var_dump($section['image_urls']);
		if (isset($section['image_urls'])) {

			foreach ($section['image_urls'] as $image_url) 
				echo "<img src='".$image_url['url']."' width='".$image_url['width']."' height='".$image_url['height']."' style='vertical-align:top;'>";

		}

		echo $m->render($section['findings'],$variables);
	}

	echo "<div style='page-break-after:always'></div><div style='page-break-before:always'></div>";
	
endforeach; ?>
	
</body>
</html>