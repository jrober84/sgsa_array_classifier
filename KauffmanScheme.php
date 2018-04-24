<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include __DIR__."/Classes/ReadTsv.php";
error_reporting(E_ALL);
set_time_limit (3000);
ini_set('display_errors', 1);
/*$file = '/Users/jrobertson/Desktop/kauffman.txt';
$k = new KauffmanScheme();

$k->process($file);
*/

class KauffmanScheme
{
    var $serovars = array();
    var $oH1 = array();
    var $oH2 = array();
    var $H2H1 = array();
        
    function getSerovar($o,$h1,$h2)
    {
        if(array_key_exists($o,$this->serovars) && array_key_exists($h1,$this->serovars[$o]) && array_key_exists($h2, $this->serovars[$o][$h1]))
        {
            return $this->serovars[$o][$h1][$h2];
        }
        return array();
    }
    
    function getOh1AntCombinations($o)
    {
        if(array_key_exists($o, $this->oH1))
        {
            return $this->oH1[$o];
        }
        else{
            return array();
        }
        
    }
    function getOh2AntCombinations($o)
    {
        if(array_key_exists($o, $this->oH2))
        {
            return $this->oH2[$o];
        }
        else{
            return array();
        }
        
    }
    function geth2h1AntCombinations($h2)
    {
        if(array_key_exists($h2, $this->H2H1))
        {
            return $this->H2H1[$h2];
        }
        return array();
    }    
    

    function process($file)
    {
        $tsv = $this->readTSVdata($file);
        foreach($tsv as $row => $record)
        {
            $serovar = $record['Serovar'];
            $o = $record['O'];
            $h1 = $record['H1'];
            $h2 = $record['H2'];
            $prob = $record['Probability'];
            $this->addSerovar($serovar,$o,$h1,$h2,$prob);
            $this->oH1 = $this->addLookUp($this->oH1, $o, $h1, "");
            $this->oH2 = $this->addLookUp($this->oH2, $o, $h2, "");
            $this->H2H1 = $this->addLookUp($this->H2H1, $h2, $h1, "");            
        }
    }
    
    function addSerovar($serovar,$o,$h1,$h2,$prob)
    {
        if(!array_key_exists($o, $this->serovars))
        {
            $this->serovars[$o] = array();
        }
        if(!array_key_exists($h1, $this->serovars[$o]))
        {
            $this->serovars[$o][$h1] = array();
        }
        if(!array_key_exists($h2, $this->serovars[$o][$h1]))
        {
            $this->serovars[$o][$h1][$h2] = array();
        }
        $this->serovars[$o][$h1][$h2][$serovar] = $prob;

    }
    
    function addLookUp($array,$pKey,$sKey,$value)
    {
        
        if(!array_key_exists($pKey,$array))
        {
            $array[$pKey] = array();
        }
        if(!array_key_exists($sKey,$array[$pKey]))
        {
            $array[$pKey][$sKey] = $value;
        } 
        return $array;
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