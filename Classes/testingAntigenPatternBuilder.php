<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
include __DIR__."/ReadTsv.php";
error_reporting(E_ALL);
set_time_limit (4000);
ini_set('memory_limit','4000M');
ini_set('display_errors', 1);
mb_internal_encoding("UTF-8");

$file = '/Users/jrobertson/Desktop/merge.txt';
$data = readTSVdata($file);
$records = array();
$groups = array('O'=>array(),'H1'=>array(),'H2'=>array());
foreach($data as $row => $record)
{
    $key = $record['FileName'];
    $probe = $record['Probe'];
    $ave = $record['Average'];
    $oAnt = $record['SGSA_O'];
    $h1Ant = $record['SGSA_H1'];
    $h2Ant = $record['SGSA_H2'];
    if(!array_key_exists($key, $records))
    {
        $records[$key] = array();
    }
    $records[$key][$probe] = $ave; 
    $groups['O'][$oAnt][$key] = '';
    $groups['H1'][$h1Ant][$key] = '';
    $groups['H2'][$h2Ant][$key] = '';
    unset($data[$row]);
}

//Remove Invalid Records
foreach($records as $file => $probes)
{
    if($probes['Biotin-Marke_2,5uM'] < 0.8 || $probes['120_invA'] < 0.7)
    {
        unset($records[$file]);
        foreach($groups as $mAG => $antigens)
        {
            foreach($antigens as $specAnt => $files)
            {
                if(array_key_exists($file, $files))
                {
                    unset($groups[$mAG][$specAnt][$file]);
                }
            }
        }
    }
}


$majorAntGroup['O']['5_C2-C3'] = 0.2;
$majorAntGroup['H1']['40_f,g'] = 0.2;
$majorAntGroup['H1']['gp-3'] = 0.2;
$majorAntGroup['H1']['67_r'] = 0.2;
$majorAntGroup['H2']['82_1,2_1,5_1,2,7'] = 0.25;
$majorAntGroup['H1']['190_g,p_g.p.s'] = 0.25;
$majorAntGroup['O']['1_A'] = 0.3;
$majorAntGroup['H2']['106_e,n,x-4'] = 0.3;
$majorAntGroup['O']['16_M'] = 0.3;
$majorAntGroup['H2']['139_1,2'] = 0.35;
$majorAntGroup['H1']['39_f sub 1-2'] = 0.35;
$majorAntGroup['H1']['108_ e,n,x,z15'] = 0.5;
$majorAntGroup['H2']['108_ e,n,x,z15'] = 0.5;
$majorAntGroup['O']['19_0'] = 0.55;
$majorAntGroup['H1']['36_e,h'] = 0.6;
$majorAntGroup['H1']['114_l,w'] = 0.6;
$majorAntGroup['H2']['114_l,w'] = 0.6;
$majorAntGroup['H1']['169_y-2'] = 0.6;
$majorAntGroup['H1']['170_y-3'] = 0.6;
$majorAntGroup['H2']['116_z6 and z67 -2'] = 0.6;
$majorAntGroup['H2']['86_1,7'] = 0.65;
$majorAntGroup['H1']['71_z'] = 0.65;
$majorAntGroup['H2']['71_z'] = 0.65;
$majorAntGroup['H2']['93_1,5'] = 0.7;
$majorAntGroup['H2']['88_1,5_1,2,7'] = 0.7;
$majorAntGroup['H2']['87_1,6'] = 0.7;
$majorAntGroup['O']['140_B-3'] = 0.7;
$majorAntGroup['H1']['73_z10'] = 0.7;
$majorAntGroup['H1']['74_z29'] = 0.7;
$majorAntGroup['H2']['83_1,7_Indiana'] = 0.75;
$majorAntGroup['H1']['107_e,n,z15 and e,n,x,z15'] = 0.75;
$majorAntGroup['H2']['107_e,n,z15 and e,n,x,z15'] = 0.75;
$majorAntGroup['H1']['44_f,g,s'] = 0.75;
$majorAntGroup['H1']['42_f,g,t-2'] = 0.75;
$majorAntGroup['H1']['56_g,s,t or g,t'] = 0.75;
$majorAntGroup['H1']['gmq-gq-5'] = 0.75;
$majorAntGroup['H1']['61_l,z13_l,v'] = 0.75;
$majorAntGroup['H2']['61_l,z13_l,v'] = 0.75;
$majorAntGroup['H1']['76_z4,z23'] = 0.75;
$majorAntGroup['O']['141_B-4'] = 0.8;
$majorAntGroup['O']['9_G'] = 0.8;
$majorAntGroup['H1']['173_g,m,s-4 (g,m[p]s)'] = 0.8;
$majorAntGroup['H1']['gt-1'] = 0.8;
$majorAntGroup['H1']['188_m,t_g,m,t'] = 0.8;
$majorAntGroup['H1']['154_m,t_mtpu_gmt-3'] = 0.8;
$majorAntGroup['O']['29_O-61'] = 0.8;
$majorAntGroup['H1']['224_O:61-k1'] = 0.8;
$majorAntGroup['H1']['r,[i]-4'] = 0.8;
$majorAntGroup['O']['122_Vi'] = 0.8;
$majorAntGroup['O']['26_Y'] = 0.8;
$majorAntGroup['H2']['94_1,5-2'] = 0.2;
$majorAntGroup['H2']['96_1,5-4'] = 0.2;
$majorAntGroup['H2']['1,5,7'] = 0.2;
$majorAntGroup['H1']['161_a-3'] = 0.2;
$majorAntGroup['H1']['168_b-5'] = 0.2;
$majorAntGroup['H1']['33_c'] = 0.2;
$majorAntGroup['O']['4_C1'] = 0.2;
$majorAntGroup['H1']['35_d-2'] = 0.2;
$majorAntGroup['H1']['d-4'] = 0.2;
$majorAntGroup['O']['D1/D2-5'] = 0.2;
$majorAntGroup['O']['147_E1-2'] = 0.2;
$majorAntGroup['H1']['157_fgt,fg,gmt'] = 0.2;
$majorAntGroup['H1']['183_g,m,q or g,q-2'] = 0.2;
$majorAntGroup['H1']['57_g,z51'] = 0.2;
$majorAntGroup['H1']['219_gp-1'] = 0.2;
$majorAntGroup['O']['10_H'] = 0.2;
$majorAntGroup['H1']['216_i-2'] = 0.2;
$majorAntGroup['O']['12_J'] = 0.2;
$majorAntGroup['O']['13_K'] = 0.2;
$majorAntGroup['H1']['59_k'] = 0.2;
$majorAntGroup['O']['14_L'] = 0.2;
$majorAntGroup['H1']['179_l,z13,z28-2 (FliC)'] = 0.2;
$majorAntGroup['H1']['64_l,z28'] = 0.2;
$majorAntGroup['O']['18_N'] = 0.2;
$majorAntGroup['O']['28_O-58'] = 0.2;
$majorAntGroup['O']['21_P'] = 0.2;
$majorAntGroup['H1']['p'] = 0.2;
$majorAntGroup['H1']['69_r,[i]'] = 0.2;
$majorAntGroup['O']['23_S'] = 0.2;
$majorAntGroup['O']['24_V'] = 0.2;
$majorAntGroup['H1']['75_z38'] = 0.2;
$majorAntGroup['H1']['78_z4,z24'] = 0.2;


