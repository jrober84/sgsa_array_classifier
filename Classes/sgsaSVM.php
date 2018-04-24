<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
error_reporting(E_ALL);
set_time_limit (3000);
ini_set('display_errors', 1);
include __DIR__."/sgsaArray.php";
include __DIR__."/KauffmanScheme.php";
/*
$kauff_file = '/Users/jrobertson/Desktop/kauffman.txt';
$files = glob('/Users/jrobertson/Desktop/SGSA_ValidationData/*');
$svm = new svm();
$svm->setKauff($kauff_file);
foreach($files as $sgsa_file)
{
    $svm->setSGSA($sgsa_file);
}
*/
class svm
{
    var $kauff;
    var $sgsa;
    
    function setSGSA($file)
    {
        $this->sgsa = new sgsaArray();
        $this->sgsa->readRawData($file);
        $this->sgsa->calcAverage();
        
        //var_dump($candidates);
       // die;
        
        if(array_key_exists('114_l,w',$this->sgsa->probes) && array_key_exists('61_l,z13_l,v',$this->sgsa->probes))
    	{
    		if($this->sgsa->probes['114_l,w'] > $this->sgsa->probes['61_l,z13_l,v'])
    		{
    			unset($this->sgsa->probes['61_l,z13_l,v']);
    		}	
     		elseif($this->sgsa->probes['114_l,w'] < $this->sgsa->probes['61_l,z13_l,v'])
    		{
    			unset($this->sgsa->probes['114_l,w']);
    		}	   	
    	}  
    	
    	$candidates = $this->sgsa->classify();       
        foreach($candidates as $ant => $values)
        {
            asort($values,SORT_NUMERIC);
            $candidates[$ant] = array_reverse($values,$preserve_keys=TRUE);
            reset($candidates[$ant]);
        }
     
        if(count($candidates['O'])==0)
        {
            $candidates['O'] = array('-'=>1);
        }
       // var_dump($candidates);
        if(count($candidates['H1'])==0)
        {
            $candidates['H1'] = array('-'=>1);
        }   
        if(count($candidates['H2'])==0)
        {
            $candidates['H2'] = array('-'=>1);
        }
        //var_dump($candidates);
        $this->filterIncompatAntigens($candidates);

        
        foreach($candidates as $ant => $antigens)
        {
            if(count($antigens) <= 1)
            {
                continue;
            }
            $topAnt = '';
            $topScore = 0;
            $new_key = '';
            foreach($antigens as $k => $v)
            {

                if($topAnt == '')
                {
                    $topAnt = $k;
                    $topScore = $v;
                    $new_key.= $topAnt;
                    continue;
                }
                if($v < $topScore)
                {
                    unset($candidates[$ant][$k]);
                }

            }

        }

        
        /*var_dump($candidates);
        var_dump($this->sgsa->probes);
        var_dump($this->sgsa->rankedProbes);  */

        if(array_key_exists('120_invA',$this->sgsa->probes) && 
                array_key_exists('Biotin-Marke_2,5uM',$this->sgsa->probes) && 
                $this->sgsa->probes['120_invA'] > 30 && $this->sgsa->probes['Biotin-Marke_2,5uM'] > 70)
        {   
            //var_dump($this->sgsa->probes['120_invA'] );
            //var_dump($this->sgsa->probes['Biotin-Marke_2,5uM'] );
            $serovars = $this->getSerovars($candidates);
            if(count($serovars) === 0)
            {
                if(count($serovars) === 0)
                {
                    if(array_key_exists('A', $candidates['O']) && array_key_exists('A', $candidates['O']))
                    {
                        $temp = $candidates;
                         $temp['O'] = array('A/D'=>"");
                         $serovars = $this->getSerovars($temp);
                    }
                }
                if(count($serovars) === 0)
                {
                    $temp = $candidates['H1'];
                    $candidates['H1'] = $candidates['H2'];
                    $candidates['H2']  = $temp;
                    $serovars = $this->getSerovars($candidates);
                }
                
                if(count($serovars) === 0)
                {
                    $temp = $candidates['H1'];
                    $candidates['H1'] = $candidates['H2'];
                    $candidates['H2']  = $temp;                    
                }
                
            }
            //Serovar Exceptions
            if(array_key_exists('137_RHS-Gallinarum-2',$this->sgsa->rankedProbes))
            {
                
                if(array_key_exists('210_Pullorum-1',$this->sgsa->rankedProbes))
                {
                    if(array_key_exists('213_Enteritidis-1',$this->sgsa->rankedProbes))
                    {
                        $serovars = array('Enteritidis');
                    }
                    else{
                        $serovars = array('Pullorum');
                    }
                    
                }
            }
            
            
           // var_dump($candidates);
            $o = implode('/',array_keys($candidates['O']));
            if($o == 'A/D/Vi')
            {
                $o = 'A/D Vi';
            }
 
            
            $h1 = implode('/',array_keys($candidates['H1']));
            $h2 = implode('/',array_keys($candidates['H2']));
            if($o == 'A/D' && $h1 == 'a' && $h2 == '1,5' && array_key_exists('161_a-3', $this->sgsa->rankedProbes['H1']))
            {
                $o = 'A';
                $serovars = array('Paratyphi A');
            }
            
            
            $results = array('File'=>basename($file),'O'=>$o,'H1'=>$h1,'H2'=>$h2,'Serovar(s)'=>implode(',',$serovars)
                ,'FullProbes'=>$this->sgsa->probes,
                'RankedProbes'=>$this->sgsa->rankedProbes,'Status'=>'PASS');         
        }
        else{
            $results = array('File'=>basename($file),'O'=>'','H1'=>'','H2'=>'','Serovar(s)'=>''
                ,'FullProbes'=>$this->sgsa->probes,
                'RankedProbes'=>$this->sgsa->rankedProbes,'Status'=>'FAIL'); 
        }
        return $results;
    }
    
