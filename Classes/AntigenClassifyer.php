<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
include __DIR__."/ReadTsv.php";

/*$threshFile = '/Users/jrobertson/NetBeansProjects/SGSA/DataFiles/probe_thresholds.txt';
$profileFile  = '/Users/jrobertson/NetBeansProjects/SGSA/DataFiles/antigen_probe_average_profiles.txt';
$files = glob("/Users/jrobertson/Desktop/SGSA_ValidationData/*");
$obj = new AntigenClassifyer($profileFile,$threshFile);
foreach($files as $rawFile)
{
    $obj->classify($rawFile);
}
*/

class AntigenClassifyer
{
var $raw_data = array();
var $probes = array();
var $expProbes = array();
var $distances = array();
var $antFormula = array();
var $results = array();
var $thresholds = array();

function setAntFormula($f)
{
    $this->antFormula = $f;
}

function getAntFormula()
{
    return $this->antFormula;
}

function getAllDistances()
{
    return $this->distances;
}

function setAntDist($mAG,$specAnt,$value)
{
    if(!array_key_exists($mAG, $this->distances))
    {
        $this->distances[$mAG] = array();
    }
    if(!array_key_exists($specAnt, $this->distances[$mAG]))
    {
        $this->distances[$mAG][$specAnt] = 0;
    }    
    $this->distances[$mAG][$specAnt] = $value;
}

function getAntDist($mAG,$specAnt)
{
    if(!array_key_exists($specAnt, $this->distances[$mAG]))
    {
        return 0;
    }
    return $this->distances[$mAG][$specAnt];
}

function orderDistances()
{
    foreach($this->distances as $group => $values)
    {
        natsort($values);
        $this->distances[$group] = $values;    
    }
}

function isAboveThresh($name,$value)
{
    return ($this->getProbeThresh($name) < $value);
}

function setProbeThresh($name,$thresh)
{
    $this->thresholds[$name] = $thresh;
}

function getProbeThresh($name)
{
    if(!array_key_exists($name, $this->thresholds))
    {
        return 0;
    }
    return $this->thresholds[$name];
}

function calcEuclideanDist($array1,$array2)
{

    $dist = 0;
    foreach($array1 as $key => $value)
    {
        if(array_key_exists($key, $array2))
        {
            //echo "$key\t$value\t$array2[$key]\n";
            $dist = $dist + pow($value-$array2[$key],2);
        }
    }
    return $dist;
}

function calcProfileDistances()
{
    foreach($this->profile as $mAG => $antigens)
    {
        foreach($antigens as $specAnt => $sProbes)
        {
            $this->setAntDist($mAG, $specAnt, $this->calcEuclideanDist($this->probes, $sProbes));
        }
    }
}

function readRawData($file)
{
     #summarize the data file
    $raw_data = $this->readTSVdata($file);
    //var_dump($raw_data);
    
    $data = array();
    foreach($raw_data as $row => $record)
    {
        //echo "$row\n";
        $probe = preg_replace('/"/','',$record['Substance']);
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
   $this->raw_data = $data;
   //return;
   //die;
}

function calcAverage()
{
    #get the average for each probe
    foreach($this->raw_data as $probe => $info)
    {
        $sum = $info['sum']*100;
        $count = $info['count'];
        if($sum > 0 && $count > 0)
        {
            $ave = $sum/$count;
        }
        else{
            $ave = 0;
        }
        $this->probes[$probe] = $ave;
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
    $line = preg_replace("/—|–|-/","-",preg_replace("/\"/","",trim($tsv->getNextLine())));

    while($line != "" )
    {
//        /echo $line."\n";
        $line = preg_replace("/—|–|-/","-",preg_replace("/\"/","",$line));
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

function readThresholds($file)
{
    $tsv = $this->readTSVdata($file);
    foreach($tsv as $row => $data)
    {
        $probe = $data['probe'];
        $value = $data['threshold']*100;
        $this->setProbeThresh($probe, $value);
    }
}

function read_profile($file)
{
    $tsv = $this->readTSVdata($file);
    $profile = array();
    foreach($tsv as $row => $data)
    {

        $majorAnt = $data['AntigenCategory'];
        $specAnt = $data['Specific Antigen'];
        $probe = $data['Probe'];
        $ave = $data['Average']*100;
        if(!$this->isAboveThresh($probe, $ave))
        {
           // $ave = 0;
        }
        if(!array_key_exists($majorAnt, $profile))
        {
            $profile[$majorAnt] = array();
        }
        if(!array_key_exists($specAnt, $profile[$majorAnt]))
        {
            $profile[$majorAnt][$specAnt] = array();
        }
        $profile[$majorAnt][$specAnt][$probe] = $ave;
    }
    $this->profile = $profile;
}

/*
function __construct($profileFile,$thresholdFile)
{
    //$this->readThresholds($thresholdFile);
    //$this->read_profile($profileFile);
}*/

function profileDistances()
{
    foreach($this->profile as $mAG => $antigens)
    {
        foreach($antigens as $specAnt => $sProbes)
        {
            foreach($antigens as $s2 => $p2)
            {
                if($s2 == $specAnt)
                {
                    //continue;
                }
                //var_dump($p2);
                //var_dump($sProbes);
                echo "$mAG\t$specAnt\t$s2\t".$this->calcEuclideanDist($p2, $sProbes)."\n";
               // die;
            }
            
        }
    }    
}

function callAntigens()
{
    $formula = '';
    foreach($this->distances as $mAG => $indDist)
    {
        reset($indDist);
        $formula.=key($indDist).":";
    }
    $this->setAntFormula($formula);
}


function classify($rawFile)
{
    $this->readRawData($rawFile);
    $this->calcAverage();
    $this->calcProfileDistances();
    $this->orderDistances();
    $this->callAntigens();
    //echo $rawFile."\t".$this->getAntFormula()."\n";
}


}
