<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 *

$lookup = new seroLookUp();
$lookup->init('/Users/jrobertson/NetBeansProjects/SGSA/data_files/SerovarAntigenicFormula.txt');
var_dump($lookup->getSerovar('A', 'a', '[1,5]'));
 */
class seroLookUp
{

var $lookup = array();

function addSerovar($o,$h1,$h2,$serovar)
{
    if(!array_key_exists($o, $this->lookup))
    {
        $this->lookup[$o] = array();
    }
    if(!array_key_exists($h1, $this->lookup[$o]))
    {
        $this->lookup[$o][$h1] = array();
    }
    if(array_key_exists($h2, $this->lookup[$o][$h1]))
    {
        $this->lookup[$o][$h1][$h2] = $this->lookup[$o][$h1][$h2]."|".$serovar;
    }
    else{
        $this->lookup[$o][$h1][$h2] = $serovar;
    }
    
}

function getSerovar($o,$h1,$h2)
{
    $endash = html_entity_decode('&#x2013;', ENT_COMPAT, 'UTF-8');
    $o = str_replace($endash, '-', $o);
    $h1 = str_replace($endash, '-', $h1);
    $h2 = str_replace($endash, '-', $h2);
    if(array_key_exists($o, $this->lookup) 
            && array_key_exists($h1, $this->lookup[$o]) 
            && array_key_exists($h2, $this->lookup[$o][$h1]))
    {
        return $this->lookup[$o][$h1][$h2];
    }
    else{
        return '';
    }
}

function init($file)
{
    $contents = explode("\n",file_get_contents($file));
    $header = '';
    foreach($contents as $line)
    {
        $row = explode("\t",$line);
        if($header == '')
        {
            foreach ($row as $i => $element)
            {
                $header[$element] = $i;
            }
            continue;
        }
        $o = $row[$header['O']];
        $h1 = $row[$header['H1']];
        $h2 = $row[$header['H2']];
        $serovar = $row[$header['Serovar']];
        $this->addSerovar($o, $h1, $h2, $serovar);
    }
    
}

}