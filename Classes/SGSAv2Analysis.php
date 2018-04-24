<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
//include __DIR__."/ReadTsv.php";
include __DIR__."/AntigenClassifyer.php";
error_reporting(E_ALL);
set_time_limit (3000);
ini_set('display_errors', 1);
mb_internal_encoding("UTF-8");



class SGSAv2downgrade extends AntigenClassifyer
{
var $header = array();
var $summary = array();
    
public function encodeToUtf8($string) {
     return mb_convert_encoding($string, "UTF-8", mb_detect_encoding($string, "UTF-8, ISO-8859-1, ISO-8859-15", true));
}

function preprocess($rawFile)
{
    $this->readRawData($rawFile);
    $this->calcAverage();
}

function addRow($inputFile)
{
    
    ksort($this->probes,SORT_NATURAL);
    $this->header = "InputFile\t".implode("\t",  array_keys($this->probes));
    $this->summary[] = $inputFile."\t".implode("\t",  array_values($this->probes));    
}

function writeData($file)
{
    $fh = fopen($file,'w');
    fwrite($fh,$this->header."\n".implode("\n",$this->summary)."\n");

    fclose($fh);
}


}

$files = glob("/Users/jrobertson/Desktop/Sent from Alere validation panel SGSAv2 2/*");
$out = '/Users/jrobertson/Desktop/out.txt';
$obj = new SGSAv2downgrade();
foreach($files as $in)
{
   echo "$in\n"; 
    $obj->preprocess($in);
    $obj->addRow($in);   
}
$obj->writeData($out); 


