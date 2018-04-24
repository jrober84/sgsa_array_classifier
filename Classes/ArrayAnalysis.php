<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
include __DIR__."/ReadTsv.php";
error_reporting(E_ALL);
set_time_limit (3000);
ini_set('display_errors', 1);

class ArrayAnalysis
{
    var $raw_data_file;
    var $profile_file;
    var $out_file;
    var $profile = array();
    var $data = array();
    var $isError = FALSE;
    var $distances = array();
    var $rfh;
    var $probes = array();
    var $oAnt;
    var $h1Ant;
    var $h2Ant;
    var $oProbe;
    var $h1Probe;
    var $h2Probe;
    var $results;
    var $thresholds;
    
    function setOprobe($p)
    {
        $this->oProbe = $p;
    }
    function getOprobe()
    {
        return $this->oProbe;
    }
    function setH1probe($p)
    {
        $this->h1Probe = $p;
    }
    function getH1probe()
    {
        return $this->h1Probe;
    }
    function setH2probe($p)
    {
        $this->h2Probe = $p;
    }
    function getH2probe()
    {
        return $this->h2Probe;
    } 
    function getOant()
    {
        return $this->oAnt;
    }
    function setOant($ant)
    {
        $this->oAnt = $ant;
    }
    function getH1ant()
    {
        return $this->h1Ant;
    }
    function setH1ant($ant)
    {
        $this->h1Ant = $ant;
    }   
     function getH2ant()
    {
        return $this->h2Ant;
    }
    function setH2ant($ant)
    {
        $this->h2Ant = $ant;
    }   
    
    
    function setError($status)
    {
        $this->isError = $status;
    }
    function getError()
    {
        return $this->isError;
    }
    
    function init($profile_file)
    {
        $this->profile = $this->read_profile($profile_file);
    }
    
    function ProcessRawData()
    {
         #summarize the data file
        $raw_data = $this->readTSVdata($this->getRawDataFile());
        $data = array();
        foreach($raw_data as $row => $record)
        {
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

        #get the average for each probe
        foreach($data as $probe => $info)
        {

            $sum = $info['sum']*100;
            $count = $info['count'];
            if($sum > 0 && $count > 0)
            {
                $this->data[$probe]['average'] = $sum/$count;
            }
            else{
                $this->data[$probe]['average'] = 0;
            }
            if( $this->data[$probe]['average'] < 2)
            {
                $this->data[$probe]['average'] = 0;
            }
            $this->data[$probe]['sum'] = $sum;
            $this->data[$probe]['count'] = $count;
            $this->probes[$probe] = $this->data[$probe]['average'];
        }   
       // var_dump($this->probes);
    }
    
    
    function getProbeStats($probe_name)
    {
        if(array_key_exists($probe_name, $this->data))
        {
            return $this->data[$probe_name];
        }
        else{
            return array();
        }
    }
    
    
    function isControlValid($probe_name)
    {
        $control = $this->getProbeStats($probe_name);
        if(count($control) === 0)
        {
            return FALSE;
        }
        elseif($control['average'] === 0)
        {
            return FALSE;
        }
        else{
            return TRUE;
        }
    }
    
    function setRawDataFile($file)
    {
        $this->raw_data_file = $file;
    }
    
    function getRawDataFile()
    {
        return $this->raw_data_file;
    }
    
    function setProfileFile($file)
    {
        $this->profile_file = $file;
    }
    
    function getProfileFile()
    {
        return $this->profile_file;
    }
    
    function setOutFile($file)
    {
        $this->out_file = $file;
        $this->rfh = fopen($file,'w');
    }
    
    function getOutFile()
    {
        return $this->out_file;
    }
    
    function read_profile()
    {
        $tsv = $this->readTSVdata($this->getProfileFile());
        $profile = array();
        foreach($tsv as $row => $data)
        {

            $majorAnt = $data['AntigenCategory'];
            $specAnt = $data['Specific Antigen'];
            $probe = $data['Probe'];
            $ave = $data['Average']*100;
            if(!$this->isAboveThresh($probe, $ave))
            {
                $ave = 0;
            }
            $weight = $data['Weight'];
            if(!array_key_exists($majorAnt, $profile))
            {
                $profile[$majorAnt] = array();
            }
            if(!array_key_exists($specAnt, $profile[$majorAnt]))
            {
                $profile[$majorAnt][$specAnt] = array();
            }
            $profile[$majorAnt][$specAnt][$probe] = array('value'=>$ave,'weight'=>$weight);
        }
      $this->profile = $profile;
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
        $line = preg_replace("/—|–|-/","-",preg_replace("/\"/","",trim($tsv->getNextLine())));
        $row++;
    }
    unset($tsv);

    return $dataArray;
}    

