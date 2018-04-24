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


$raw_file = $argv[1];
$profile_file = $argv[2];
$out_file = $argv[3];
$rfh = fopen($out_file, 'w');

$raw_data = readTSVdata($raw_file);

$profile = read_profile($profile_file);

#summarize the data file
$data = array();
foreach($raw_data as $row => $record)
{
    $probe = $record['Substance'];
    $value = $record['Signal'];
    if(!array_key_exists($probe, $data))
    {
        $data[$probe] = array('sum'=>0,'count'=>0,'average'=>0);
    }
    $data[$probe]['sum'] = $data[$probe]['sum'] + $value;
    $data[$probe]['count']++;
}

#get the average for each probe
foreach($data as $probe => $info)
{

    $sum = $info['sum'];
    $count = $info['count'];
    if($sum > 0 && $count > 0)
    {
        $data[$probe]['average'] = $sum/$count;
    }
    else{
        $data[$probe]['average'] = 0;
    }

}

#compare against profile
$distances = array();
foreach($data as $probe => $info)
{
    $v1 = $info['average'];
    foreach($profile as $sero => $probes)
    {
        if(!array_key_exists($sero, $distances))
        {
            $distances[$sero] = 0;
        }
        if(!array_key_exists($probe, $probes))
        {
            $v2 = 0;
        }
        else{
            $v2 = $probes[$probe];
        }
        $dist = pow($v1-$v2,2);
        $distances[$sero] = $distances[$sero] + $dist;
    }
    
}
natsort($distances);
$min = min($distances);
$max = max($distances);
reset($distances);
$sum=0;
$count=0;
$match = '';
$match_dist = '';
$nn_match = '';
$nn_dist = '';
foreach($distances as $sero => $dist)
{
    $sum = $dist+$sum;
    
    if($count === 0)
    {
        $match = $sero;
        $match_dist = $dist;
    }
    if($count === 1)
    {
        $nn_match = $sero;
        $nn_dist = $dist;
    }
    $count++;
}
$ave = $sum/$count;
fputs($rfh, "Min:$min\nMax:$max\nAve:$ave\nMatch: $match\nMatch Dist: $match_dist\nNearest Neighbor: $nn_match\nNearest Neighbor Dist:$nn_dist\n");







function read_profile($file)
{
    $tsv = readTSVdata($file);
    $profile = array();
    foreach($tsv as $row => $data)
    {
        if(!array_key_exists($data['Serovar'], $profile))
        {
            $profile[$data['Serovar']] = array();
        }
        $sero = $data['Serovar'];
        $probe = $data['Probe'];
        $ave = $data['Average'];
        $profile[$sero][$probe] = $ave;
    }
    return $profile;
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