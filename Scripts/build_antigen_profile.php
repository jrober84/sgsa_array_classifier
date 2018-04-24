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

$tsv_file = $argv[1];
$tsv = readTSVdata($tsv_file); 
$majorAntigens = array('H1'=>array('161_a-3','168_b-5','33_c','35_d-2','264_d-4','36_e,h','108_ e,n,x,z15','107_e,n,z15 and e,n,x,z15',
    '213_Enteritidis-1','39_f sub 1-2','40_f,g','44_f,g,s ','42_f,g,t-2','157_fgt,fg,gmt','183_g,m,q or g,q-2','173_g,m,s-4 (g,m[p]s)',
    '190_g,p_g.p.s','56_g,s,t or g,t','57_g,z51','250_gmq-gq-5','219_gp-1','249_gp-3','252_gt-1 ','216_i-2','59_k','114_l,w','61_l,z13_l,v',
    '179_l,z13,z28-2 (FliC)','64_l,z28','188_m,t_g,m,t','154_m,t_mtpu_gmt-3','224_O:61-k1','317_p','210_Pullorum-1','67_r','69_r,[i]',
    '283_r,[i]-4','169_y-2','170_y-3','71_z','73_z10','74_z29','75_z38','76_z4,z23','78_z4,z24'),
'H2'=>array('108_ e,n,x,z15','107_e,n,z15 and e,n,x,z15','114_l,w','61_l,z13_l,v','71_z','82_1,2_1,5_1,2,7','83_1,7_Indiana','86_1,7','87_1,6','88_1,5_1,2,7','93_1,5','94_1,5-2','96_1,5-4','106_e,n,x-4','116_z6 and z67 -2','139_1,2','223_Kottbus','273_1,5,7'),
'O'=>array('161_a-3','1_A','4_C1','5_C2-C3','9_G','10_H','12_J','13_K','14_L','16_M','18_N','19_0','21_P','23_S','24_V','26_Y','28_O-58','29_O-61','140_B-3','141_B-4','147_E1-2'));

$probe_list = array();
$antigens = array('O'=>array(),'H1'=>array(),'H2'=>array());
foreach($tsv as $row => $record)
{
    $sero = $record['ObsSer'];
    $Oant = $record['O-Ant'];
    $H1ant = $record['H1-Ant'];
    $H2ant = $record['H2-Ant'];
    $probe = $record['Probe'];
    if(strpos($probe, 'Biotin'))
    {
        $probe = 'Biotin-Marke_2,5uM';
    }
    $probe_list[$probe] = $probe;
    $value = $record['Average'];
    if(!array_key_exists($Oant, $antigens['O']))
    {
        $antigens['O'][$Oant] = array();
    }
    if(!array_key_exists($H1ant, $antigens['H1']))
    {
        $antigens['H1'][$H1ant] = array();
    }
    if(!array_key_exists($H2ant, $antigens['H2']))
    {
        $antigens['H2'][$H2ant] = array();
    }
    
    if(!array_key_exists($probe, $antigens['O'][$Oant]))
    {
        $antigens['O'][$Oant][$probe] = array('count'=>0,'sum'=>'0','ave'=>0);
    }
    if(!array_key_exists($probe, $antigens['H1'][$H1ant]))
    {
        $antigens['H1'][$H1ant][$probe]  = array('count'=>0,'sum'=>'0','ave'=>0);
    }
    if(!array_key_exists($probe, $antigens['H2'][$H2ant]))
    {
        $antigens['H2'][$H2ant][$probe]  = array('count'=>0,'sum'=>'0','ave'=>0);
    }
 
    $antigens['O'][$Oant][$probe] ['count']++;
    $antigens['H1'][$H1ant][$probe] ['count']++;
    $antigens['H2'][$H2ant][$probe] ['count']++;
    $antigens['O'][$Oant][$probe] ['sum'] = $antigens['O'][$Oant][$probe] ['sum']+$value;
    $antigens['H1'][$H1ant][$probe] ['sum'] = $antigens['H1'][$H1ant][$probe] ['sum']+$value;
    $antigens['H2'][$H2ant][$probe] ['sum'] = $antigens['H2'][$H2ant][$probe] ['sum']+$value;
}

    
#Average the Values
foreach($antigens as $antigen => $types)
{
    foreach($types as $specificAnt => $probes)
    {
        foreach($probes as $probe => $value)
        {

            if($value['count'] > 0)
            {
                $antigens[$antigen][$specificAnt][$probe]['ave'] = $value['sum']/$value['count'];
            }
        }
        
    }
}

#Normalize the probes to housekeeping genes
foreach($antigens as $antigen => $types)
{
    foreach($types as $specificAnt => $probes)
    {
        if(array_key_exists('Biotin-Marke_2,5uM', $probes))
        {
            $control1 = $probes['Biotin-Marke_2,5uM']['ave'];
        }
        else{
            unset($antigens[$antigen][$specificAnt]);
            continue;
        }

        if($control1 === 0)
        {
            unset($antigens[$antigen][$specificAnt]);
            continue;            
        }
        foreach($probes as $probe => $value)
        {
            $rawValue = $antigens[$antigen][$specificAnt][$probe]['ave'];
            $normalized = ($rawValue/$control1);
            //$antigens[$antigen][$specificAnt][$probe]['ave'] = $normalized;
        }
        
    }
}



#write the profile
echo "AntigenCategory\tSpecific Antigen\tProbe\tAverage\tWeight\n";


foreach($antigens as $antigen => $types)
{
    foreach($types as $specificAnt => $probes)
    {

        $out_string = "$antigen:$specificAnt";
        foreach($probe_list as $probe => $junk)
        {
            if(!array_key_exists($probe, $probes))
            {
                $value = 0;
            }
            else{
                $value = $probes[$probe]['ave'];
            }
            if(!in_array($probe, $majorAntigens[$antigen]))
            {
                $weight = 0;               
            }
            else{
                $weight = 1;
            }
            echo "$antigen\t$specificAnt\t$probe\t$value\t$weight\n";
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