    function writeResults()
    {
        if($this->getError() === FALSE)
        {
            foreach($this->results as $majorAnt => $data)
            {
                $line = "Major Antigen:\t".$majorAnt."\n".
                "Min:\t".$data['MIN']."\n".
                "Max:\t".$data['MAX']."\n".
                "Ave:\t".$data['AVE']."\n".
                "Match Name:\t".$data['match_name']."\n".
                "Match Dist:\t".$data['match_dist']."\n".
                "Nearest Neighbor Name:\t".$data['nn_name']."\n".
                "Nearest Neighbor Dist\t".$data['nn_dist']."\n";
                $this->writeLine($line);
            }
        }
        else{
            fputs($this->rfh,"Antigenic Formula: -:-:-\nWarning Control is below Threshold, Cannot Call Antigenit Formula\n\n");
            
        }  
        #Output Ordered Probes
        natsort($this->probes);
        $this->probes = array_reverse($this->probes);
        fputs($this->rfh,"\nProbe\tAverage Signal\n");
        foreach($this->probes as $probe => $ave)
        {
            fputs($this->rfh,"$probe\t$ave\n");
        }
        
    }
    function writeLine($line)
    {
        fputs($this->rfh,$line);
    }
    
    
    function readThresholds()
    {
        $tsv = $this->readTSVdata('/var/www/html/SGSA/DataFiles/probe_thresholds.txt');
        foreach($tsv as $row => $data)
        {
            $probe = $data['probe'];
            $value = $data['threshold']*100;
            $this->setProbeThresh($probe, $value);
        }
    }
    