//Put probes into Antigen Categories
$processed = array();
foreach($records as $file => $probes)
{
    $processed[$file] = $majorAntGroup;
    foreach($majorAntGroup as $mAG => $probeList)
    {
        foreach($probeList as $probe => $value)
        {
            if($probes[$probe] > $value)
            {
                $processed[$file][$mAG][$probe] = $probes[$probe];
            }
            else{
                $processed[$file][$mAG][$probe] = 0;
            }
            
        }
        
    }
    unset($records[$file]);
    
}



foreach($processed as $file => $majorAntGroup)
{
    $line = $file;
    foreach($majorAntGroup as $mAG => $probes)
    {
        natsort($probes);
        $probes = array_reverse($probes);
        $count = 0;
        $line.="\t".$mAG;
        foreach($groups[$mAG] as $s => $array)
        {
            if(array_key_exists($file, $array))
            {
                $line.="\t".$s;
            }
        }
        foreach($probes as $probe => $value)
        {
            $count++;
            if($count == 5)
            {
                break;
            }

            $line.="\t".$probe."\t".$value;
        }
  
    } 
    echo $line."\n";
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



function thresholds()
{
    
$thresholds['O']['5_C2-C3'] = 0.2;
$thresholds['H1']['40_f,g'] = 0.2;
$thresholds['H1']['gp-3'] = 0.2;
$thresholds['H1']['67_r'] = 0.2;
$thresholds['H2']['82_1,2_1,5_1,2,7'] = 0.25;
$thresholds['H1']['190_g,p_g.p.s'] = 0.25;
$thresholds['O']['1_A'] = 0.3;
$thresholds['H2']['106_e,n,x-4'] = 0.3;
$thresholds['O']['16_M'] = 0.3;
$thresholds['H2']['139_1,2'] = 0.35;
$thresholds['H1']['39_f sub 1-2'] = 0.35;
$thresholds['H1']['108_ e,n,x,z15'] = 0.5;
$thresholds['H2']['108_ e,n,x,z15'] = 0.5;
$thresholds['O']['19_0'] = 0.55;
$thresholds['H1']['36_e,h'] = 0.6;
$thresholds['H1']['114_l,w'] = 0.6;
$thresholds['H2']['114_l,w'] = 0.6;
$thresholds['H1']['169_y-2'] = 0.6;
$thresholds['H1']['170_y-3'] = 0.6;
$thresholds['H2']['116_z6 and z67 -2'] = 0.6;
$thresholds['H2']['86_1,7'] = 0.65;
$thresholds['H1']['71_z'] = 0.65;
$thresholds['H2']['71_z'] = 0.65;
$thresholds['H2']['93_1,5'] = 0.7;
$thresholds['H2']['88_1,5_1,2,7'] = 0.7;
$thresholds['H2']['87_1,6'] = 0.7;
$thresholds['O']['140_B-3'] = 0.7;
$thresholds['H1']['73_z10'] = 0.7;
$thresholds['H1']['74_z29'] = 0.7;
$thresholds['H2']['83_1,7_Indiana'] = 0.75;
$thresholds['H1']['107_e,n,z15 and e,n,x,z15'] = 0.75;
$thresholds['H2']['107_e,n,z15 and e,n,x,z15'] = 0.75;
$thresholds['H1']['44_f,g,s'] = 0.75;
$thresholds['H1']['42_f,g,t-2'] = 0.75;
$thresholds['H1']['56_g,s,t or g,t'] = 0.75;
$thresholds['H1']['gmq-gq-5'] = 0.75;
$thresholds['H1']['61_l,z13_l,v'] = 0.75;
$thresholds['H2']['61_l,z13_l,v'] = 0.75;
$thresholds['H1']['76_z4,z23'] = 0.75;
$thresholds['O']['141_B-4'] = 0.8;
$thresholds['O']['9_G'] = 0.8;
$thresholds['H1']['173_g,m,s-4 (g,m[p]s)'] = 0.8;
$thresholds['H1']['gt-1'] = 0.8;
$thresholds['H1']['188_m,t_g,m,t'] = 0.8;
$thresholds['H1']['154_m,t_mtpu_gmt-3'] = 0.8;
$thresholds['O']['29_O-61'] = 0.8;
$thresholds['H1']['224_O:61-k1'] = 0.8;
$thresholds['H1']['r,[i]-4'] = 0.8;
$thresholds['O']['122_Vi'] = 0.8;
$thresholds['O']['26_Y'] = 0.8;
$thresholds['H2']['94_1,5-2'] = 0.2;
$thresholds['H2']['96_1,5-4'] = 0.2;
$thresholds['H2']['1,5,7'] = 0.2;
$thresholds['H1']['161_a-3'] = 0.2;
$thresholds['H1']['168_b-5'] = 0.2;
$thresholds['H1']['33_c'] = 0.2;
$thresholds['O']['4_C1'] = 0.2;
$thresholds['H1']['35_d-2'] = 0.2;
$thresholds['H1']['d-4'] = 0.2;
$thresholds['O']['D1/D2-5'] = 0.2;
$thresholds['O']['147_E1-2'] = 0.2;
$thresholds['H1']['157_fgt,fg,gmt'] = 0.2;
$thresholds['H1']['183_g,m,q or g,q-2'] = 0.2;
$thresholds['H1']['57_g,z51'] = 0.2;
$thresholds['H1']['219_gp-1'] = 0.2;
$thresholds['O']['10_H'] = 0.2;
$thresholds['H1']['216_i-2'] = 0.2;
$thresholds['O']['12_J'] = 0.2;
$thresholds['O']['13_K'] = 0.2;
$thresholds['H1']['59_k'] = 0.2;
$thresholds['O']['14_L'] = 0.2;
$thresholds['H1']['179_l,z13,z28-2 (FliC)'] = 0.2;
$thresholds['H1']['64_l,z28'] = 0.2;
$thresholds['O']['18_N'] = 0.2;
$thresholds['O']['28_O-58'] = 0.2;
$thresholds['O']['21_P'] = 0.2;
$thresholds['H1']['p'] = 0.2;
$thresholds['H1']['69_r,[i]'] = 0.2;
$thresholds['O']['23_S'] = 0.2;
$thresholds['O']['24_V'] = 0.2;
$thresholds['H1']['75_z38'] = 0.2;
$thresholds['H1']['78_z4,z24'] = 0.2;
return $thresholds;
}