<?php
include ('class.ezpdf.php');
$pdf =& new Cezpdf();
$pdf->selectFont('./fonts/Helvetica.afm');

$pdf->ezText("teste"); 
$pdf->ezStream();
?>