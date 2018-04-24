<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of sgsaArray
 *
 * @author jrobertson
 */
class sgsaArray {
var $raw_data = array();
var $probes = array();
var $kauff;
var $rankedProbes = array();

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
    }
function filterProbe($array,$pName)
{
    
    if(array_key_exists($pName, $array) )
    {
        unset($array[$pName]);
    }
    return $array;
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
    
    function orderProbes()
    {
        natsort($this->probes);
    }
    
    function assignProbesToCategory()
    {
        $categories = array('O'=>array(),'H1'=>array(),'H2'=>array());
        $thresh = $this->getThresholds();
        foreach($this->probes as $probe => $value)
        {
            foreach($thresh as $mAG => $AntProbes)
            {
                if(array_key_exists($probe, $thresh[$mAG]))
                {
                    //echo "$mAG\t$probe\t$value\n";
                    if($value > $thresh[$mAG][$probe]*100)
                    {
                        $categories[$mAG][$probe] = $value;
                    }
                }
            }
            
        }
        return $categories;
    }
     
    function classify()
    {
        $this->orderProbes();
        $categories = $this->assignProbesToCategory();
        return $this->assignAntigens($categories);
    }
    
    function getThresholds()
    {
        $thresholds['H2']['1,5,7'] = 0.2;
        $thresholds['H2']['106_e,n,x-4'] = 0.3;
        $thresholds['H2']['107_e,n,z15 and e,n,x,z15'] = 0.75;
        $thresholds['H2']['108_ e,n,x,z15'] = 0.5;
        $thresholds['H2']['114_l,w'] = 0.6;
        $thresholds['H2']['116_z6 and z67 -2'] = 0.6;
        $thresholds['H2']['139_1,2'] = 0.35;
        $thresholds['H2']['61_l,z13_l,v'] = 0.75;
        $thresholds['H2']['71_z'] = 0.65;
        $thresholds['H2']['82_1,2_1,5_1,2,7'] = 0.25;
        $thresholds['H2']['83_1,7_Indiana'] = 0.75;
        $thresholds['H2']['86_1,7'] = 0.65;
        $thresholds['H2']['87_1,6'] = 0.7;
        $thresholds['H2']['88_1,5_1,2,7'] = 0.7;
        $thresholds['H2']['93_1,5'] = 0.7;
        $thresholds['H2']['94_1,5-2'] = 0.2;
        $thresholds['H2']['96_1,5-4'] = 0.3;
        $thresholds['O']['1_A'] = 0.3;
        $thresholds['O']['10_H'] = 0.2;
        $thresholds['O']['12_J'] = 0.2;
        $thresholds['O']['122_Vi'] = 0.8;
        $thresholds['O']['13_K'] = 0.2;
        $thresholds['O']['14_L'] = 0.2;
        $thresholds['O']['140_B-3'] = 0.7;
        $thresholds['O']['141_B-4'] = 0.8;
        $thresholds['O']['147_E1-2'] = 0.2;
        $thresholds['O']['16_M'] = 0.3;
        $thresholds['O']['18_N'] = 0.2;
        $thresholds['O']['19_0'] = 0.55;
        $thresholds['O']['21_P'] = 0.2;
        $thresholds['O']['23_S'] = 0.2;
        $thresholds['O']['24_V'] = 0.2;
        $thresholds['O']['26_Y'] = 0.8;
        $thresholds['O']['28_O-58'] = 0.2;
        $thresholds['O']['29_O-61'] = 0.8;
        $thresholds['O']['4_C1'] = 0.2;
        $thresholds['O']['9_G'] = 0.8;
        $thresholds['O']['D1/D2-5'] = 0.2;
       	$thresholds['O']['5_C2-C3'] = 0.2;
        $thresholds['H1']['107_e,n,z15 and e,n,x,z15'] = 0.75;
        $thresholds['H1']['108_ e,n,x,z15'] = 0.5;
        $thresholds['H1']['114_l,w'] = 0.6;
        $thresholds['H1']['154_m,t_mtpu_gmt-3'] = 0.8;
        $thresholds['H1']['157_fgt,fg,gmt'] = 0.2;
        $thresholds['H1']['161_a-3'] = 0.2;
        $thresholds['H1']['168_b-5'] = 0.2;
        $thresholds['H1']['169_y-2'] = 0.6;
        $thresholds['H1']['170_y-3'] = 0.6;
        $thresholds['H1']['173_g,m,s-4 (g,m[p]s)'] = 0.8;
        $thresholds['H1']['179_l,z13,z28-2 (FliC)'] = 0.3;
        $thresholds['H1']['183_g,m,q or g,q-2'] = 0.4;
        $thresholds['H1']['188_m,t_g,m,t'] = 0.8;
        $thresholds['H1']['190_g,p_g.p.s'] = 0.25;
        $thresholds['H1']['216_i-2'] = 0.2;
        $thresholds['H1']['219_gp-1'] = 0.3;
        $thresholds['H1']['224_O:61-k1'] = 0.8;
        $thresholds['H1']['33_c'] = 0.2;
        $thresholds['H1']['35_d-2'] = 0.2;
        $thresholds['H1']['36_e,h'] = 0.6;
        $thresholds['H1']['39_f sub 1-2'] = 0.35;
        $thresholds['H1']['40_f,g'] = 0.2;
        $thresholds['H1']['42_f,g,t-2'] = 0.75;
        $thresholds['H1']['44_f,g,s'] = 0.75;
        $thresholds['H1']['56_g,s,t or g,t'] = 0.75;
        $thresholds['H1']['57_g,z51'] = 0.2;
        $thresholds['H1']['59_k'] = 0.2;
        $thresholds['H1']['61_l,z13_l,v'] = 0.75;
        $thresholds['H1']['64_l,z28'] = 0.2;
        $thresholds['H1']['67_r'] = 0.2;
        $thresholds['H1']['69_r,[i]'] = 0.2;
        $thresholds['H1']['71_z'] = 0.65;
        $thresholds['H1']['73_z10'] = 0.7;
        $thresholds['H1']['74_z29'] = 0.7;
        $thresholds['H1']['75_z38'] = 0.2;
        $thresholds['H1']['76_z4,z23'] = 0.75;
        $thresholds['H1']['78_z4,z24'] = 0.2;
        $thresholds['H1']['d-4'] = 0.2;
        $thresholds['H1']['gmq-gq-5'] = 0.8;
        $thresholds['H1']['gp-3'] = 0.2;
        $thresholds['H1']['gt-1'] = 0.8;
        $thresholds['H1']['p'] = 0.2;
        $thresholds['H1']['r,[i]-4'] = 0.8;
        return $thresholds;
    }
    
    function getCandidateAntigens($mAG,$probes)
    {

        $lookup = $this->lookUpRules();
        $scores = array();
        foreach ($probes as $probe => $value)
        {
            //echo "$probe\n";
            foreach($lookup[$mAG] as $specAnt => $pList)
            {

                if(array_key_exists($probe, $pList))
                {
                    if(!array_key_exists($specAnt, $scores))
                    {
                        //echo "$probe\n";
                        $scores[$specAnt] = 0;
                    }
                    $scores[$specAnt] = $scores[$specAnt] + $value;
                }
            }
        }
        //var_dump($scores);
        natsort($scores);

        return array_reverse($scores,$preserve_keys=TRUE);
    }
    
    function assignAntigens($probes)
    {
        //var_dump($probes);
        //die;
        $this->rankedProbes = array('O'=>array(),'H1'=>array(),'H2'=>array());

        if(array_key_exists('122_Vi', $probes['O']) && !array_key_exists('1_A', $probes['O']))
        {
            $probes['O'] = $this->filterProbe($probes['O'],'122_Vi');
        }
        $probes['O'] = array_reverse($probes['O'],$preserve_keys=TRUE);
        reset($probes['O']);
        //var_dump($probes['O']);
        $topOprobe = key($probes['O']);
        //var_dump($topOprobe);
        if($topOprobe != '140_B-3' && $topOprobe != '141_B-4')
        {
            //echo "------";
            $probes['O'] = $this->filterProbe($probes['O'],'141_B-4');
            $probes['O'] = $this->filterProbe($probes['O'],'140_B-3');
        }        
        $o = $this->getCandidateAntigens('O',$probes['O']);
        natsort($o);
        $o = array_reverse($o,$preserve_keys = TRUE);
        //O Exceptions
        if(array_key_exists('1_A', $probes['O']) && count($probes['O']) < 4)
        {          
            if((!array_key_exists('122_Vi', $probes['O'])) && ( $probes['O']['1_A'] > $probes['O']['D1/D2-5'] && $probes['O']['1_A'] > 0.7))
            {
                $o = array('A'=>1);
            }
            if((!array_key_exists('122_Vi', $probes['O'])) && ($probes['O']['1_A'] < $probes['O']['D1/D2-5']))
            {
                $o = array('A'=>1,'D'=>1);
            }
        }
        if(!array_key_exists('122_Vi', $probes['O']))
        {
            foreach($o as $k => $v)
            {
                if($k === 'A Vi' || $k === 'D Vi' || $k === 'A/D Vi')
                {
                    unset($o[$k]);
                }
            }
        }             
        //H1 Probe Exceptions
        if(array_key_exists('gmq-gq-5', $probes['H1']))
        {
            if(array_key_exists('173_g,m,s-4 (g,m[p]s)', $probes['H1']))
            {
               $probes['H1'] = $this->filterProbe($probes['H1'],'gmq-gq-5');
               
            }
        } 
        
        if(array_key_exists('56_g,s,t or g,t', $probes['H1']))
        {
           $probes['H1'] = $this->filterProbe($probes['H1'],'173_g,m,s-4 (g,m[p]s)');

        } 
       // var_dump($probes['H1']);
       // die;
        
        
        
        asort($probes['H2'],SORT_NUMERIC);
        asort($probes['H1'],SORT_NUMERIC);
        //var_dump($probes['H2']);
        //die;
        $probes['H2'] = array_reverse($probes['H2'],$preserve_keys=TRUE);
        $probes['H1'] = array_reverse($probes['H1'],$preserve_keys=TRUE);
        //reset($probes['H1']);
        //reset($probes['H2']);
        //var_dump($probes['H2']);
        //die;
        $h1Top = key($probes['H1']);
        //var_dump($h1Top);
        $h2Top = key($probes['H2']);
        //var_dump($h2Top);
        //die;
        if($h1Top == $h2Top)
        {
            
            if($h2Top == '107_e,n,z15 and e,n,x,z15')
            {
                 unset($probes['H1'][$h1Top]);
            }
            elseif($h2Top == '114_l,w'){
            
                unset($probes['H1'][$h1Top]);
            }
            elseif(count($probes['H2']) > 1){
            
                unset($probes['H2'][$h1Top]);
            }
            else{
                unset($probes['H1'][$h1Top]);
            }
           
        }
        if(array_key_exists('87_1,6',$probes['H2']) && 
                array_key_exists('86_1,7',$probes['H2']))
        {

            if($probes['H2']['87_1,6'] > $probes['H2']['86_1,7'] )
            {
                unset($probes['H2']['86_1,7']);
            }
            
        }          
        if(array_key_exists('83_1,7_Indiana',$probes['H2']) || array_key_exists('86_1,7',$probes['H2']))
        {
            unset($probes['H2']['139_1,2']);
        }  
        $h2 = $this->getCandidateAntigens('H2',$probes['H2']);

        if(array_key_exists('116_z6 and z67 -2',$probes['H2']))
        {
            if(count($probes['H2'] == 1))
            {
                $h2 = array('z6'=>1);
            }  
        }
        
        $this->rankedProbes['O'] = $probes['O'];
        
        $this->rankedProbes['H2'] = $probes['H2'];
        
        if(array_key_exists("107_e,n,z15 and e,n,x,z15", $probes['H2']))
        {
            if(count($probes['H2'] == 1))
            {
                $h2 = array('e,n,z15'=>1);
            }
            elseif(array_key_exists("107_e,n,z15 and e,n,x,z15", $probes['H2']))
            {
                $h2 = array('e,n,x,z15'=>1);
            }
        }
        if(array_key_exists('88_1,5_1,2,7', $probes['H2']))
        {
            if(array_key_exists('82_1,2_1,5_1,2,7', $probes['H2']) && array_key_exists('83_1,7_Indiana', $probes['H2']))
            {
                
                $h2 = array('1,2,7'=>1);
            }
            elseif(array_key_exists('29_O-61', $probes['O']))
            {
                $h2 = array('1,5'=>1);
            }
            elseif(array_key_exists('94_1,5-2',$probes['H2']) 
                    || array_key_exists('96_1,5-4"',$probes['H2'])){
                 $h2 = array('1,5'=>1);
            }
            elseif(array_key_exists('82_1,2_1,5_1,2,7',$probes['H2'])){
                //echo "----";
                 //$h2 = array('1,2,7'=>1);
            }
        }
        
        if(array_key_exists('82_1,2_1,5_1,2,7', $probes['H2']))
        {
            //echo "----";
           // var_dump($probes['H2']);
            if(count($probes['H2']) == 1)
            {
                $h2 = array('1,2'=>1);
                 //echo "----";
            }
            elseif(array_key_exists('139_1,2', $probes['H2']) && count($probes['H2']) == 2){
               
                $h2 = array('1,2'=>1);
            }
        }
        
        if(array_key_exists('gmq-gq-5', $probes['H2']))
        {
            if(array_key_exists('173_g,m,s-4 (g,m[p]s)', $probes['H2']))
            {
               unset($probes['H2']['gmq-gq-5']);
            }
        }       
       
         //Rules
        //var_dump($probes['H1']);
        if(!array_key_exists('39_f sub 1-2', $probes['H1']))
        {
           
            $filter = array('157_fgt,fg,gmt' => 1,'40_f,g' => 1,'44_f,g,s' => 1,'42_f,g,t-2' => 1);
            foreach($probes['H1'] as $k => $v)
            {
                if(array_key_exists($k, $filter))
                {
                   unset($probes['H1'][$k]);
                }
            }
        }       

        $h1 = $this->getCandidateAntigens('H1',$probes['H1']);
        natsort($h1);
        $h1 = array_reverse($h1,$preserve_keys=TRUE);
        reset($h1);
        $topH1 = key($h1);
        //var_dump($topH1);
        if($topH1 == 'r,[i]' && array_key_exists('r,[i]', $h1) && !array_key_exists('i', $h1) )
        {
            if(array_key_exists('69_r,[i]', $probes['H1']))
            {
                if(array_key_exists('r,[i]-4', $probes['H1']))
                {
                    $h1 = array('r,[i]'=>1);
                }
                else{
                    $h1 = array('r'=>1);
                }
                
            }
            else{
                $h1 = array('r'=>1);
            }     
        }
        if(array_key_exists('m,t', $h1) && array_key_exists('g,m,t', $h1) )
        {
            if(     array_key_exists('188_m,t_g,m,t', $probes['H1'] ) &&(
                    array_key_exists('154_m,t_mtpu_gmt-3', $probes['H1']) ||
                    array_key_exists('157_fgt,fg,gmt', $probes['H1'])))
            {
                $h1 = array('g,m,t'=>1);
            }
            elseif(array_key_exists('154_m,t_mtpu_gmt-3', $probes['H1']) ||
                    array_key_exists('188_m,t_g,m,t', $probes['H1'] )){
                $h1 = array('m,t'=>1);
            }
            else{
                $h1 = array('m,t,p,u'=>1);
            }   
        } 
        if(!array_key_exists('39_f sub 1-2', $probes['H1']) && array_key_exists('g,m,s', $h1) && array_key_exists('g,m[p]s', $h1) )
        {
            if(array_key_exists("173_g,m,s-4 (g,m[p]s)", $probes['H1']) && 
                    !array_key_exists("56_g,s,t or g,t", $probes['H1']))
            {
                $h1 = array('g,m,s'=>1);
            }
            else{
                $h1 = array('g,m[p]s'=>1);
            }
        }        
        
        if(array_key_exists('r,[i]', $h1) && array_key_exists('i', $h1))
        {
            $h1 = array('i'=>1);
        }
        $this->rankedProbes['H1'] = $probes['H1'];
        if(count($o) > 1)
        {
            $top = '';
            $value = 0;
            foreach($o as $k => $v)
            {
                if($top == '')
                {
                    $top = $k;
                    $value = $v;
                    continue;
                }
                if($v < $value)
                {
                    unset($o[$k]);
                }
            }
        }

        if(count($h1) > 1)
        {
            $top = '';
            $value = 0;
            foreach($h1 as $k => $v)
            {
                if($top == '')
                {
                    $top = $k;
                    $value = $v;
                    continue;
                }
                if($v < $value)
                {
                    unset($h1[$k]);
                }
            }
        }

        if(count($h2) > 1)
        {
            $top = '';
            $value = 0;
            foreach($h2 as $k => $v)
            {
                if($top == '')
                {
                    $top = $k;
                    $value = $v;
                    continue;
                }
                if($v < $value)
                {
                    unset($h2[$k]);
                }
            }
        }          
        //var_dump($probes['H1']);
        //var_dump($probes['H2']);

        return array('O'=>$o,'H1'=>$h1,'H2'=>$h2);
        
    }
    
    
    
    
    function lookUpRules()
    {
        $lookUp['O']['58']['28_O-58'] = 1;
        $lookUp['O']['61']['29_O-61'] = 1;
        $lookUp['H2']['e,n,x,z15']['108_ e,n,x,z15'] = 1;
        $lookUp['H1']['e,n,x,z15']['108_ e,n,x,z15'] = 1;
        $lookUp['H2']['1,2']['139_1,2'] = 1;
        $lookUp['H2']['1,2']['82_1,2_1,5_1,2,7'] = 1;
        $lookUp['H2']['1,2,7']['82_1,2_1,5_1,2,7'] = 1;
        $lookUp['H2']['1,2,7']['88_1,5_1,2,7'] = 1;
        $lookUp['H2']['1,5']['82_1,2_1,5_1,2,7'] = 1;
        $lookUp['H2']['1,5']['88_1,5_1,2,7'] = 1;
        $lookUp['H2']['1,5']['93_1,5'] = 1;
        $lookUp['H2']['1,5']['94_1,5-2'] = 1;
        $lookUp['H2']['1,5']['96_1,5-4'] = 1;
        $lookUp['H2']['1,5,7']['1,5,7'] = 1;
        $lookUp['H2']['1,6']['87_1,6'] = 1;
        $lookUp['H2']['1,7']['83_1,7_Indiana'] = 1;
        $lookUp['H2']['1,7']['86_1,7'] = 1;
        $lookUp['O']['A']['1_A'] = 1;
        $lookUp['O']['A']['D1/D2-5'] = 1;
        $lookUp['H1']['a']['161_a-3'] = 1;
        //$lookUp['O']['A/D']['D1/D2-5'] = 1;
        $lookUp['O']['D']['1_A'] = 1;
        $lookUp['O']['D']['D1/D2-5'] = 1;
        $lookUp['O']['D']['D1/D2-5'] = 1;
        $lookUp['O']['B']['140_B-3'] = 1;
        $lookUp['O']['B']['141_B-4'] = 1;
        $lookUp['H1']['b']['168_b-5'] = 1;
        $lookUp['H1']['c']['33_c'] = 1;
        $lookUp['O']['C1']['4_C1'] = 1;
        $lookUp['O']['C2-C3']['5_C2-C3'] = 1;
        $lookUp['H1']['d']['35_d-2'] = 1;
        $lookUp['H1']['d']['d-4'] = 1;
        $lookUp['O']['E']['147_E1-2'] = 1;
        $lookUp['H1']['e,h']['36_e,h'] = 1;
        $lookUp['H2']['e,n,x']['106_e,n,x-4'] = 1;
        $lookUp['H2']['e,n,x,z15']['107_e,n,z15 and e,n,x,z15'] = 1;
        $lookUp['H1']['e,n,x,z15']['107_e,n,z15 and e,n,x,z15'] = 1;
        $lookUp['H2']['e,n,z15']['107_e,n,z15 and e,n,x,z15'] = 1;
        $lookUp['H1']['e,n,z15']['107_e,n,z15 and e,n,x,z15'] = 1;
        $lookUp['H1']['f sub 1-2']['39_f sub 1-2'] = 1;
        $lookUp['H1']['f sub 1-2']['39_f sub 1-2'] = 1;
        $lookUp['H1']['f,g']['157_fgt,fg,gmt'] = 1;
        $lookUp['H1']['f,g']['39_f sub 1-2'] = 1;
        $lookUp['H1']['f,g']['40_f,g'] = 1;
        $lookUp['H1']['f,g,s']['39_f sub 1-2'] = 1;
        $lookUp['H1']['f,g,s']['44_f,g,s'] = 1;
        $lookUp['H1']['f,g,t']['157_fgt,fg,gmt'] = 1;
        $lookUp['H1']['f,g,t']['39_f sub 1-2'] = 1;
        $lookUp['H1']['f,g,t']['42_f,g,t-2'] = 1;
        $lookUp['O']['G']['9_G'] = 1;
        $lookUp['H1']['g,m,q']['183_g,m,q or g,q-2'] = 1;
        $lookUp['H1']['g,m,q']['gmq-gq-5'] = 1;
        $lookUp['H1']['g,m,s']['173_g,m,s-4 (g,m[p]s)'] = 1;
        $lookUp['H1']['g,m,t']['154_m,t_mtpu_gmt-3'] = 1;
        $lookUp['H1']['g,m,t']['157_fgt,fg,gmt'] = 1;
        $lookUp['H1']['g,m,t']['188_m,t_g,m,t'] = 1;
        $lookUp['H1']['g,m[p]s']['173_g,m,s-4 (g,m[p]s)'] = 1;
        $lookUp['H1']['g,p']['190_g,p_g.p.s'] = 1;
        $lookUp['H1']['g,p']['gp-3'] = 1;
        $lookUp['H1']['g,q']['183_g,m,q or g,q-2'] = 1;
        $lookUp['H1']['g,q']['gmq-gq-5'] = 1;
        $lookUp['H1']['g,s,t']['56_g,s,t or g,t'] = 1;
        $lookUp['H1']['g,t']['56_g,s,t or g,t'] = 1;
        $lookUp['H1']['g,t']['gt-1'] = 1;
        $lookUp['H1']['g,z51']['57_g,z51'] = 1;
        $lookUp['H1']['g,p,s']['190_g,p_g.p.s'] = 1;
        $lookUp['H1']['g,p']['219_gp-1'] = 1;
        $lookUp['O']['H']['10_H'] = 1;
        $lookUp['H1']['i']['216_i-2'] = 1;
        $lookUp['O']['J']['12_J'] = 1;
        $lookUp['O']['K']['13_K'] = 1;
        $lookUp['H1']['k']['224_O:61-k1'] = 1;
        $lookUp['H1']['k']['59_k'] = 1;
        $lookUp['O']['L']['14_L'] = 1;
        $lookUp['H1']['l,v']['61_l,z13_l,v'] = 1;
        $lookUp['H2']['l,w']['114_l,w'] = 1;
        $lookUp['H1']['l,w']['114_l,w'] = 1;
        $lookUp['H1']['l,z13']['61_l,z13_l,v'] = 1;
        $lookUp['H2']['l,z13/l,v']['61_l,z13_l,v'] = 1;
        $lookUp['H1']['l,z13,z28']['179_l,z13,z28-2 (FliC)'] = 1;
        $lookUp['H1']['l,z28']['64_l,z28'] = 1;
        $lookUp['O']['M']['16_M'] = 1;
        $lookUp['H1']['m,t']['188_m,t_g,m,t'] = 1;
        $lookUp['H1']['m,t,p,u']['154_m,t_mtpu_gmt-3'] = 1;
        $lookUp['H1']['m,t']['154_m,t_mtpu_gmt-3'] = 1;
        $lookUp['O']['N']['18_N'] = 1;
        $lookUp['O']['O']['19_0'] = 1;
        $lookUp['O']['P']['21_P'] = 1;
        $lookUp['H1']['p']['p'] = 1;
        $lookUp['H1']['r']['67_r'] = 1;
        $lookUp['H1']['r,[i]']['69_r,[i]'] = 1;
        $lookUp['H1']['r,[i]']['r,[i]-4'] = 1;
        $lookUp['O']['S']['23_S'] = 1;
        $lookUp['O']['V']['24_V'] = 1;
        $lookUp['O']['A/D Vi']['1_A'] = 1;
        $lookUp['O']['A/D Vi']['D1/D2-5'] = 1;
        $lookUp['O']['A/D Vi']['122_Vi'] = 1;
        $lookUp['O']['Y']['26_Y'] = 1;
        $lookUp['H1']['y']['169_y-2'] = 1;
        $lookUp['H1']['y']['170_y-3'] = 1;
        $lookUp['H2']['z']['71_z'] = 1;
        $lookUp['H1']['z']['71_z'] = 1;
        $lookUp['H1']['z10']['73_z10'] = 1;
        $lookUp['H1']['z29']['74_z29'] = 1;
        $lookUp['H1']['z38']['75_z38'] = 1;
        $lookUp['H1']['z4,z23']['76_z4,z23'] = 1;
        $lookUp['H1']['z4,z24']['78_z4,z24'] = 1;
        $lookUp['H2']['z6']['116_z6 and z67 -2'] = 1;
        $lookUp['H2']['z67']['116_z6 and z67 -2'] = 1;
        return $lookUp;
    }
    

}
