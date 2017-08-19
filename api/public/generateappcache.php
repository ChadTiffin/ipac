<?php

ini_set('display_errors', 'On');
error_reporting(E_ALL);

$css_dir = "static/css";
$js_dir = "static/js";

$manifest = "manifest.appcache";

unlink($manifest);

$manifest_header = 
"CACHE MANIFEST
# V".time()."
https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css
https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/fonts/fontawesome-webfont.woff2?v=4.7.0
https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css
https://cdn.tinymce.com/4/tinymce.js
";

file_put_contents($manifest, $manifest_header);

$dir = new DirectoryIterator($css_dir);
foreach ($dir as $fileinfo) {
	$filename = $fileinfo->getFilename();

	if ($filename != '.' && $filename != "..") {
		echo $filename."<br>";
		
		file_put_contents($manifest, "static/css/".$filename."\n",FILE_APPEND);
	}
}

$dir = new DirectoryIterator($js_dir);
foreach ($dir as $fileinfo) {
	$filename = $fileinfo->getFilename();

	if ($filename != '.' && $filename != "..") {
		echo $filename."<br>";
		
		file_put_contents($manifest, "static/js/".$filename."\n",FILE_APPEND);
	}
}

file_put_contents($manifest, "NETWORK:\n*",FILE_APPEND);

echo "Appcache file generated:<br><br>";

echo "<pre>";
echo file_get_contents($manifest);