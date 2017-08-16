<?php

ini_set('display_errors', 'On');
error_reporting(E_ALL);

//require_once("../application/libraries/dompdf/Dompdf.php");

require_once '../application/libraries/dompdf/autoload.inc.php';

use Dompdf\Dompdf;

$pdf = new Dompdf();

$pdf->loadHtml("<p>Hellow world!</p>");

$pdf->setPaper('Letter','portrait');

$pdf->render();

$pdf->stream();