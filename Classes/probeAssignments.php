<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
include __DIR__."/ReadTsv.php";



class probeAssignments
{
    var $probe_name_list = array();
    var $majorAntigenProbes = array();
    
    
    function setProbeName($name,$value)
    {
        $this->probe_name_list[$name] = $value;
    }
    
    function setMajorAntigenProbeValue($name,$antigen,$value)
    {
        if(!array_key_exists($antigen, $this->majorAntigenProbes))
        {
            $this->majorAntigenProbes[$antigen] = array();
        }
        $this->majorAntigenProbes[$antigen][$name] = $value;
    }
    
    function getMajorAntigenProbeValue($name,$antigen)
    {
        if(!array_key_exists($antigen, $this->majorAntigenProbes))
        {
            return null;
        }
        return $this->majorAntigenProbes[$antigen][$name];
    }    
    
    function getProbeValue($name)
    {
        if(array_key_exists($name, $this->probe_name_list))
        {
            return $this->probe_name_list[$name];
        }
        return "";
    }
    
    function getProbeList()
    {
        return $this->probe_name_list;
    }
    
    function getMajorAntigenList()
    {
        return $this->majorAntigenProbes;
    }
    
    function processRawData($file)
    {
        $tsv = $this->readTSVdata($file);
        foreach($tsv as $row)
        {
            $probe_name = $row['ProbeName'];
            if($row['O'] == 'Y')
            {
                $this->setMajorAntigenProbeValue($probe_name, 'O', '');
                $this->setProbeName($probe_name, '');
            }
            if($row['H1'] == 'Y')
            {
                $this->setMajorAntigenProbeValue($probe_name, 'H1', '');
                $this->setProbeName($probe_name, '');
            }
            if($row['H2'] == 'Y')
            {
                $this->setMajorAntigenProbeValue($probe_name, 'H2', '');
                $this->setProbeName($probe_name, '');
            } 
            if($row['Serovar'] == 'Y')
            {
                $this->setMajorAntigenProbeValue($probe_name, 'Serovar', '');
                $this->setProbeName($probe_name, '');
            }
            if($row['Subspecies'] == 'Y')
            {
                $this->setMajorAntigenProbeValue($probe_name, 'Subspecies', '');
                $this->setProbeName($probe_name, '');
            }
            if($row['Control'] == 'Y')
            {
                $this->setMajorAntigenProbeValue($probe_name, 'Control', '');
                $this->setProbeName($probe_name, '');
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