    function isAboveThresh($name,$value)
    {
        return $this->getProbeThresh($name) < $value;
    }
    function setProbeThresh($name,$thresh)
    {
        $this->thresholds[$name] = $thresh;
    }
    function getProbeThresh($name)
    {
        return $this->thresholds[$name];
    }
    function categorizeProbes()
    {
$majorAntGroup['H1']['gp-3'] = 0;
$majorAntGroup['H1']['40_f,g'] = 0;
$majorAntGroup['H1']['67_r'] = 0;
$majorAntGroup['H1']['190_g,p_g.p.s'] = 0;
$majorAntGroup['H1']['190_g,p_g.p.s'] = 0;
$majorAntGroup['H1']['82_1,2_1,5_1,2,7'] = 0;
$majorAntGroup['H1']['82_1,2_1,5_1,2,7'] = 0;
$majorAntGroup['H1']['82_1,2_1,5_1,2,7'] = 0;
$majorAntGroup['H1']['106_e,n,x-4'] = 0;
$majorAntGroup['H1']['39_f sub 1-2'] = 0;
$majorAntGroup['H1']['108_ e,n,x,z15'] = 0;
$majorAntGroup['H1']['114_l,w'] = 0;
$majorAntGroup['H1']['169_y-2'] = 0;
$majorAntGroup['H1']['170_y-3'] = 0;
$majorAntGroup['H1']['36_e,h'] = 0;
$majorAntGroup['H1']['71_z'] = 0;
$majorAntGroup['H1']['86_1,7'] = 0;
$majorAntGroup['H1']['73_z10'] = 0;
$majorAntGroup['H1']['74_z29'] = 0;
$majorAntGroup['H1']['87_1,6'] = 0;
$majorAntGroup['H1']['88_1,5_1,2,7'] = 0;
$majorAntGroup['H1']['88_1,5_1,2,7'] = 0;
$majorAntGroup['H1']['93_1,5'] = 0;
$majorAntGroup['H1']['107_e,n,z15 and e,n,x,z15'] = 0;
$majorAntGroup['H1']['107_e,n,z15 and e,n,x,z15'] = 0;
$majorAntGroup['H1']['223_Kottbus'] = 0;
$majorAntGroup['H1']['gmq-gq-5'] = 0;
$majorAntGroup['H1']['gmq-gq-5'] = 0;
$majorAntGroup['H1']['42_f,g,t-2'] = 0;
$majorAntGroup['H1']['44_f,g,s'] = 0;
$majorAntGroup['H1']['56_g,s,t or g,t'] = 0;
$majorAntGroup['H1']['56_g,s,t or g,t'] = 0;
$majorAntGroup['H1']['61_l,z13_l,v'] = 0;
$majorAntGroup['H1']['61_l,z13_l,v'] = 0;
$majorAntGroup['H1']['76_z4,z23'] = 0;
$majorAntGroup['H1']['83_1,7_Indiana'] = 0;
$majorAntGroup['H1']['154_m,t_mtpu_gmt-3'] = 0;
$majorAntGroup['H1']['154_m,t_mtpu_gmt-3'] = 0;
$majorAntGroup['H1']['154_m,t_mtpu_gmt-3'] = 0;
$majorAntGroup['H1']['173_g,m,s-4 (g,m[p]s)'] = 0;
$majorAntGroup['H1']['173_g,m,s-4 (g,m[p]s)'] = 0;
$majorAntGroup['H1']['183_g,m,q or g,q-2'] = 0;
$majorAntGroup['H1']['188_m,t_g,m,t'] = 0;
$majorAntGroup['H1']['183_g,m,q or g,q-2'] = 0;
$majorAntGroup['H1']['188_m,t_g,m,t'] = 0;
$majorAntGroup['H1']['224_O:61-k1'] = 0;
$majorAntGroup['H1']['gt-1'] = 0;
$majorAntGroup['H1']['r,[i]-4'] = 0;
$majorAntGroup['H1']['1,2,7-5'] = 0;
$majorAntGroup['H1']['152_m,t_mtpu_gmt'] = 0;
$majorAntGroup['H1']['152_m,t_mtpu_gmt'] = 0;
$majorAntGroup['H1']['152_m,t_mtpu_gmt'] = 0;
$majorAntGroup['H1']['153_m,t_mtpu_gmt-2'] = 0;
$majorAntGroup['H1']['153_m,t_mtpu_gmt-2'] = 0;
$majorAntGroup['H1']['153_m,t_mtpu_gmt-2'] = 0;
$majorAntGroup['H1']['157_fgt,fg,gmt'] = 0;
$majorAntGroup['H1']['157_fgt,fg,gmt'] = 0;
$majorAntGroup['H1']['157_fgt,fg,gmt'] = 0;
$majorAntGroup['H1']['161_a-3'] = 0;
$majorAntGroup['H1']['168_b-5'] = 0;
$majorAntGroup['H1']['gpu-gp-fg-gst'] = 0;
$majorAntGroup['H1']['gpu-gp-fg-gst'] = 0;
$majorAntGroup['H1']['gpu-gp-fg-gst'] = 0;
$majorAntGroup['H1']['gpu-gp-fg-gst'] = 0;
$majorAntGroup['H1']['179_l,z13,z28-2 (FliC)'] = 0;
$majorAntGroup['H1']['180_l,z13,z28-2 (FljB)'] = 0;
$majorAntGroup['H1']['180_l,z13,z28-2 (FljB)'] = 0;
$majorAntGroup['H1']['216_i-2'] = 0;
$majorAntGroup['H1']['219_gp-1'] = 0;
$majorAntGroup['H1']['d-4'] = 0;
$majorAntGroup['H1']['1,5,7'] = 0;
$majorAntGroup['H1']['pepT-3'] = 0;
$majorAntGroup['H1']['33_c'] = 0;
$majorAntGroup['H1']['35_d-2'] = 0;
$majorAntGroup['H1']['45_f,g,m,t'] = 0;
$majorAntGroup['H1']['46_f,g,m,t-2'] = 0;
$majorAntGroup['H1']['57_g,z51'] = 0;
$majorAntGroup['H1']['59_k'] = 0;
$majorAntGroup['H1']['64_l,z28'] = 0;
$majorAntGroup['H1']['gpu-2'] = 0;
$majorAntGroup['H1']['69_r,[i]'] = 0;
$majorAntGroup['H1']['75_z38'] = 0;
$majorAntGroup['H1']['78_z4,z24'] = 0;
$majorAntGroup['H1']['94_1,5-2'] = 0;
$majorAntGroup['H1']['96_1,5-4'] = 0;
$majorAntGroup['H1']['fgt-gmt-mptu-mt'] = 0;
$majorAntGroup['H1']['fgt-gmt-mptu-mt'] = 0;
$majorAntGroup['H1']['fgt-gmt-mptu-mt'] = 0;
$majorAntGroup['H1']['fgt-gmt-mptu-mt'] = 0;
$majorAntGroup['H1']['gp-2'] = 0;
$majorAntGroup['H1']['p'] = 0;
$majorAntGroup['H2']['139_1,2'] = 0;
$majorAntGroup['H2']['108_ e,n,x,z15'] = 0;
$majorAntGroup['H2']['116_z6 and z67 -2'] = 0;
$majorAntGroup['H2']['116_z6 and z67 -2'] = 0;
$majorAntGroup['H2']['107_e,n,z15 and e,n,x,z15'] = 0;
$majorAntGroup['H2']['107_e,n,z15 and e,n,x,z15'] = 0;
$majorAntGroup['H2']['61_l,z13_l,v'] = 0;
$majorAntGroup['H2']['61_l,z13_l,v'] = 0;
$majorAntGroup['H2']['179_l,z13,z28-2 (FliC)'] = 0;
$majorAntGroup['O']['137_RHS-Gallinarum-2'] = 0;
$majorAntGroup['O']['5_C2-C3'] = 0;
$majorAntGroup['O']['1_A'] = 0;
$majorAntGroup['O']['16_M'] = 0;
$majorAntGroup['O']['19_0'] = 0;
$majorAntGroup['O']['210_Pullorum-1'] = 0;
$majorAntGroup['O']['140_B-3'] = 0;
$majorAntGroup['O']['Dublin-nupC'] = 0;
$majorAntGroup['O']['122_Vi'] = 0;
$majorAntGroup['O']['122_Vi'] = 0;
$majorAntGroup['O']['141_B-4'] = 0;
$majorAntGroup['O']['224_O:61-k1'] = 0;
$majorAntGroup['O']['26_Y'] = 0;
$majorAntGroup['O']['29_O-61'] = 0;
$majorAntGroup['O']['9_G'] = 0;
$majorAntGroup['O']['10_H'] = 0;
$majorAntGroup['O']['12_J'] = 0;
$majorAntGroup['O']['13_K'] = 0;
$majorAntGroup['O']['14_L'] = 0;
$majorAntGroup['O']['142_D1/D2-3'] = 0;
$majorAntGroup['O']['147_E1-2'] = 0;
$majorAntGroup['O']['18_N'] = 0;
$majorAntGroup['O']['21_P'] = 0;
$majorAntGroup['O']['213_Enteritidis-1'] = 0;
$majorAntGroup['O']['23_S'] = 0;
$majorAntGroup['O']['D1/D2-5'] = 0;
$majorAntGroup['O']['D1/D2-5'] = 0;
$majorAntGroup['O']['24_V'] = 0;
$majorAntGroup['O']['28_O-58'] = 0;
$majorAntGroup['O']['4_C1'] = 0;
        foreach($this->probes as $probe => $value)
        {     
            foreach($majorAntGroup as $mAG => $probes)
            {
                if(array_key_exists($probe, $probes))
                {
                    //$this->writeLine($mAG.'|'.$probe.'|'.$value.'|'.$this->isAboveThresh($probe, $value)."\n");
                    if($this->isAboveThresh($probe, $value))
                    {       
                        $majorAntGroup[$mAG][$probe] = $value;
                    }
                }
            }
        }
        
        foreach($majorAntGroup as $mAG => $probes)
        {
            natsort($probes);
            $majorAntGroup[$mAG] = array_reverse($probes);
        }
        unset($this->data);
        $this->data = $majorAntGroup;
    }
    function calcDistances()
    {
        $distances = array();  

        foreach($this->data as $mAG => $probes)
        {
            $tracker =  0;
            foreach($probes as $probe => $v1)
            {
                foreach($this->profile as $majorAnt => $antigens)
                {
                    if($majorAnt != $mAG)
                    {
                        continue;
                    }
                    foreach($antigens as $specAnt => $profileProbes)
                    {
                        if(!array_key_exists($probe, $profileProbes))
                        {
                            $v2 = 0;
                            $weight =  0;
                        }
                        else{
                            $v2 = $profileProbes[$probe]['value'];
                            $weight = $profileProbes[$probe]['weight'];
                        }                    
                        if(!array_key_exists($probe, $probes))
                        {
                            continue;
                        }
                        $dist = pow($v1-$v2,2);       
                        if($tracker === 0)
                        {
                            $dist = $dist*5;
                        }
                            
                        

                        if(!array_key_exists($majorAnt, $distances))
                        {
                            $distances[$majorAnt] = array();
                        }
                        if(!array_key_exists($specAnt, $distances[$majorAnt]))
                        {
                            $distances[$majorAnt][$specAnt] = 0;
                        }
                        $line = "$majorAnt\t$probe\t$v1\t$v2\t$specAnt\t$dist\t".$distances[$majorAnt][$specAnt]."\n";
                        $distances[$majorAnt][$specAnt] = $distances[$majorAnt][$specAnt] + $dist;
                        //$this->writeLine($line);
                    }
                    
                }
                $tracker++;
                
            }        
        }
        
        $this->distances = $distances;
       // var_dump($distances);
     
    }
    
