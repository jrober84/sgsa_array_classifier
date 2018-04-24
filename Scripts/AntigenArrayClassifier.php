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
    if(preg_match('/Biotin/',$probe))
    {
        $probe = 'Biotin-Marke_2,5uM';
    }
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

#Normalize the data
$control1 =0;
$control2 = 0;

if(array_key_exists('Biotin-Marke_2,5uM', $data))
{
    $control1 = $data['Biotin-Marke_2,5uM']['average'];
}

if($control1 === 0 )
{
    echo "Error the controls fall below the threshold\n";
    echo "Biotin-Marke_2,5uM: ".$control1."\n'";
    echo "0,1M NaPP Standard pH 9: ".$control2."\n";
    die;
}
foreach($data as $probe => $info)
{
    $rawValue = $info['average'];
    //$normalized = ($rawValue/$control1);
    //$data[$probe]['average'] = $normalized;

}
#compare against profile
$distances = array();
foreach($data as $probe => $info)
{
    $v1 = $info['average'];
    foreach($profile as $majorAnt => $antigens)
    {
        if(!array_key_exists($majorAnt, $distances))
        {
            $distances[$majorAnt] = array();
        }
        foreach($antigens as $specAnt => $probes)
        {
            if(!array_key_exists($specAnt, $distances[$majorAnt]))
            {
                $distances[$majorAnt][$specAnt] = 0;
            }
            if(!array_key_exists($probe, $probes))
            {
                $v2 = 0;
                $weight =  0;
            }
            else{
                $v2 = $probes[$probe]['value'];
                $weight = $probes[$probe]['weight'];
            }
            $dist = pow($v1-$v2,2)*$weight;
            $distances[$majorAnt][$specAnt] = $distances[$majorAnt][$specAnt] + $dist;
                   
        }

    }
    
}

#write Results
$antFormula = array();
foreach($distances as $majorAnt => $antigens)
{
    natsort($antigens);
    $sum = 0;
    $count =0;
    foreach($antigens as $specAnt => $dist)
    {
        $sum = $sum + $dist;
        if($count === 0)
        {
            $min = $dist;
            $match = $specAnt;
            $match_dist = $dist;
        }
        if($count === 1)
        {
            $nn_match = $specAnt;
            $nn_dist = $dist;
        }
        $max = $dist;
        $count++;
    }
    $ave = $sum/$count;
    $antFormula[$majorAnt] = "$match";
    fputs($rfh, "Major Antigen:$majorAnt\nMin:$min\nMax:$max\nAve:$ave\nMatch: $match\nMatch Dist: $match_dist\nNearest Neighbor: $nn_match\nNearest Neighbor Dist:$nn_dist\n");


    
}

fputs($rfh,"Antigenic Formula: ".$antFormula['O'].":".$antFormula['H1'].":".$antFormula['H2']."\n");





function read_profile($file)
{
    $tsv = readTSVdata($file);
    $profile = array();
    foreach($tsv as $row => $data)
    {

        $majorAnt = $data['AntigenCategory'];
        $specAnt = $data['Specific Antigen'];
        $probe = $data['Probe'];
        $ave = $data['Average'];
        $weight = $data['Weight'];
        if(!array_key_exists($majorAnt, $profile))
        {
            $profile[$majorAnt] = array();
        }
        if(!array_key_exists($specAnt, $profile[$majorAnt]))
        {
            $profile[$majorAnt][$specAnt] = array();
        }
        $profile[$majorAnt][$specAnt][$probe] = array('value'=>$ave,'weight'=>$weight);
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