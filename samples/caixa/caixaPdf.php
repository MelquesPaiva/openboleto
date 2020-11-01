<?php

require 'caixaSample.php';

use JasperPHP\Instructions;
use JasperPHP\PdfProcessor;
use JasperPHP\Report;
use Source\OpenBoleto\Pdf\Boleto;

$report = new Report(__DIR__ . "/../xml/bol01Files/boletoA4.jrxml", array());

Instructions::prepare($report);
$report->dbData = array(
    new Boleto('', $boleto),
    new Boleto('', $boleto),
);

$report->generate(array());

$report->out();
$pdf = PdfProcessor::get();

/** @var TCPDF $pdf */
$pdf->Output('boleto.pdf', "I");