    function orderDistances()
    {
        foreach($this->distances as $group => $values)
        {
            natsort($values);
            $this->distances[$group] = $values;    
        }
    }
    
    function callAntigens()
    {
        #write Results
        $antFormula = array();
        
        foreach($this->distances as $majorAnt => $antigens)
        {
            natsort($antigens);
            $sum = 0;
            $count =0;
            foreach($antigens as $specAnt => $dist)
            {
                $sum = $sum + $dist;
                if($count === 0 || $dist < $min)
                {
                    $min = $dist;
                    $match = $specAnt;
                    $match_dist = $dist;
                }
                if($count === 1)
                {
                    $nn_match = $specAnt;
                    $nn_dist = $dist;
                }
                $max = $dist;
                $count++;
            }
            
            $ave = $sum/$count;
            if($majorAnt === 'O')
            {
                $this->setOant($match);
            }
            if($majorAnt === 'H1')
            {
                $this->setH1ant($match);
            }     
            if($majorAnt === 'H2')
            {
                $this->setH2ant($match);
            } 
            $this->results[$majorAnt] = array('MIN'=>$min,'MAX'=>$max,'AVE'=>$ave,
                'match_name'=>$match,'nn_name'=>$nn_match,'match_dist'=>$match_dist,'nn_dist'=>$nn_dist);
        } 
      
        //var_dump($this->probes);
    }
    
    
    function classify()
    {
        $this->readThresholds();
        $this->ProcessRawData();
        $this->categorizeProbes();
        $this->calcDistances();
        $this->orderDistances();
        $this->setError($this-> isControlValid('Biotin-Marke_2,5uM'));
        $this->callAntigens();
        $this->writeResults();
    }
    
    
}