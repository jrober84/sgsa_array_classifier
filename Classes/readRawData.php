<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
error_reporting(E_ALL);
set_time_limit (3000);
ini_set('display_errors', 1);



class readRawData{
    var $data = array();
    var $counts = array();
    var $sums = array();
    
    function __construct($file) {
        $rows = $this->readTSVdata($file);
        $this->process($rows);
        
    }
    
    function process($data_array)
    {
        foreach($data_array as $row)
        {
            $probe = $row['Substance'];
            if(substr($probe,0,6) == 'Biotin')
            {
                $probe = 'Biotin-Marke_2,5µM';
            }
            $value = $row['Signal'];
            if(!array_key_exists($probe, $this->data))
            {
                $this->counts[$probe] = 1;
                $this->sums[$probe] = $value;     
                $this->data[$probe] = 0;
            }
            else{
                if(($row['Valid'] == 0) )
                {
                    $this->counts[$probe]++;
                    $this->sums[$probe] = $this->sums[$probe] + $value; 
                }
                
                                 
            }
        }
        $this->average();
    }
    
    function average()
    {
        foreach($this->data as $probe => $value)
        {
            $sum = $this->sums[$probe];
            $count = $this->counts[$probe];
            if($count > 0)
            {
                $this->data[$probe] = $sum/$count;
            }
        }
    }
    
    function getProbeValue($probe)
    {
        return $this->data[$probe];
    }
    
    function getProbeNames()
    {
        return array_keys($data);
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
