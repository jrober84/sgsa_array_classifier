<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class readProfile{
var $samples = array();

    function __construct($file) {
        $data = $this->readTSVdata($file);
        $this->process($data);

    }
    
    function process($data)
    {
        foreach($data as $row)
        {
            $sampleID = $row['File'];
            foreach($row as $element => $value)
            {
                if($element == 'File')
                {
                    $this->samples[$value] = array();
                    $sampleID = $value;
                }
                else{
                    $this->samples[$sampleID][$element] = $value;
                }   
            }
        }
    }
    
    function getIDS()
    {
        return array_keys($this->samples);
    }
    
    function getSampleData($id)
    {
        return $this->samples[$id];
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