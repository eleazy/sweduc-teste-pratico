<?php
include ('class.ezpdf.php');
$pdf =& new Cezpdf();
$pdf->selectFont('./fonts/Helvetica.afm');
$i=0;
while ($i < 11)
{
	$codigo = "codigo " . $i;
	$nome = "nome " . $i;
	$telefone = "telefone " . $i;
	$observacao = "observacao " . $i;
	$data[] = array('C�digo' => $codigo,
	'Nome' => $nome,
	'Telefone' => $telefone,
	'Observa��o' => $observacao
	);
	$i = $i + 1;
}
$pdf->ezTable($data);
$pdf->ezStream();
?>