    function getSerovars($candidates)
    {
        $serovars = array();
        foreach($candidates['O'] as $o => $v1)
        {
            
            foreach($candidates['H1'] as $h1 => $v2)
            {
                foreach($candidates['H2'] as $h2 => $v3)
                {
                    $s = $this->kauff->getSerovar($o,$h1,$h2);
                    if(count($s) >= 1)
                    {
                        foreach($s as $sero => $v)
                        {
                            $serovars[] = $sero;
                        }
                        
                    }
                   // echo "$o:$h1:$h2\n";
                   // var_dump($serovars);
                    
                }
            }
        }
        natsort($serovars);
        return $serovars;
    }
    
    
    
    function setKauff($file)
    {
        $this->kauff = new KauffmanScheme();
        $this->kauff->process($file);
        
        
    }
    
    function filterIncompatAntigens($candidates)
    {
        if(count($candidates['O']) == 1)
        {
            if(count($candidates['H1']) > 1)
            {
                $filter = $this->kauff->getOh1AntCombinations(key($candidates['O']));
                $this->filterInclude($candidates['H1'],$filter);
            }
            if(count($candidates['H2']) > 1)
            {
                $filter = $this->kauff->getOh2AntCombinations(key($candidates['O']));
                $this->filterInclude($candidates['H2'],$filter);
            }
            if(count($candidates['H1']) > 1 && count($candidates['H2']) == 1)
            {
                $filter = $this->kauff->getH2H1AntCombinations(key($candidates['H2']));
                $this->filterInclude($candidates['H1'],$filter);
            }            
        }
        return $candidates;

    }
    function filterInclude($array,$filter)
    {
        foreach($array as $k => $v)
        {
            if(!array_key_exists($k, $filter))
            {
                unset($array[$k]);
            }
        }
        return $array;
    }
    
    function getKauff()
    {
        return $this->kauff;
    }
}


