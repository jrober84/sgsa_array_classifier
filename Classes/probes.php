<?php

error_reporting(E_ALL);
set_time_limit (3000);
ini_set('display_errors', 1);

include __DIR__."/ReadTsv.php";
/*
$config = '/Users/jrobertson/Desktop/Classifier_Data/probe.config';
$probes = new probes($config);
var_dump($probes);
*/
class probes
{
    var $probes = array();
    var $probe_values = array();
    var $probe_thresholds = array();
    var $probe_assignments = array();

    function __construct($probe_config_file) {
        $data = $this->readTSVdata($probe_config_file);
        $this->init($data);
    }
    
    function init($data_array)
    {
        foreach($data_array as $row)
        {
            $antigen = $row['MajorAntigen'];
            $probe_name = $row['Probe'];
            $antigen_name = $row['AntigenName'];
            $threshold = $row['Threshold'];
            $this->addProbe($probe_name,$antigen,$antigen_name,$threshold);
        }
    }
    
    function addProbe($probe_name,$antigen,$antigen_name,$threshold)
    {
        $this->addAntigen($antigen);   
        $this->addAntigenName($antigen,$antigen_name);
        $this->addProbeID($antigen,$antigen_name,$probe_name);
        $this->setThreshold($probe_name,$threshold);
        $this->setProbeValue($probe_name,0);
        $this->probes[$antigen][$probe_name][$antigen_name] = $threshold;
        $this->setProbeAssignment($probe_name,$antigen);
    }
    
    function setProbeAssignment($probe,$antigen)
    {
        if(!array_key_exists($probe, $this->probe_assignments))
        {
            $this->probe_assignments[$probe] = array();
        }
        $this->probe_assignments[$probe][$antigen] = '';
    }
    
    function getProbeAssignment($probe)
    {
        if(array_key_exists($probe, $this->probe_assignments))
        {
            return $this->probe_assignments[$probe];
        }
        return array();
    }
    
    
    function getProbeValue($probe_name)
    {
        return $this->probe_values[$probe_name];
    }    
    
    function addAntigen($antigen)
    {
        if(!array_key_exists($antigen, $this->probes))
        {
            $this->probes[$antigen] = array();
        }
    }
    
    
    function addAntigenName($antigen,$antigen_name)
    {
        if(!array_key_exists($antigen_name, $this->probes[$antigen]))
        {
            $this->probes[$antigen][$antigen_name] = array();
        }
    }
 
    function addProbeID($antigen,$antigen_name,$probe_name)
    {
        if(!array_key_exists($probe_name, $this->probes[$antigen][$antigen_name]))
        {
            $this->probes[$antigen][$antigen_name][$probe_name] = 0;
        }
    }
    
    function setProbeValue($probe,$value)
    {
        $this->probe_values[$probe] = $value;
    }
 
    
    function setThreshold($probe_name,$threshold)
    {
        $this->probe_thresholds[$probe_name] = $threshold;
    }
    
    function getThreshold($probe_name)
    {
        if(!array_key_exists($probe_name, $this->probes))
        {
            return 1;
        }
        return $this->probes[$probe_name];
    }
    function getThresholds()
    {
        return $this->probe_thresholds;
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
    
    
}
