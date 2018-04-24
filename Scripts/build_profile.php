<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

error_reporting(E_ALL);
ini_set("memory_limit","3000M");
ini_set('display_errors', 1);
//Includes
include "/Users/jrobertson/NetBeansProjects/SGSA_Pipeline/Classes/ReadTsv.php";

$tsv_file = $argv[1];
$tsv = readTSVdata($tsv_file); 
$serovars = array();
foreach($tsv as $row => $record)
{
    $sero = $record['ObsSer'];
    $probe = $record['Probe'];
    $value = $record['Average'];
    if(!array_key_exists($sero, $serovars))
    {
        $serovars[$sero] = array();
    }
    if(!array_key_exists($probe, $serovars[$sero]))
    {
        $serovars[$sero][$probe]['sum'] = 0;
        $serovars[$sero][$probe]['count'] = 0;
    }
    $serovars[$sero][$probe]['sum'] = $serovars[$sero][$probe]['sum']+ $value;     
     $serovars[$sero][$probe]['count']++;    
}

foreach($serovars as $sero => $probes)
{
    foreach($probes as $probe => $info)
    {
        if($info['count'] > 0)
        {
            echo $sero."\t$probe\t".$info['sum']."\t".$info['count']."\t".($info['sum']/$info['count'])."\n";
        }
        else{
            echo $sero."\t$probe\t".$info['sum']."\t".$info['count']."\t"."0\n";
        }
            
        
    }   
    
}


function readTSVdata($file)
{

//Load TSV into memory
$tsv = new ReadTSV($file);
$header = $tsv->getHeader();
$hCount = count($header);
$dataArray = array();
$row = 1;
$line = trim($tsv->getNextLine());
while($line != "")
{
    if(!preg_match("/\w/",$line))
    {
        continue;
    }
    $fields = explode("\t",$line);
    $dataArray[$row] = array();
    for($i=0; $i< $hCount; $i++)
    {
        if(!preg_match("/\w/",$header[$i]))
        {
            continue;
        }
        if(!array_key_exists($i, $fields))
        {
            $dataArray[$row][$header[$i]] = null;
        }
        else{
            $dataArray[$row][$header[$i]] = $fields[$i];
        }
        
    }
    $line = trim($tsv->getNextLine());
    $row++;
}
unset($tsv);

return $dataArray;
}