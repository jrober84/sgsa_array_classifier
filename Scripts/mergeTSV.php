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

$tsv_dir = $argv[1];
$header = array('Spot', 'ID',	'Substance',	'Confidence',	'Signal',	'Valid',	'Background',	'Mean');
$hcount = count($header);
$files = glob($tsv_dir."*.txt");


$data = array();

foreach($files as $file)
{
    $tsv = readTSVdata($file); 
    $data[$file] = array();

    foreach($tsv as $row => $record)
    {
        $probe = $record['Substance'];
        $value = $record['Signal'];
        if(!array_key_exists($probe, $data[$file]))
        {
            $data[$file][$probe] = array('sum'=>0,'count'=>0,'average'=>0);
        }
        $data[$file][$probe]['sum'] = $data[$file][$probe]['sum'] + $value;
        $data[$file][$probe]['count']++;
    }

    foreach($data[$file] as $probe => $info)
    {

        $sum = $info['sum'];
        $count = $info['count'];
        if($sum > 0 && $count > 0)
        {
            $data[$file][$probe]['average'] = $sum/$count;
        }
        else{
            $data[$file][$probe]['average'] = 0;
        }

    }

} 
foreach($data as $file1 => $probes)
{
    foreach($probes as $probe => $value)
    {
        echo $file1."\t".$probe."\t".$value['average']."\n";
    }
   
}

/*
#pairwise comparisions
$distances = array();
foreach($data as $file1 => $probes1)
{
    $distances[$file1] = array();
    foreach($data as $file2 => $probes2)
    {
        
        if($file1 == $file2)
        {
            continue;
        }
        if(!array_key_exists($file2, $distances[$file1]))
        {
            $distances[$file1][$file2]=0;
        }
        $dist = 0;
        foreach($probes1 as $probe => $value)
        {
            //echo $file1."\t".$file2."\t".$probe."\t".($dist)."\n";
            if(array_key_exists($probe, $probes2))
            {
                $dist = pow($value['average']-$probes2[$probe]['average'],2);
            }
            else{
                $dist = pow($value['average'],2);
            }
            $distances[$file1][$file2]= $distances[$file1][$file2]+$dist; 
        }
                 
    }

}
echo "============================\n";
foreach($distances as $file1 => $comparisions)
{
    foreach($comparisions as $file2 => $dist)
    {
        echo "$file1\t$file2\t$dist\n";
    }
}


*/
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